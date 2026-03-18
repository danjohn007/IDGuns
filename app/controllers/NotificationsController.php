<?php
class NotificationsController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        // Sync recent geofence events from Traccar → alertas_eventos + notificaciones
        $this->syncTraccarGeofenceEvents();

        $notificaciones = [];
        try {
            $db = Database::getInstance();
            $notificaciones = $db->prepare(
                "SELECT * FROM notificaciones WHERE user_id = :uid ORDER BY created_at DESC LIMIT 100"
            );
            $notificaciones->execute([':uid' => $_SESSION['user_id'] ?? 0]);
            $notificaciones = $notificaciones->fetchAll();

            // Mark all as read
            $db->prepare("UPDATE notificaciones SET leido = 1, leido_at = :now WHERE user_id = :uid AND leido = 0")
               ->execute([':now' => date('Y-m-d H:i:s'), ':uid' => $_SESSION['user_id'] ?? 0]);
        } catch (\Throwable $e) {
            // table may not exist
        }

        $this->render('notifications/index', [
            'title'          => 'Notificaciones',
            'flash'          => $this->getFlash(),
            'notificaciones' => $notificaciones,
        ]);
    }

    /**
     * AJAX: GET /notificaciones/eventos
     * Returns geofence events from alertas_eventos as JSON (for live polling).
     */
    public function events(): void
    {
        $this->requireAuth();
        session_write_close();

        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days')) . 'T00:00:00Z';
        $to   = $_GET['to']   ?? date('Y-m-d') . 'T23:59:59Z';

        $this->syncTraccarGeofenceEvents();

        $eventos = [];
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare(
                "SELECT * FROM alertas_eventos
                 WHERE evento_at BETWEEN :from AND :to
                 ORDER BY evento_at DESC LIMIT 200"
            );
            $stmt->execute([
                ':from' => date('Y-m-d H:i:s', strtotime($from)),
                ':to'   => date('Y-m-d H:i:s', strtotime($to)),
            ]);
            $eventos = $stmt->fetchAll();
        } catch (\Throwable $e) {
            // table may not exist
        }

        $this->json($eventos);
    }

    /**
     * AJAX: GET /notificaciones/count
     * Returns unread notification count for current user.
     */
    public function count(): void
    {
        $this->requireAuth();
        session_write_close();

        $count = 0;
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM notificaciones WHERE user_id = :uid AND leido = 0");
            $stmt->execute([':uid' => $_SESSION['user_id'] ?? 0]);
            $count = (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {}

        $this->json(['count' => $count]);
    }

    // ── Traccar sync ────────────────────────────────────────────────────────

    private function syncTraccarGeofenceEvents(): void
    {
        try {
            $settings = (new Setting())->getAllGrouped();
            $url      = rtrim($settings['traccar_url']  ?? '', '/');
            $user     = $settings['traccar_usuario']     ?? '';
            $pass     = $settings['traccar_password']    ?? '';
            if (empty($url)) return;

            $from = date('Y-m-d', strtotime('-7 days')) . 'T00:00:00Z';
            $to   = date('Y-m-d') . 'T23:59:59Z';

            // 1. Fetch devices → name map
            $devices = $this->traccarGet($url, $user, $pass, '/api/devices');
            if (!is_array($devices) || isset($devices['error']) || empty($devices)) return;
            $deviceMap = [];
            foreach ($devices as $d) $deviceMap[$d['id']] = $d['name'] ?? 'Dispositivo #' . $d['id'];

            // 2. Fetch geofences → name map
            $geofences = $this->traccarGet($url, $user, $pass, '/api/geofences');
            $geoMap = [];
            if (is_array($geofences) && !isset($geofences['error'])) {
                foreach ($geofences as $gf) $geoMap[$gf['id']] = $gf['name'] ?? 'Geozona #' . $gf['id'];
            }

            // 3. Fetch geofence events for all devices
            $params = '';
            foreach (array_keys($deviceMap) as $did) $params .= '&deviceId=' . (int) $did;
            $endpoint = '/api/reports/events?' . ltrim($params, '&')
                      . '&from=' . urlencode($from) . '&to=' . urlencode($to)
                      . '&type=geofenceEnter&type=geofenceExit';
            $events = $this->traccarGet($url, $user, $pass, $endpoint);
            if (!is_array($events) || isset($events['error']) || empty($events)) return;

            // 4. Upsert events + create notifications
            $db = Database::getInstance();

            $insertEvt = $db->prepare(
                "INSERT IGNORE INTO alertas_eventos
                    (traccar_event_id, tipo, device_name, traccar_device_id,
                     geozona_nombre, traccar_geofence_id, mensaje, evento_at, created_at)
                 VALUES (:eid, :tipo, :dname, :did, :gname, :gid, :msg, :eat, :now)"
            );

            $insertNotif = $db->prepare(
                "INSERT INTO notificaciones (user_id, tipo, mensaje, url, leido, created_at)
                 VALUES (:uid, :tipo, :msg, :url, 0, :cr)"
            );

            // Get admin/superadmin user IDs
            $adminIds = [];
            try {
                $admins   = $db->query("SELECT id FROM users WHERE rol IN ('superadmin','admin') AND activo = 1")->fetchAll();
                $adminIds = array_column($admins, 'id');
            } catch (\Throwable $e) {}

            $tipoLabels = [
                'geofenceEnter' => 'Entrada a geozona',
                'geofenceExit'  => 'Salida de geozona',
            ];

            foreach ($events as $ev) {
                $evId = $ev['id'] ?? null;
                if (!$evId) continue;

                $tipo       = $ev['type'] ?? 'unknown';
                $deviceId   = $ev['deviceId'] ?? 0;
                $geofenceId = $ev['geofenceId'] ?? 0;
                $deviceName = $deviceMap[$deviceId] ?? 'Dispositivo #' . $deviceId;
                $geoName    = $geoMap[$geofenceId] ?? '';
                $tipoLabel  = $tipoLabels[$tipo] ?? $tipo;

                $mensaje = $tipoLabel . ': ' . $deviceName;
                if ($geoName) $mensaje .= ' — ' . $geoName;

                $eventoAt = isset($ev['eventTime'])
                    ? date('Y-m-d H:i:s', strtotime($ev['eventTime']))
                    : date('Y-m-d H:i:s');

                try {
                    $insertEvt->execute([
                        ':eid'   => (int) $evId,
                        ':tipo'  => $tipo,
                        ':dname' => $deviceName,
                        ':did'   => $deviceId,
                        ':gname' => $geoName,
                        ':gid'   => $geofenceId ?: null,
                        ':msg'   => $mensaje,
                        ':eat'   => $eventoAt,
                        ':now'   => date('Y-m-d H:i:s'),
                    ]);

                    // If inserted (not duplicate), notify admins
                    if ($insertEvt->rowCount() > 0 && !empty($adminIds)) {
                        foreach ($adminIds as $uid) {
                            try {
                                $insertNotif->execute([
                                    ':uid'  => $uid,
                                    ':tipo' => $tipo,
                                    ':msg'  => $mensaje,
                                    ':url'  => '/notificaciones',
                                    ':cr'   => $eventoAt,
                                ]);
                            } catch (\Throwable $e) {}
                        }
                    }
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            // Traccar unavailable or tables missing — fail silently
        }
    }

    private function traccarGet(string $baseUrl, string $user, string $pass, string $endpoint): array
    {
        $ch = curl_init($baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERPWD        => $user . ':' . $pass,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $code === 0) return ['error' => 'No se pudo conectar'];
        if ($code !== 200)         return ['error' => 'HTTP ' . $code];
        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['error' => 'Respuesta inválida'];
    }
}
