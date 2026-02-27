<?php
class GeozonaController extends BaseController
{
    private Setting $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    public function index(): void
    {
        $this->requireAuth();

        $assets = [];
        try {
            $db     = Database::getInstance();
            $assets = $db->query("SELECT id, codigo, nombre FROM activos WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll();
        } catch (\Throwable $e) {
            // table may not be ready
        }

        $settings   = $this->settingModel->getAllGrouped();
        $traccarUrl = rtrim($settings['traccar_url'] ?? '', '/');

        $this->render('geozonas/index', [
            'title'      => 'Geozonas',
            'flash'      => $this->getFlash(),
            'assets'     => $assets,
            'traccarUrl' => $traccarUrl,
            'csrf'       => $this->csrfToken(),
        ]);
    }

    /**
     * Proxy: GET /geozonas/listar
     * Returns geofences list from Traccar API as JSON.
     */
    public function list(): void
    {
        $this->requireAuth();
        $data = $this->traccarGet('/api/geofences');
        $this->json(is_array($data) ? $data : []);
    }

    /**
     * Proxy: POST /geozonas/guardar
     * Creates a geofence in Traccar.
     */
    public function store(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->json(['error' => 'Petición inválida'], 403);
        }

        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        $area   = trim($_POST['area'] ?? '');

        if (empty($nombre) || empty($area)) {
            $this->setFlash('error', 'El nombre y el área son obligatorios.');
            $this->redirect('geozonas');
        }

        $payload = [
            'name'        => $nombre,
            'description' => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'area'        => $area,
            'calendarId'  => 0,
            'attributes'  => new \stdClass(),
        ];

        $result = $this->traccarPost('/api/geofences', $payload);

        if (!empty($result['error'])) {
            $this->setFlash('error', 'Error al crear la geozona en Traccar: ' . $result['error']);
        } else {
            // Optionally save locally
            try {
                $db = Database::getInstance();
                $db->prepare(
                    "INSERT IGNORE INTO geozonas (traccar_id, nombre, descripcion, area, created_at)
                     VALUES (:tid, :nombre, :desc, :area, :cr)"
                )->execute([
                    ':tid'    => $result['id'] ?? null,
                    ':nombre' => $nombre,
                    ':desc'   => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    ':area'   => $area,
                    ':cr'     => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                // geozonas table may not exist yet; ignore
            }
            $this->setFlash('success', 'Geozona creada correctamente.');
        }

        $this->redirect('geozonas');
    }

    /**
     * Proxy: DELETE /geozonas/eliminar/{id}
     */
    public function delete(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect('geozonas');
        }

        $result = $this->traccarDelete('/api/geofences/' . $id);

        if (!empty($result['error'])) {
            $this->setFlash('error', 'Error al eliminar la geozona: ' . $result['error']);
        } else {
            try {
                $db = Database::getInstance();
                $db->prepare("DELETE FROM geozonas WHERE traccar_id = :id")->execute([':id' => $id]);
            } catch (\Throwable $e) {
                // ignore
            }
            $this->setFlash('success', 'Geozona eliminada.');
        }

        $this->redirect('geozonas');
    }

    // ── Traccar proxy helpers ─────────────────────────────────────────────────

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
        if (empty($cfg['url'])) {
            return ['error' => 'Servidor Traccar no configurado'];
        }

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
        if (empty($cfg['url'])) {
            return ['error' => 'Servidor Traccar no configurado'];
        }

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

        if ($error || $code === 0) return ['error' => 'No se pudo conectar al servidor Traccar'];
        if ($code === 401)         return ['error' => 'Credenciales Traccar incorrectas'];
        if ($code >= 400)          return ['error' => 'Error Traccar: código ' . $code];

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function traccarDelete(string $endpoint): array
    {
        $cfg = $this->getTraccarConfig();
        if (empty($cfg['url'])) {
            return ['error' => 'Servidor Traccar no configurado'];
        }

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
}
