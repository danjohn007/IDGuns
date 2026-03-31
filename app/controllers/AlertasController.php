<?php
class AlertasController extends BaseController
{
    private Setting $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    /* ================================================================
     *  CRUD – index / store / edit / updateRule / delete / toggle
     * ================================================================ */

    public function index(): void
    {
        $this->requireAuth();

        $reglas   = [];
        $assets   = [];
        $geozonas = [];

        $this->syncGeozonas();

        try {
            $db      = Database::getInstance();
            $reglas  = $db->query(
                "SELECT r.*, a.nombre AS activo_nombre, a.codigo AS activo_codigo,
                        g.nombre AS geozona_nombre
                 FROM alertas_reglas r
                 LEFT JOIN activos a  ON r.activo_id  = a.id
                 LEFT JOIN geozonas g ON r.geozona_id = g.id
                 ORDER BY r.id DESC"
            )->fetchAll();
            $assets   = $db->query("SELECT id, codigo, nombre FROM activos WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll();
            $geozonas = $db->query("SELECT id, nombre FROM geozonas WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
        } catch (\Throwable $e) {
            // tables may not exist yet
        }

        $this->render('alertas/index', [
            'title'    => 'Alertas y Notificaciones',
            'flash'    => $this->getFlash(),
            'reglas'   => $reglas,
            'assets'   => $assets,
            'geozonas' => $geozonas,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('alertas');
        }

        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        if (empty($nombre)) {
            $this->setFlash('error', 'El nombre de la regla es obligatorio.');
            $this->redirect('alertas');
        }

        $tipo = $this->sanitizeTipo($_POST['tipo'] ?? '');

        $activoId  = !empty($_POST['activo_id'])  ? (int)$_POST['activo_id']  : null;
        $geozonaId = !empty($_POST['geozona_id']) ? (int)$_POST['geozona_id'] : null;
        $email     = isset($_POST['notificar_email'])    ? 1 : 0;
        $whatsapp  = isset($_POST['notificar_whatsapp']) ? 1 : 0;

        try {
            $db = Database::getInstance();

            /* 1) Insert local rule ---------------------------------------- */
            $stmt = $db->prepare(
                "INSERT INTO alertas_reglas
                     (nombre, tipo, activo_id, geozona_id, notificar_email,
                      notificar_whatsapp, activo, created_at)
                 VALUES (:n, :t, :a, :g, :em, :wa, 1, :cr)"
            );
            $stmt->execute([
                ':n'  => $nombre,
                ':t'  => $tipo,
                ':a'  => $activoId,
                ':g'  => $geozonaId,
                ':em' => $email,
                ':wa' => $whatsapp,
                ':cr' => date('Y-m-d H:i:s'),
            ]);
            $ruleId = (int) $db->lastInsertId();

            /* 2) Create notification in Traccar + link -------------------- */
            $traccarNid = $this->createTraccarNotification($tipo, $activoId, $geozonaId);
            if ($traccarNid) {
                $db->prepare("UPDATE alertas_reglas SET traccar_notification_id = :tid WHERE id = :id")
                   ->execute([':tid' => $traccarNid, ':id' => $ruleId]);
            }

            $this->setFlash('success', 'Regla de alerta creada correctamente.'
                . ($traccarNid ? '' : ' (Sin enlace con Traccar)'));
        } catch (\Throwable $e) {
            $this->setFlash('error', 'No se pudo guardar la regla. ' . $e->getMessage());
        }

        $this->redirect('alertas');
    }

    public function edit(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) { $this->redirect('alertas'); }

        $regla    = null;
        $assets   = [];
        $geozonas = [];

        $this->syncGeozonas();

        try {
            $db       = Database::getInstance();
            $stmt     = $db->prepare("SELECT * FROM alertas_reglas WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $regla    = $stmt->fetch();
            $assets   = $db->query("SELECT id, codigo, nombre FROM activos WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll();
            $geozonas = $db->query("SELECT id, nombre FROM geozonas WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
        } catch (\Throwable $e) { /* tables may not exist */ }

        if (!$regla) {
            $this->setFlash('error', 'Regla no encontrada.');
            $this->redirect('alertas');
        }

        $this->render('alertas/edit', [
            'title'    => 'Editar Regla',
            'flash'    => $this->getFlash(),
            'regla'    => $regla,
            'assets'   => $assets,
            'geozonas' => $geozonas,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    public function updateRule(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('alertas');
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');

        if (!$id || empty($nombre)) {
            $this->setFlash('error', 'Datos inválidos.');
            $this->redirect('alertas');
        }

        $tipo      = $this->sanitizeTipo($_POST['tipo'] ?? '');
        $activoId  = !empty($_POST['activo_id'])  ? (int)$_POST['activo_id']  : null;
        $geozonaId = !empty($_POST['geozona_id']) ? (int)$_POST['geozona_id'] : null;
        $email     = isset($_POST['notificar_email'])    ? 1 : 0;
        $whatsapp  = isset($_POST['notificar_whatsapp']) ? 1 : 0;

        try {
            $db = Database::getInstance();

            /* Fetch the old rule to get the traccar notification id */
            $oldStmt = $db->prepare("SELECT traccar_notification_id, tipo, activo_id, geozona_id FROM alertas_reglas WHERE id = :id");
            $oldStmt->execute([':id' => $id]);
            $old = $oldStmt->fetch();

            /* Update local DB */
            $db->prepare(
                "UPDATE alertas_reglas
                 SET nombre=:n, tipo=:t, activo_id=:a, geozona_id=:g,
                     notificar_email=:em, notificar_whatsapp=:wa
                 WHERE id=:id"
            )->execute([
                ':n'  => $nombre,
                ':t'  => $tipo,
                ':a'  => $activoId,
                ':g'  => $geozonaId,
                ':em' => $email,
                ':wa' => $whatsapp,
                ':id' => $id,
            ]);

            /* If tipo, activo or geozona changed → recreate Traccar notification */
            $changed = !$old
                || $old['tipo'] !== $tipo
                || (int)$old['activo_id'] !== (int)$activoId
                || (int)$old['geozona_id'] !== (int)$geozonaId;

            if ($changed) {
                // Delete old notification in Traccar
                $oldTid = $old['traccar_notification_id'] ?? null;
                if ($oldTid) {
                    $this->traccarDelete('/api/notifications/' . (int)$oldTid);
                }
                // Create fresh one
                $newTid = $this->createTraccarNotification($tipo, $activoId, $geozonaId);
                $db->prepare("UPDATE alertas_reglas SET traccar_notification_id = :tid WHERE id = :id")
                   ->execute([':tid' => $newTid, ':id' => $id]);
            }

            $this->setFlash('success', 'Regla actualizada correctamente.');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'No se pudo actualizar la regla.');
        }

        $this->redirect('alertas');
    }

    public function delete(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            try {
                $db   = Database::getInstance();
                $stmt = $db->prepare("SELECT traccar_notification_id FROM alertas_reglas WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $row  = $stmt->fetch();

                /* Remove from Traccar first */
                if (!empty($row['traccar_notification_id'])) {
                    $this->traccarDelete('/api/notifications/' . (int)$row['traccar_notification_id']);
                }

                $db->prepare("DELETE FROM alertas_reglas WHERE id = :id")->execute([':id' => $id]);
                $this->setFlash('success', 'Regla eliminada.');
            } catch (\Throwable $e) {
                $this->setFlash('error', 'No se pudo eliminar la regla.');
            }
        }
        $this->redirect('alertas');
    }

    public function toggle(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) { $this->redirect('alertas'); }

        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT activo, tipo, activo_id, geozona_id, traccar_notification_id FROM alertas_reglas WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $rule = $stmt->fetch();

            if (!$rule) {
                $this->redirect('alertas');
            }

            $newState = $rule['activo'] ? 0 : 1;

            if ($newState === 0 && !empty($rule['traccar_notification_id'])) {
                /* Deactivating → delete notification from Traccar */
                $this->traccarDelete('/api/notifications/' . (int)$rule['traccar_notification_id']);
                $db->prepare("UPDATE alertas_reglas SET activo = 0, traccar_notification_id = NULL WHERE id = :id")
                   ->execute([':id' => $id]);
            } elseif ($newState === 1) {
                /* Activating → recreate notification in Traccar */
                $tid = $this->createTraccarNotification(
                    $rule['tipo'],
                    $rule['activo_id'] ? (int)$rule['activo_id'] : null,
                    $rule['geozona_id'] ? (int)$rule['geozona_id'] : null
                );
                $db->prepare("UPDATE alertas_reglas SET activo = 1, traccar_notification_id = :tid WHERE id = :id")
                   ->execute([':tid' => $tid, ':id' => $id]);
            }

            $this->setFlash('success', 'Estado de la regla actualizado.');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'No se pudo actualizar la regla.');
        }

        $this->redirect('alertas');
    }

    /* ================================================================
     *  Traccar notification helper – create + link to devices/geofences
     * ================================================================ */

    /**
     * Create a Traccar notification of the given type and link it to the
     * appropriate devices and geofences.  Returns the Traccar notification id
     * on success, or null on failure.
     */
    private function createTraccarNotification(string $tipo, ?int $activoId, ?int $geozonaId): ?int
    {
        /*
         * always=true → notification fires for ALL devices the user has access to.
         * This avoids the need for device↔notification linking (which the Traccar
         * demo server does NOT support — tc_notification_device table is missing).
         * The notification is automatically linked to the creating user by Traccar.
         */
        $payload = [
            'type'         => $tipo,
            'always'       => true,
            'notificators' => 'web',
            'attributes'   => new \stdClass(),
        ];

        $result = $this->traccarPost('/api/notifications', $payload);
        if (isset($result['error']) || empty($result['id'])) {
            return null;
        }

        $notifId = (int) $result['id'];

        /* Resolve Traccar geofence ID */
        $traccarGeofenceId = $geozonaId ? $this->getTraccarGeofenceId($geozonaId) : null;

        if ($traccarGeofenceId) {
            /* Link notification ↔ geofence (required for geofence alerts) */
            $this->traccarPost('/api/permissions', [
                'notificationId' => $notifId,
                'geofenceId'     => $traccarGeofenceId,
            ]);

            /* Link each device ↔ geofence (required for geofence events to fire) */
            $this->linkDevicesToGeofence($traccarGeofenceId, $activoId);
        }

        return $notifId;
    }

    /**
     * Link ALL devices to a geofence.
     * If $activoId is provided, only link that device; otherwise link all.
     */
    private function linkDevicesToGeofence(int $geofenceId, ?int $activoId): void
    {
        $deviceIds = $this->getTraccarDeviceIds($activoId);
        foreach ($deviceIds as $did) {
            $this->traccarPost('/api/permissions', [
                'deviceId'   => $did,
                'geofenceId' => $geofenceId,
            ]);
        }
    }

    /**
     * Get all device IDs directly from Traccar API.
     * If $activoId is set, returns only that activo's device.
     */
    private function getTraccarDeviceIds(?int $activoId): array
    {
        $deviceIds = [];

        if ($activoId) {
            // Get specific device from local DB → Traccar ID
            try {
                $db   = Database::getInstance();
                $stmt = $db->prepare(
                    "SELECT traccar_device_id FROM dispositivos_gps WHERE activo_id = :aid AND activo = 1 LIMIT 1"
                );
                $stmt->execute([':aid' => $activoId]);
                $row = $stmt->fetch();
                if (!empty($row['traccar_device_id'])) {
                    $deviceIds[] = (int) $row['traccar_device_id'];
                }
            } catch (\Throwable $e) {}
        }

        // Always also get ALL devices from Traccar API as fallback
        if (empty($deviceIds)) {
            $devices = $this->traccarGet('/api/devices');
            if (is_array($devices) && !isset($devices['error'])) {
                foreach ($devices as $d) {
                    if (!empty($d['id'])) {
                        $deviceIds[] = (int) $d['id'];
                    }
                }
            }
        }

        return array_unique($deviceIds);
    }

    /**
     * Get the Traccar geofence ID for a local geozona.
     */
    private function getTraccarGeofenceId(int $geozonaId): ?int
    {
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT traccar_id FROM geozonas WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $geozonaId]);
            $gz   = $stmt->fetch();
            if (!empty($gz['traccar_id'])) {
                return (int) $gz['traccar_id'];
            }
        } catch (\Throwable $e) {}
        return null;
    }



    /* ================================================================
     *  Sync geozonas from Traccar → local DB
     * ================================================================ */

    private function syncGeozonas(): void
    {
        try {
            $geofences = $this->traccarGet('/api/geofences');
            if (isset($geofences['error']) || !is_array($geofences)) return;

            $db = Database::getInstance();
            foreach ($geofences as $g) {
                if (empty($g['id'])) continue;
                $db->prepare(
                    "INSERT INTO geozonas (traccar_id, nombre, descripcion, area, activo, created_at)
                     VALUES (:tid, :nombre, :desc, :area, 1, :cr)
                     ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), activo = 1"
                )->execute([
                    ':tid'    => (int) $g['id'],
                    ':nombre' => $g['name'],
                    ':desc'   => $g['description'] ?? '',
                    ':area'   => $g['area'] ?? '',
                    ':cr'     => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore sync errors silently
        }
    }

    /* ================================================================
     *  Traccar HTTP helpers (match GeozonaController pattern)
     * ================================================================ */

    private function getTraccarConfig(): array
    {
        $settings = $this->settingModel->getAllGrouped();
        return [
            'url'  => rtrim($settings['traccar_url']      ?? '', '/'),
            'user' => $settings['traccar_usuario']         ?? '',
            'pass' => $settings['traccar_password']        ?? '',
        ];
    }

    private function traccarGet(string $endpoint): array
    {
        $cfg = $this->getTraccarConfig();
        if (empty($cfg['url'])) return ['error' => 'Servidor Traccar no configurado'];

        $ch = curl_init($cfg['url'] . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERPWD        => $cfg['user'] . ':' . $cfg['pass'],
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $code === 0) return ['error' => 'No se pudo conectar al servidor Traccar'];
        if ($code === 401)         return ['error' => 'Credenciales Traccar incorrectas'];

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['error' => 'Respuesta inválida del servidor Traccar'];
    }

    private function traccarPost(string $endpoint, mixed $payload): array
    {
        $cfg = $this->getTraccarConfig();
        if (empty($cfg['url'])) return ['error' => 'Servidor Traccar no configurado'];

        $body = json_encode($payload);
        $ch   = curl_init($cfg['url'] . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_USERPWD        => $cfg['user'] . ':' . $cfg['pass'],
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        $this->logTraccar("POST {$endpoint} | payload={$body} | HTTP {$code} | resp={$response} | err={$error}");

        if ($error || $code === 0) return ['error' => 'No se pudo conectar al servidor Traccar'];
        if ($code === 401)         return ['error' => 'Credenciales Traccar incorrectas'];
        if ($code >= 400)          return ['error' => 'Error Traccar: código ' . $code . ' — ' . $response];

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function traccarDelete(string $endpoint): array
    {
        $cfg = $this->getTraccarConfig();
        if (empty($cfg['url'])) return ['error' => 'Servidor Traccar no configurado'];

        $ch = curl_init($cfg['url'] . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_USERPWD        => $cfg['user'] . ':' . $cfg['pass'],
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $code === 0) return ['error' => 'No se pudo conectar al servidor Traccar'];
        if ($code === 401)         return ['error' => 'Credenciales Traccar incorrectas'];
        if ($code >= 400)          return ['error' => 'Error Traccar: código ' . $code];

        return [];
    }

    /* ================================================================
     *  Helpers
     * ================================================================ */

    private function sanitizeTipo(string $raw): string
    {
        $allowed = [
            'geofenceExit', 'geofenceEnter', 'speeding', 'deviceOffline',
            'deviceOnline', 'ignitionOn', 'ignitionOff', 'alarm', 'custom',
        ];
        return in_array($raw, $allowed, true) ? $raw : 'geofenceExit';
    }

    /**
     * Write a line to the Traccar debug log so we can trace API calls.
     */
    private function logTraccar(string $message): void
    {
        $logFile   = __DIR__ . '/../../traccar_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }

    /* ================================================================
     *  Diagnostic action – shows raw Traccar API results for debugging.
     *  Route: alertas/debug
     * ================================================================ */

    public function debugTraccar(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        header('Content-Type: text/html; charset=utf-8');

        $results = [];

        // 1) Session
        $session = $this->traccarGet('/api/session');
        $results['1. GET /api/session'] = $session;

        // 2) Devices
        $devices = $this->traccarGet('/api/devices');
        $results['2. GET /api/devices'] = $devices;

        // 3) Geofences
        $geofences = $this->traccarGet('/api/geofences');
        $results['3. GET /api/geofences'] = $geofences;

        // 4) Notifications
        $notifications = $this->traccarGet('/api/notifications');
        $results['4. GET /api/notifications'] = $notifications;

        // 5) Local geozonas (traccar_id)
        try {
            $db = Database::getInstance();
            $gz = $db->query("SELECT id, nombre, traccar_id FROM geozonas WHERE activo = 1 ORDER BY id")->fetchAll();
            $results['5. Local geozonas (traccar_ids)'] = $gz;
        } catch (\Throwable $e) {
            $results['5. Local geozonas'] = ['error' => $e->getMessage()];
        }

        // 6) Local dispositivos_gps (traccar_device_id)
        try {
            $dg = $db->query("SELECT id, nombre, traccar_device_id, activo_id FROM dispositivos_gps WHERE activo = 1 ORDER BY id")->fetchAll();
            $results['6. Local dispositivos_gps (traccar_device_ids)'] = $dg;
        } catch (\Throwable $e) {
            $results['6. Local dispositivos_gps'] = ['error' => $e->getMessage()];
        }

        // 7) Test permission: link first notification to session user
        $testPermResult = null;
        if (!empty($session['id']) && is_array($notifications) && !isset($notifications['error'])) {
            foreach ($notifications as $n) {
                if (!empty($n['id'])) {
                    $testPermResult = $this->traccarPostRaw('/api/permissions', [
                        'userId'         => (int)$session['id'],
                        'notificationId' => (int)$n['id'],
                    ]);
                    break;
                }
            }
        }
        $results['7. Test POST /api/permissions (user↔notif)'] = $testPermResult ?? 'N/A – no notification found';

        // 8) Test device↔notification permission
        $testDevNotif = null;
        if (is_array($devices) && !isset($devices['error']) && is_array($notifications) && !isset($notifications['error'])) {
            $firstDevice = null;
            $firstNotif  = null;
            foreach ($devices as $d) { if (!empty($d['id'])) { $firstDevice = (int)$d['id']; break; } }
            foreach ($notifications as $n) { if (!empty($n['id'])) { $firstNotif = (int)$n['id']; break; } }
            if ($firstDevice && $firstNotif) {
                $testDevNotif = $this->traccarPostRaw('/api/permissions', [
                    'notificationId' => $firstNotif,
                    'deviceId'       => $firstDevice,
                ]);
            }
        }
        $results['8. Test POST /api/permissions (device↔notif)'] = $testDevNotif ?? 'N/A';

        // 9) Test device↔geofence permission
        $testDevGeo = null;
        if (is_array($devices) && !isset($devices['error']) && is_array($geofences) && !isset($geofences['error'])) {
            $firstDevice = null;
            $firstGeo    = null;
            foreach ($devices as $d) { if (!empty($d['id'])) { $firstDevice = (int)$d['id']; break; } }
            foreach ($geofences as $g) { if (!empty($g['id'])) { $firstGeo = (int)$g['id']; break; } }
            if ($firstDevice && $firstGeo) {
                $testDevGeo = $this->traccarPostRaw('/api/permissions', [
                    'deviceId'   => $firstDevice,
                    'geofenceId' => $firstGeo,
                ]);
            }
        }
        $results['9. Test POST /api/permissions (device↔geofence)'] = $testDevGeo ?? 'N/A';

        // Render
        echo '<!DOCTYPE html><html><head><title>Traccar Debug</title>';
        echo '<style>body{font-family:monospace;padding:20px;background:#1e1e2e;color:#cdd6f4}';
        echo 'pre{background:#313244;padding:12px;border-radius:6px;overflow-x:auto;color:#a6e3a1}';
        echo 'h2{color:#89b4fa}h3{color:#f9e2af;margin-top:24px}</style></head><body>';
        echo '<h2>Traccar API Diagnostic</h2>';
        foreach ($results as $label => $data) {
            echo '<h3>' . htmlspecialchars($label) . '</h3>';
            echo '<pre>' . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
        }
        echo '</body></html>';
        exit;
    }

    /**
     * Raw POST – returns the HTTP code + raw response body (for diagnostics).
     */
    private function traccarPostRaw(string $endpoint, mixed $payload): array
    {
        $cfg = $this->getTraccarConfig();
        if (empty($cfg['url'])) return ['error' => 'No config'];

        $body = json_encode($payload);
        $ch   = curl_init($cfg['url'] . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_USERPWD        => $cfg['user'] . ':' . $cfg['pass'],
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        return [
            'sent'      => $payload,
            'http_code' => $code,
            'response'  => json_decode($response, true) ?? $response,
            'curl_err'  => $error ?: null,
        ];
    }
}
