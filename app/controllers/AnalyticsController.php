<?php
class AnalyticsController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $db = Database::getInstance();

        // Assets summary
        $assetsByCategory = (new Asset())->getCategoryStats();
        $assetsByStatus   = (new Asset())->getStatsByStatus();

        // Vehicles summary
        $vehiclesByType = $db->query(
            "SELECT tipo, COUNT(*) AS total FROM vehiculos GROUP BY tipo ORDER BY total DESC"
        )->fetchAll();
        $vehiclesByStatus = $db->query(
            "SELECT estado, COUNT(*) AS total FROM vehiculos GROUP BY estado"
        )->fetchAll();

        // Weapons summary
        $weaponsByType = $db->query(
            "SELECT tipo, COUNT(*) AS total FROM armas GROUP BY tipo ORDER BY total DESC"
        )->fetchAll();

        // Warehouse movements last 30 days
        $movimientos30 = $db->query(
            "SELECT DATE(fecha) AS dia, tipo, SUM(cantidad) AS total
             FROM movimientos_almacen
             WHERE fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY dia, tipo
             ORDER BY dia ASC"
        )->fetchAll();

        // Low stock count
        $lowStockCount = (new Supply())->countLowStock();

        // Top 5 activos más recientes
        $recentActivos = $db->query(
            "SELECT codigo, nombre, categoria, estado, created_at
             FROM activos ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();

        // PySpark API configuration
        $settingModel = new Setting();
        $pysparkUrl   = $settingModel->get('pyspark_url') ?? '';
        $pysparkToken = $settingModel->get('pyspark_token') ?? '';

        // Try to fetch PySpark analysis if configured
        $pysparkData = null;
        if (!empty($pysparkUrl)) {
            $pysparkData = $this->fetchPySparkData($pysparkUrl, $pysparkToken);
        }

        $this->render('analytics/index', [
            'title'            => 'Análisis de Datos',
            'flash'            => $this->getFlash(),
            'assetsByCategory' => json_encode($assetsByCategory),
            'assetsByStatus'   => json_encode($assetsByStatus),
            'vehiclesByType'   => json_encode($vehiclesByType),
            'vehiclesByStatus' => json_encode($vehiclesByStatus),
            'weaponsByType'    => json_encode($weaponsByType),
            'movimientos30'    => json_encode($movimientos30),
            'lowStockCount'    => $lowStockCount,
            'recentActivos'    => $recentActivos,
            'pysparkUrl'       => $pysparkUrl,
            'pysparkData'      => $pysparkData,
            'csrf'             => $this->csrfToken(),
        ]);
    }

    public function pysparkQuery(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->json(['error' => 'Petición inválida.'], 400);
        }

        $settingModel = new Setting();
        $pysparkUrl   = $settingModel->get('pyspark_url') ?? '';
        $pysparkToken = $settingModel->get('pyspark_token') ?? '';

        if (empty($pysparkUrl)) {
            $this->json(['error' => 'La URL del API de PySpark no está configurada.'], 400);
        }

        $query  = htmlspecialchars(trim($_POST['query'] ?? ''), ENT_QUOTES, 'UTF-8');
        $result = $this->fetchPySparkData($pysparkUrl, $pysparkToken, $query);

        $this->json(['result' => $result]);
    }

    private function fetchPySparkData(string $url, string $token, string $query = ''): ?array
    {
        try {
            $headers = ['Content-Type: application/json'];
            if (!empty($token)) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }

            $payload = json_encode(['query' => $query ?: 'summary']);

            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => implode("\r\n", $headers),
                    'content' => $payload,
                    'timeout' => 10,
                    'ignore_errors' => true,
                ],
            ]);

            $response = @file_get_contents($url, false, $ctx);
            if ($response === false) return null;

            $data = json_decode($response, true);
            return is_array($data) ? $data : ['raw' => $response];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
