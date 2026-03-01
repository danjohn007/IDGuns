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
            // Build uniqueId → traccar_id map as fallback for devices without traccar_device_id
            $traccarDeviceMap = $this->fetchTraccarDeviceMap($settings);

            foreach ($devices as $device) {
                $traccarId = (int)($device['traccar_device_id'] ?? 0);
                if (!$traccarId && !empty($device['unique_id'])) {
                    $traccarId = $traccarDeviceMap[(string)$device['unique_id']] ?? 0;
                }
                if (!$traccarId) {
                    // No Traccar ID found: include device with null data
                    $reports[] = [
                        'device'          => $device,
                        'summary'         => [],
                        'km_total'        => null,
                        'litros_estimados'=> null,
                        'costo_estimado'  => null,
                    ];
                    continue;
                }

                $today        = date('Y-m-d');
                $isHistorical = $dateTo < $today;

                // For fully historical periods, try the local cache first
                $stored  = null;
                $summary = [];
                if ($isHistorical) {
                    $stored = $this->gpsModel->findKmReporte((int)$device['id'], $dateFrom, $dateTo);
                }

                if ($stored) {
                    // Build a summary-compatible array from cached data
                    $summary = [
                        'distance'    => (float)($stored['distancia_m']     ?? 0),
                        'engineHours' => (int)($stored['engine_hours_ms']   ?? 0),
                        'maxSpeed'    => (float)($stored['velocidad_max']   ?? 0),
                    ];
                } else {
                    // Query Traccar live
                    $summary = $this->fetchTraccarSummary(
                        $traccarId,
                        $dateFrom,
                        $dateTo,
                        $settings
                    );

                    // Persist to the local DB (cache for historical; refresh for current period)
                    if (!empty($summary) && !isset($summary['error'])) {
                        try {
                            $this->gpsModel->upsertKmReporte([
                                'dispositivo_id'    => (int)$device['id'],
                                'traccar_device_id' => $traccarId,
                                'fecha_desde'       => $dateFrom,
                                'fecha_hasta'       => $dateTo,
                                'distancia_m'       => $summary['distance']    ?? null,
                                'engine_hours_ms'   => $summary['engineHours'] ?? null,
                                'velocidad_max'     => $summary['maxSpeed']    ?? null,
                            ]);
                        } catch (\Throwable $e) {
                            // Non-fatal: table may not exist until migration_v10 runs
                        }
                    }
                }

                $kmTotal = isset($summary['distance']) ? round($summary['distance'] / 1000, 2) : null;
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

    /**
     * Fetch all Traccar devices and return a map of uniqueId → traccar device id.
     * Used as fallback when traccar_device_id is not stored locally.
     */
    private function fetchTraccarDeviceMap(array $settings): array
    {
        $baseUrl = rtrim($settings['traccar_url'] ?? '', '/');
        $user    = $settings['traccar_usuario']  ?? '';
        $pass    = $settings['traccar_password'] ?? '';

        if (empty($baseUrl)) return [];

        $ch = curl_init($baseUrl . '/api/devices');
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

        $map = [];
        foreach ($decoded as $d) {
            if (!empty($d['uniqueId']) && !empty($d['id'])) {
                $map[(string)$d['uniqueId']] = (int)$d['id'];
            }
        }
        return $map;
    }

    private function fetchTraccarSummary(int $deviceId, string $dateFrom, string $dateTo, array $settings): array
    {
        $baseUrl = rtrim($settings['traccar_url'] ?? '', '/');
        $user    = $settings['traccar_usuario']  ?? '';
        $pass    = $settings['traccar_password'] ?? '';

        if (empty($baseUrl)) return [];

        // Build timestamps using the configured local timezone so Traccar
        // returns positions for the correct local day boundaries (not UTC midnight).
        $timezone = $settings['app_timezone'] ?? 'America/Mexico_City';
        try {
            $tz     = new \DateTimeZone($timezone);
            $dt     = new \DateTime('now', $tz);
            $offSec = $dt->getOffset(); // seconds east of UTC
            $sign   = $offSec >= 0 ? '+' : '-';
            $hh     = str_pad((string)abs((int)($offSec / 3600)),        2, '0', STR_PAD_LEFT);
            $mm     = str_pad((string)abs((int)(($offSec % 3600) / 60)), 2, '0', STR_PAD_LEFT);
            $tzStr  = $sign . $hh . ':' . $mm;
        } catch (\Throwable $e) {
            $tzStr = '+00:00'; // fallback: UTC
        }

        $from = $dateFrom . 'T00:00:00' . $tzStr;
        $to   = $dateTo   . 'T23:59:59' . $tzStr;

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

        // Traccar returns an array; handle both array and single-object responses
        if (isset($decoded['distance']) || isset($decoded['deviceId'])) {
            return $decoded; // single summary object
        }
        return $decoded[0] ?? [];
    }
}
