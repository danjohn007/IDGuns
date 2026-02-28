<?php
class AlertasController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $reglas  = [];
        $assets  = [];
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

        $allowedTipos = [
            'geofenceExit', 'geofenceEnter', 'speeding', 'deviceOffline',
            'deviceOnline', 'ignitionOn', 'ignitionOff', 'alarm', 'custom',
        ];
        $tipo = $_POST['tipo'] ?? 'geofenceExit';
        if (!in_array($tipo, $allowedTipos)) {
            $tipo = 'geofenceExit';
        }

        try {
            $db = Database::getInstance();
            $db->prepare(
                "INSERT INTO alertas_reglas
                     (nombre, tipo, activo_id, geozona_id, notificar_email, notificar_whatsapp, activo, created_at)
                 VALUES (:n, :t, :a, :g, :em, :wa, 1, :cr)"
            )->execute([
                ':n'  => $nombre,
                ':t'  => $tipo,
                ':a'  => !empty($_POST['activo_id'])  ? (int)$_POST['activo_id']  : null,
                ':g'  => !empty($_POST['geozona_id']) ? (int)$_POST['geozona_id'] : null,
                ':em' => isset($_POST['notificar_email'])     ? 1 : 0,
                ':wa' => isset($_POST['notificar_whatsapp'])  ? 1 : 0,
                ':cr' => date('Y-m-d H:i:s'),
            ]);
            $this->setFlash('success', 'Regla de alerta creada correctamente.');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'No se pudo guardar la regla. Verifique que la tabla exista (ejecute la migración v3).');
        }

        $this->redirect('alertas');
    }

    public function edit(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect('alertas');
        }

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
        } catch (\Throwable $e) {
            // tables may not exist
        }

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

        $allowedTipos = [
            'geofenceExit', 'geofenceEnter', 'speeding', 'deviceOffline',
            'deviceOnline', 'ignitionOn', 'ignitionOff', 'alarm', 'custom',
        ];
        $tipo = $_POST['tipo'] ?? 'geofenceExit';
        if (!in_array($tipo, $allowedTipos)) {
            $tipo = 'geofenceExit';
        }

        try {
            $db = Database::getInstance();
            $db->prepare(
                "UPDATE alertas_reglas
                 SET nombre=:n, tipo=:t, activo_id=:a, geozona_id=:g,
                     notificar_email=:em, notificar_whatsapp=:wa
                 WHERE id=:id"
            )->execute([
                ':n'  => $nombre,
                ':t'  => $tipo,
                ':a'  => !empty($_POST['activo_id'])  ? (int)$_POST['activo_id']  : null,
                ':g'  => !empty($_POST['geozona_id']) ? (int)$_POST['geozona_id'] : null,
                ':em' => isset($_POST['notificar_email'])    ? 1 : 0,
                ':wa' => isset($_POST['notificar_whatsapp']) ? 1 : 0,
                ':id' => $id,
            ]);
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
                $db = Database::getInstance();
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
        if ($id) {
            try {
                $db = Database::getInstance();
                $db->prepare(
                    "UPDATE alertas_reglas SET activo = 1 - activo WHERE id = :id"
                )->execute([':id' => $id]);
                $this->setFlash('success', 'Estado de la regla actualizado.');
            } catch (\Throwable $e) {
                $this->setFlash('error', 'No se pudo actualizar la regla.');
            }
        }
        $this->redirect('alertas');
    }

    /**
     * Fetch geofences from Traccar and upsert them into the local geozonas table.
     * This ensures the Alertas dropdown always shows all geozonas from Traccar,
     * including those created outside the IDGuns UI.
     */
    private function syncGeozonas(): void
    {
        try {
            $db       = Database::getInstance();
            $settings = (new Setting())->getAllGrouped();
            $url      = rtrim($settings['traccar_url']     ?? '', '/');
            $user     = $settings['traccar_usuario']        ?? '';
            $pass     = $settings['traccar_password']       ?? '';

            if (empty($url)) return;

            $ch = curl_init($url . '/api/geofences');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_USERPWD        => $user . ':' . $pass,
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code !== 200) return;

            $geofences = json_decode($response, true);
            if (!is_array($geofences)) return;

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
}
