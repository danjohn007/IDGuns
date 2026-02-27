<?php
class VehicleController extends BaseController
{
    private Vehicle $vehicleModel;

    public function __construct()
    {
        $this->vehicleModel = new Vehicle();
    }

    public function index(): void
    {
        $this->requireAuth();

        $perPage  = 20;
        $page     = $this->currentPage();
        $filters  = [
            'estado' => $_GET['estado'] ?? '',
            'tipo'   => $_GET['tipo']   ?? '',
            'buscar' => $_GET['buscar'] ?? '',
        ];

        $all      = $this->vehicleModel->getWithDetails($filters);
        $total    = count($all);
        $pages    = (int) ceil($total / $perPage);
        $vehiculos= array_slice($all, ($page - 1) * $perPage, $perPage);

        $users    = (new User())->getAllActive();

        $this->render('vehicle/index', [
            'title'    => 'Vehículos',
            'flash'    => $this->getFlash(),
            'vehiculos'=> $vehiculos,
            'filters'  => $filters,
            'page'     => $page,
            'pages'    => $pages,
            'total'    => $total,
            'users'    => $users,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $personal    = $this->getPersonal();
        $catVehiculos = $this->getCatalog('vehiculos_tipo');

        $this->render('vehicle/create', [
            'title'        => 'Nuevo Vehículo',
            'flash'        => $this->getFlash(),
            'personal'     => $personal,
            'catVehiculos' => $catVehiculos,
            'csrf'         => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('vehiculos/crear');
        }

        $assetModel = new Asset();

        // First create activo
        $assetData = [
            'codigo'           => $assetModel->generateCode('vehiculo'),
            'nombre'           => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'categoria'        => 'vehiculo',
            'marca'            => htmlspecialchars(trim($_POST['marca'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'modelo'           => htmlspecialchars(trim($_POST['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'serie'            => htmlspecialchars(trim($_POST['serie'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'estado'           => 'activo',
            'color'            => htmlspecialchars(trim($_POST['color'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'responsable_id'   => !empty($_POST['responsable_id']) ? (int)$_POST['responsable_id'] : null,
            'ubicacion'        => htmlspecialchars(trim($_POST['ubicacion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'descripcion'      => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'fecha_adquisicion'=> $_POST['fecha_adquisicion'] ?? null,
            'valor'            => !empty($_POST['valor']) ? (float)$_POST['valor'] : null,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        $activoId = $assetModel->insert($assetData);

        // Set personal_id separately (column may not exist until migration v3 is run)
        if (!empty($_POST['personal_id'])) {
            try {
                Database::getInstance()->prepare(
                    "UPDATE activos SET personal_id = :pid WHERE id = :id"
                )->execute([':pid' => (int)$_POST['personal_id'], ':id' => $activoId]);
            } catch (\Throwable $e) { /* column not yet available */ }
        }

        // Then create vehiculo record
        $vehicleData = [
            'activo_id'      => $activoId,
            'tipo'           => $_POST['tipo'] ?? 'patrulla',
            'placas'         => htmlspecialchars(trim($_POST['placas'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'año'            => (int) ($_POST['anio'] ?? date('Y')),
            'color'          => htmlspecialchars(trim($_POST['color'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'estado'         => $_POST['estado'] ?? 'operativo',
            'kilometraje'    => (int) ($_POST['kilometraje'] ?? 0),
            'responsable_id' => !empty($_POST['responsable_id']) ? (int)$_POST['responsable_id'] : null,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        $vehicleId = $this->vehicleModel->insert($vehicleData);

        // Set personal_id on vehiculo separately
        if (!empty($_POST['personal_id'])) {
            try {
                Database::getInstance()->prepare(
                    "UPDATE vehiculos SET personal_id = :pid WHERE id = :id"
                )->execute([':pid' => (int)$_POST['personal_id'], ':id' => $vehicleId]);
            } catch (\Throwable $e) { /* column not yet available */ }
        }

        $this->setFlash('success', 'Vehículo registrado correctamente.');
        $this->redirect('vehiculos');
    }

    public function edit(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id      = (int) ($_GET['id'] ?? 0);
        $vehicle = $this->vehicleModel->getById($id);

        if (!$vehicle) {
            $this->setFlash('error', 'Vehículo no encontrado.');
            $this->redirect('vehiculos');
        }

        $mantenimientos = $this->vehicleModel->getMantenimientos($id);
        $combustibles   = $this->vehicleModel->getCombustible($id, 10);
        $personal       = $this->getPersonal();
        $catVehiculos   = $this->getCatalog('vehiculos_tipo');

        $this->render('vehicle/edit', [
            'title'          => 'Editar Vehículo',
            'flash'          => $this->getFlash(),
            'vehicle'        => $vehicle,
            'mantenimientos' => $mantenimientos,
            'combustibles'   => $combustibles,
            'personal'       => $personal,
            'catVehiculos'   => $catVehiculos,
            'csrf'           => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('vehiculos');
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $activoId    = (int) ($_POST['activo_id'] ?? 0);

        // Update asset
        $assetModel = new Asset();
        $assetModel->update($activoId, [
            'nombre'      => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'marca'       => htmlspecialchars(trim($_POST['marca'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'modelo'      => htmlspecialchars(trim($_POST['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'serie'       => htmlspecialchars(trim($_POST['serie'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'color'       => htmlspecialchars(trim($_POST['color'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'ubicacion'   => htmlspecialchars(trim($_POST['ubicacion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'descripcion' => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'valor'       => !empty($_POST['valor']) ? (float)$_POST['valor'] : null,
        ]);

        // Update vehicle
        $this->vehicleModel->update($id, [
            'tipo'           => $_POST['tipo'] ?? 'patrulla',
            'placas'         => htmlspecialchars(trim($_POST['placas'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'año'            => (int) ($_POST['anio'] ?? date('Y')),
            'color'          => htmlspecialchars(trim($_POST['color'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'estado'         => $_POST['estado'] ?? 'operativo',
            'kilometraje'    => (int) ($_POST['kilometraje'] ?? 0),
            'responsable_id' => !empty($_POST['responsable_id']) ? (int)$_POST['responsable_id'] : null,
        ]);

        // Set personal_id separately (column may not exist until migration v3 is run)
        try {
            $personalId = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
            $db = Database::getInstance();
            $db->prepare("UPDATE vehiculos SET personal_id = :pid WHERE id = :id")
               ->execute([':pid' => $personalId, ':id' => $id]);
            $db->prepare("UPDATE activos SET personal_id = :pid WHERE id = :id")
               ->execute([':pid' => $personalId, ':id' => $activoId]);
        } catch (\Throwable $e) { /* column not yet available */ }

        // Add maintenance record if provided
        if (!empty($_POST['mantenimiento_tipo'])) {
            $db = Database::getInstance();
            $db->prepare(
                "INSERT INTO mantenimientos (vehiculo_id,tipo,descripcion,fecha_inicio,fecha_fin,costo,proveedor,estado,created_at)
                 VALUES (:v,:t,:d,:fi,:ff,:c,:p,:e,:cr)"
            )->execute([
                ':v'  => $id,
                ':t'  => $_POST['mantenimiento_tipo'],
                ':d'  => htmlspecialchars($_POST['mantenimiento_desc'] ?? '', ENT_QUOTES, 'UTF-8'),
                ':fi' => $_POST['fecha_inicio'] ?? null,
                ':ff' => $_POST['fecha_fin'] ?? null,
                ':c'  => !empty($_POST['costo']) ? (float)$_POST['costo'] : null,
                ':p'  => htmlspecialchars($_POST['proveedor'] ?? '', ENT_QUOTES, 'UTF-8'),
                ':e'  => $_POST['mantenimiento_estado'] ?? 'pendiente',
                ':cr' => date('Y-m-d H:i:s'),
            ]);
        }

        // Add fuel record if provided
        if (!empty($_POST['litros'])) {
            $db = Database::getInstance();
            $db->prepare(
                "INSERT INTO combustible (vehiculo_id,litros,costo,kilometraje,responsable_id,fecha,created_at)
                 VALUES (:v,:l,:c,:k,:r,:f,:cr)"
            )->execute([
                ':v'  => $id,
                ':l'  => (float)$_POST['litros'],
                ':c'  => !empty($_POST['costo_combustible']) ? (float)$_POST['costo_combustible'] : null,
                ':k'  => (int)($_POST['km_actual'] ?? 0),
                ':r'  => $_SESSION['user_id'] ?? null,
                ':f'  => date('Y-m-d H:i:s'),
                ':cr' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->setFlash('success', 'Vehículo actualizado correctamente.');
        $this->redirect('vehiculos');
    }

    public function export(): void
    {
        $this->requireAuth();

        $filters = [
            'estado' => $_GET['estado'] ?? '',
            'tipo'   => $_GET['tipo']   ?? '',
            'buscar' => $_GET['buscar'] ?? '',
        ];

        $vehiculos = $this->vehicleModel->getWithDetails($filters);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="vehiculos_' . date('Ymd_His') . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
        fputcsv($out, ['Código','Nombre','Placas','Tipo','Año','Color','Estado','Kilómetros','Responsable']);
        foreach ($vehiculos as $v) {
            fputcsv($out, [
                $v['codigo'],
                $v['nombre'] ?? trim(($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? '')),
                $v['placas'] ?? '',
                $v['tipo'],
                $v['año'] ?? '',
                $v['color'] ?? '',
                $v['estado'],
                $v['kilometraje'] ?? 0,
                $v['responsable_nombre'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function delete(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $this->vehicleModel->delete($id);
            $this->setFlash('success', 'Vehículo eliminado.');
        }
        $this->redirect('vehiculos');
    }

    private function getPersonal(): array
    {
        try {
            return (new Personal())->getAllActive();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getCatalog(string $tipo): array
    {
        try {
            return (new Setting())->getCatalogByType($tipo);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
