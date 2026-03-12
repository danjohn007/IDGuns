<?php
class GpsReportController extends BaseController
{
    private GpsDevice $gpsModel;
    private Setting   $settingModel;

    public function __construct()
    {
        $this->gpsModel     = new GpsDevice();
        $this->settingModel = new Setting();
    }

    /**
     * GET /reportes-gps
     * Renders page with device list. Traccar data is fetched via AJAX
     * from the browser using /geolocalizacion/resumen (the same proxy
     * endpoint the Geolocalización map popup uses successfully).
     */
    public function index(): void
    {
        $this->requireAuth();

        $settings   = $this->settingModel->getAllGrouped();
        $traccarUrl = rtrim($settings['traccar_url'] ?? '', '/');
        $timezone   = $settings['app_timezone'] ?? 'America/Mexico_City';

        $dateFrom       = $_GET['fecha_desde']   ?? date('Y-m-01');
        $dateTo         = $_GET['fecha_hasta']    ?? date('Y-m-d');
        $kmPorLitro     = !empty($_GET['km_por_litro']) ? (float)$_GET['km_por_litro'] : 0;
        $precioPorLitro = !empty($_GET['precio_litro'])  ? (float)$_GET['precio_litro']  : 22.50;

        $devices = [];
        try {
            $devices = $this->gpsModel->getAllWithAsset();
        } catch (\Throwable $e) {
            // GPS table may not exist yet
        }

        $this->render('gps_reports/index', [
            'title'          => 'Reportes GPS',
            'flash'          => $this->getFlash(),
            'devices'        => $devices,
            'traccarUrl'     => $traccarUrl,
            'timezone'       => $timezone,
            'dateFrom'       => $dateFrom,
            'dateTo'         => $dateTo,
            'kmPorLitro'     => $kmPorLitro,
            'precioPorLitro' => $precioPorLitro,
        ]);
    }

    /**
     * POST /reportes-gps/guardar-km
     * Saves the per-device km/Litro value via AJAX.
     */
    public function saveKmPorLitro(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        header('Content-Type: application/json');
        if (!$this->isPost()) {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            exit;
        }

        $id  = (int) ($_POST['device_id'] ?? 0);
        $kml = isset($_POST['km_por_litro']) && $_POST['km_por_litro'] !== ''
               ? (float) $_POST['km_por_litro']
               : null;

        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ID de dispositivo inválido']);
            exit;
        }

        try {
            $this->gpsModel->updateKmPorLitro($id, $kml);
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * POST /reportes-gps/fix-traccar-id
     * Auto-corrects a device's traccar_device_id when the JS discovers a mismatch.
     */
    public function fixTraccarId(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        header('Content-Type: application/json');
        if (!$this->isPost()) {
            echo json_encode(['ok' => false]);
            exit;
        }

        $id           = (int) ($_POST['device_id'] ?? 0);
        $realTraccarId = (int) ($_POST['real_traccar_id'] ?? 0);

        if (!$id || !$realTraccarId) {
            echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
            exit;
        }

        try {
            $this->gpsModel->update($id, ['traccar_device_id' => $realTraccarId]);
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
