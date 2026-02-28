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

    public function index(): void
    {
        $this->requireAuth();

        $settings   = $this->settingModel->getAllGrouped();
        $traccarUrl = rtrim($settings['traccar_url'] ?? '', '/');
        $timezone   = $settings['app_timezone'] ?? 'America/Mexico_City';

        // Default date range: current month
        $dateFrom = $_GET['fecha_desde'] ?? date('Y-m-01');
        $dateTo   = $_GET['fecha_hasta'] ?? date('Y-m-d');

        // km/L and gas price inputs (global / fallback values)
        $kmPorLitro  = !empty($_GET['km_por_litro']) ? (float)$_GET['km_por_litro'] : 0;
        $precioPorLitro = !empty($_GET['precio_litro']) ? (float)$_GET['precio_litro'] : 22.50; // MXN avg

        $devices = [];
        try {
            $devices = $this->gpsModel->getAllWithAsset();
        } catch (\Throwable $e) {
            // GPS table may not exist yet
        }

        // Fetch route data from Traccar for each device (summary)
        $reports = [];
        if ($traccarUrl && !empty($devices)) {
            foreach ($devices as $device) {
                if (empty($device['traccar_device_id'])) {
                    // No Traccar ID: include device with null data
                    $reports[] = [
                        'device'          => $device,
                        'summary'         => [],
                        'km_total'        => null,
                        'litros_estimados'=> null,
                        'costo_estimado'  => null,
                    ];
                    continue;
                }
                $summary = $this->fetchTraccarSummary(
                    (int)$device['traccar_device_id'],
                    $dateFrom,
                    $dateTo,
                    $settings
                );
                $kmTotal = isset($summary['distance']) ? round($summary['distance'] / 1000, 2) : null;

                // Per-device km/L takes priority over the global form value
                $deviceKmL = !empty($device['km_por_litro']) ? (float)$device['km_por_litro'] : $kmPorLitro;
                $litrosEstimados = ($deviceKmL > 0 && $kmTotal !== null) ? round($kmTotal / $deviceKmL, 2) : null;
                $costoEstimado   = ($litrosEstimados !== null) ? round($litrosEstimados * $precioPorLitro, 2) : null;
                $reports[] = [
                    'device'          => $device,
                    'summary'         => $summary,
                    'km_total'        => $kmTotal,
                    'litros_estimados'=> $litrosEstimados,
                    'costo_estimado'  => $costoEstimado,
                ];
            }
        } else {
            // No Traccar: just list devices without route data
            foreach ($devices as $device) {
                $reports[] = [
                    'device'          => $device,
                    'summary'         => [],
                    'km_total'        => null,
                    'litros_estimados'=> null,
                    'costo_estimado'  => null,
                ];
            }
        }

        $this->render('gps_reports/index', [
            'title'          => 'Reportes GPS',
            'flash'          => $this->getFlash(),
            'reports'        => $reports,
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
     * Saves the per-device km/Litro value via AJAX (JSON response).
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

    // ── Private helpers ──────────────────────────────────────────────────────

    private function fetchTraccarSummary(int $deviceId, string $dateFrom, string $dateTo, array $settings): array
    {
        $baseUrl = rtrim($settings['traccar_url'] ?? '', '/');
        $user    = $settings['traccar_usuario']  ?? '';
        $pass    = $settings['traccar_password'] ?? '';

        if (empty($baseUrl)) return [];

        $from = $dateFrom . 'T00:00:00Z';
        $to   = $dateTo   . 'T23:59:59Z';

        $url = $baseUrl . '/api/reports/summary?deviceId=' . $deviceId
             . '&from=' . urlencode($from)
             . '&to='   . urlencode($to);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERPWD        => $user . ':' . $pass,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $code === 0 || $code === 401) return [];

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) return [];

        // API returns an array; take first element for the device
        return $decoded[0] ?? [];
    }
}
