<?php
class WarehouseController extends BaseController
{
    private Supply $supplyModel;

    public function __construct()
    {
        $this->supplyModel = new Supply();
    }

    public function index(): void
    {
        $this->requireAuth();

        $perPage  = 20;
        $page     = $this->currentPage();
        $filters  = [
            'categoria' => $_GET['categoria'] ?? '',
            'buscar'    => $_GET['buscar']    ?? '',
            'alerta'    => $_GET['alerta']    ?? '',
        ];

        $all    = $this->supplyModel->getAllWithStatus(0, 0, $filters);
        $total  = count($all);
        $pages  = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $suministros = array_slice($all, $offset, $perPage);

        $lowStock  = $this->supplyModel->getLowStock();
        $recientes = $this->supplyModel->getMovimientosRecientes(10);
        $users     = (new User())->getAllActive();
        $oficiales = $this->getOficiales();

        $catSuministros = [];
        try {
            $catSuministros = (new Setting())->getCatalogByType('suministros_categoria');
        } catch (\Throwable $e) {
            // catalogos table may not exist
        }

        $this->render('warehouse/index', [
            'title'          => 'Almacén',
            'flash'          => $this->getFlash(),
            'suministros'    => $suministros,
            'lowStock'       => $lowStock,
            'recientes'      => $recientes,
            'filters'        => $filters,
            'page'           => $page,
            'pages'          => $pages,
            'total'          => $total,
            'users'          => $users,
            'oficiales'      => $oficiales,
            'catSuministros' => $catSuministros,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['superadmin', 'admin', 'almacen']);

        $catSuministros = [];
        try {
            $settingModel   = new Setting();
            $catSuministros = $settingModel->getCatalogByType('suministros_categoria');
        } catch (\Throwable $e) {
            // catalogos table may not exist
        }

        $this->render('warehouse/create', [
            'title'          => 'Nuevo Suministro',
            'flash'          => $this->getFlash(),
            'csrf'           => $this->csrfToken(),
            'catSuministros' => $catSuministros,
        ]);
    }

    public function store(): void
    {
        $this->requireRole(['superadmin', 'admin', 'almacen']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('almacen/crear');
        }

        $data = [
            'nombre'          => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'categoria'       => $_POST['categoria'] ?? 'otro',
            'unidad'          => htmlspecialchars(trim($_POST['unidad'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'stock_actual'    => (int) ($_POST['stock_actual'] ?? 0),
            'stock_minimo'    => (int) ($_POST['stock_minimo'] ?? 0),
            'stock_maximo'    => (int) ($_POST['stock_maximo'] ?? 0),
            'ubicacion'       => htmlspecialchars(trim($_POST['ubicacion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'proveedor'       => htmlspecialchars(trim($_POST['proveedor'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'precio_unitario' => !empty($_POST['precio_unitario']) ? (float)$_POST['precio_unitario'] : null,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        if (empty($data['nombre'])) {
            $this->setFlash('error', 'El nombre es obligatorio.');
            $this->redirect('almacen/crear');
        }

        $this->supplyModel->insert($data);
        $this->setFlash('success', 'Suministro registrado correctamente.');
        $this->redirect('almacen');
    }

    public function edit(): void
    {
        $this->requireRole(['superadmin', 'admin', 'almacen']);

        $id         = (int) ($_GET['id'] ?? 0);
        $suministro = $this->supplyModel->findById($id);

        if (!$suministro) {
            $this->setFlash('error', 'Suministro no encontrado.');
            $this->redirect('almacen');
        }

        $movimientos    = $this->supplyModel->getMovimientos($id, 15);
        $catSuministros = [];
        try {
            $settingModel   = new Setting();
            $catSuministros = $settingModel->getCatalogByType('suministros_categoria');
        } catch (\Throwable $e) {
            // catalogos table may not exist
        }

        $this->render('warehouse/edit', [
            'title'          => 'Editar Suministro',
            'flash'          => $this->getFlash(),
            'suministro'     => $suministro,
            'movimientos'    => $movimientos,
            'csrf'           => $this->csrfToken(),
            'catSuministros' => $catSuministros,
        ]);
    }

    public function update(): void
    {
        $this->requireRole(['superadmin', 'admin', 'almacen']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('almacen');
        }

        $id   = (int) ($_POST['id'] ?? 0);
        $data = [
            'nombre'          => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'categoria'       => $_POST['categoria'] ?? 'otro',
            'unidad'          => htmlspecialchars(trim($_POST['unidad'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'stock_minimo'    => (int) ($_POST['stock_minimo'] ?? 0),
            'stock_maximo'    => (int) ($_POST['stock_maximo'] ?? 0),
            'ubicacion'       => htmlspecialchars(trim($_POST['ubicacion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'proveedor'       => htmlspecialchars(trim($_POST['proveedor'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'precio_unitario' => !empty($_POST['precio_unitario']) ? (float)$_POST['precio_unitario'] : null,
        ];

        $this->supplyModel->update($id, $data);
        $this->setFlash('success', 'Suministro actualizado.');
        $this->redirect('almacen');
    }

    public function movement(): void
    {
        $this->requireRole(['superadmin', 'admin', 'almacen']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('almacen');
        }

        try {
            $movData = [
                'suministro_id'  => (int) ($_POST['suministro_id'] ?? 0),
                'tipo'           => $_POST['tipo'] ?? 'entrada',
                'cantidad'       => (int) ($_POST['cantidad'] ?? 0),
                'responsable_id' => $_SESSION['user_id'] ?? null,
                'oficial_id'     => !empty($_POST['oficial_id']) ? (int)$_POST['oficial_id'] : null,
                'motivo'         => htmlspecialchars(trim($_POST['motivo'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'fecha'          => date('Y-m-d H:i:s'),
                'notas'          => htmlspecialchars(trim($_POST['notas'] ?? ''), ENT_QUOTES, 'UTF-8'),
            ];

            if ($movData['cantidad'] <= 0) throw new \RuntimeException("La cantidad debe ser mayor a cero.");
            if ($movData['suministro_id'] <= 0) throw new \RuntimeException("Seleccione un suministro.");

            $this->supplyModel->registrarMovimiento($movData);
            $this->setFlash('success', 'Movimiento registrado correctamente.');
        } catch (\RuntimeException $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('almacen');
    }

    public function delete(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $this->supplyModel->delete($id);
            $this->setFlash('success', 'Suministro eliminado.');
        }
        $this->redirect('almacen');
    }

    private function getOficiales(): array
    {
        $db   = Database::getInstance();
        $stmt = $db->query("SELECT * FROM oficiales WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }
}
