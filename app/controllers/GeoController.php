<?php
class GeoController extends BaseController
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

        $devices = [];
        try {
            $devices = $this->gpsModel->getAllWithAsset();
        } catch (\Throwable $e) {
            // GPS table may not exist yet (migration pending) — show empty map
        }
        $settings = $this->settingModel->getAllGrouped();

        $traccarUrl = rtrim($settings['traccar_url'] ?? '', '/');
        $timezone   = $settings['app_timezone'] ?? 'America/Mexico_City';

        $this->render('geo/index', [
            'title'      => 'Geolocalización',
            'flash'      => $this->getFlash(),
            'devices'    => $devices,
            'traccarUrl' => $traccarUrl,
            'timezone'   => $timezone,
            'csrf'       => $this->csrfToken(),
        ]);
    }

    /**
     * Proxy: GET /geolocalizacion/positions
     * Returns current positions from Traccar API as JSON.
     */
    public function positions(): void
    {
        $this->requireAuth();
        $data = $this->traccarGet('/api/positions');
        $this->json($data);
    }

    /**
     * Proxy: GET /geolocalizacion/ruta?deviceId=X&from=Y&to=Z
     * Returns route (positions) for a device and date range.
     */
    public function route(): void
    {
        $this->requireAuth();

        $deviceId = (int) ($_GET['deviceId'] ?? 0);
        $from     = $_GET['from'] ?? date('Y-m-d') . 'T00:00:00Z';
        $to       = $_GET['to']   ?? date('Y-m-d') . 'T23:59:59Z';

        if (!$deviceId) {
            $this->json(['error' => 'deviceId requerido'], 400);
        }

        $data = $this->traccarGet(
            '/api/reports/route?deviceId=' . $deviceId
            . '&from=' . urlencode($from)
            . '&to='   . urlencode($to)
        );
        $this->json($data);
    }

    /**
     * Proxy: GET /geolocalizacion/resumen?deviceId=X&from=Y&to=Z
     * Returns route summary (distance, engineHours, maxSpeed) for a device and date range.
     */
    public function summary(): void
    {
        $this->requireAuth();

        $deviceId = (int) ($_GET['deviceId'] ?? 0);
        $from     = $_GET['from'] ?? date('Y-m-01') . 'T00:00:00Z';
        $to       = $_GET['to']   ?? date('Y-m-d')  . 'T23:59:59Z';

        if (!$deviceId) {
            $this->json(['error' => 'deviceId requerido'], 400);
        }

        $data = $this->traccarGet(
            '/api/reports/summary?deviceId=' . $deviceId
            . '&from=' . urlencode($from)
            . '&to='   . urlencode($to)
        );
        $this->json($data);
    }

    /**
     * Proxy: GET /geolocalizacion/dispositivos
     * Returns devices list from Traccar API.
     */
    public function apiDevices(): void
    {
        $this->requireAuth();
        $data = $this->traccarGet('/api/devices');
        $this->json($data);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function traccarGet(string $endpoint): array
    {
        $settings = $this->settingModel->getAllGrouped();
        $baseUrl  = rtrim($settings['traccar_url'] ?? '', '/');
        $user     = $settings['traccar_usuario']  ?? '';
        $pass     = $settings['traccar_password'] ?? '';

        if (empty($baseUrl)) {
            return ['error' => 'Servidor Traccar no configurado'];
        }

        $url = $baseUrl . $endpoint;
        $ch  = curl_init($url);
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

        if ($error || $code === 0) {
            return ['error' => 'No se pudo conectar al servidor Traccar: ' . $error];
        }
        if ($code === 401) {
            return ['error' => 'Credenciales Traccar incorrectas (401)'];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['error' => 'Respuesta inválida del servidor Traccar'];
        }
        return $decoded;
    }
}
