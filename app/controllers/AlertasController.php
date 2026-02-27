<?php
class AlertasController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $reglas  = [];
        $assets  = [];
        $geozonas = [];

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
}
