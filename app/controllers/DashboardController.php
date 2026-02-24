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
}
