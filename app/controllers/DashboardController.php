<?php
class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $assetModel  = new Asset();
        $weaponModel = new Weapon();
        $vehicleModel= new Vehicle();
        $supplyModel = new Supply();
        $logModel    = new LogEntry();

        // Stats cards
        $totalActivos      = $assetModel->count();
        $armasOperativas   = $weaponModel->countOperativas();
        $vehiculosOper     = $vehicleModel->countOperativos();
        $alertasStock      = $supplyModel->countLowStock();

        // Chart: assets by category
        $categoriaStats    = $assetModel->getCategoryStats();
        $catLabels         = array_column($categoriaStats, 'categoria');
        $catValues         = array_column($categoriaStats, 'total');

        // Chart: warehouse movements last 7 days
        $movimientosDia    = $supplyModel->getMovimientosPorDia(7);

        // Recent logbook entries
        $recentBitacora    = $logModel->getRecent(8);

        // Low stock alerts
        $lowStock          = $supplyModel->getLowStock();

        // Asset status breakdown
        $statusStats       = $assetModel->getStatsByStatus();

        $this->render('dashboard/index', [
            'title'           => 'Dashboard',
            'flash'           => $this->getFlash(),
            'totalActivos'    => $totalActivos,
            'armasOperativas' => $armasOperativas,
            'vehiculosOper'   => $vehiculosOper,
            'alertasStock'    => $alertasStock,
            'catLabels'       => json_encode($catLabels),
            'catValues'       => json_encode($catValues),
            'movimientosDia'  => json_encode($movimientosDia),
            'recentBitacora'  => $recentBitacora,
            'lowStock'        => $lowStock,
            'statusStats'     => $statusStats,
        ]);
    }

    public function search(): void
    {
        $this->requireAuth();

        $q = trim($_GET['q'] ?? '');

        $results = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $db   = Database::getInstance();

            // Search activos
            $stmt = $db->prepare(
                "SELECT 'activo' AS tipo, id, codigo, nombre, categoria AS extra, estado
                 FROM activos
                 WHERE nombre LIKE :q OR codigo LIKE :q2 OR serie LIKE :q3
                 LIMIT 10"
            );
            $stmt->execute([':q'=>$like,':q2'=>$like,':q3'=>$like]);
            $results = array_merge($results, $stmt->fetchAll());

            // Search vehiculos
            $stmt = $db->prepare(
                "SELECT 'vehiculo' AS tipo, v.id, a.codigo, a.nombre, v.placas AS extra, v.estado
                 FROM vehiculos v JOIN activos a ON v.activo_id = a.id
                 WHERE v.placas LIKE :q OR a.nombre LIKE :q2 OR a.codigo LIKE :q3
                 LIMIT 10"
            );
            $stmt->execute([':q'=>$like,':q2'=>$like,':q3'=>$like]);
            $results = array_merge($results, $stmt->fetchAll());

            // Search suministros
            $stmt = $db->prepare(
                "SELECT 'suministro' AS tipo, id, '' AS codigo, nombre, categoria AS extra, '' AS estado
                 FROM suministros
                 WHERE nombre LIKE :q OR categoria LIKE :q2
                 LIMIT 10"
            );
            $stmt->execute([':q'=>$like,':q2'=>$like]);
            $results = array_merge($results, $stmt->fetchAll());

            // Search oficiales
            $stmt = $db->prepare(
                "SELECT 'oficial' AS tipo, id, placa AS codigo, CONCAT(nombre,' ',apellidos) AS nombre, rango AS extra, '' AS estado
                 FROM oficiales
                 WHERE nombre LIKE :q OR apellidos LIKE :q2 OR placa LIKE :q3
                 LIMIT 10"
            );
            $stmt->execute([':q'=>$like,':q2'=>$like,':q3'=>$like]);
            $results = array_merge($results, $stmt->fetchAll());
        }

        $this->render('dashboard/search', [
            'title'   => 'Búsqueda Global',
            'flash'   => $this->getFlash(),
            'q'       => $q,
            'results' => $results,
        ]);
    }
}
