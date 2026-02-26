<?php
class InventoryController extends BaseController
{
    private Asset     $assetModel;
    private Weapon    $weaponModel;
    private GpsDevice $gpsModel;

    public function __construct()
    {
        $this->assetModel  = new Asset();
        $this->weaponModel = new Weapon();
        $this->gpsModel    = new GpsDevice();
    }

    public function index(): void
    {
        $this->requireAuth();

        $perPage  = 20;
        $page     = $this->currentPage();
        $filters  = [
            'categoria' => $_GET['categoria'] ?? '',
            'estado'    => $_GET['estado']    ?? '',
            'buscar'    => $_GET['buscar']    ?? '',
        ];

        $total  = $this->assetModel->countFiltered($filters);
        $pages  = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $activos= $this->assetModel->getWithResponsable($perPage, $offset, $filters);

        $users  = (new User())->getAllActive();

        $this->render('inventory/index', [
            'title'    => 'Inventario',
            'flash'    => $this->getFlash(),
            'activos'  => $activos,
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

        $users     = (new User())->getAllActive();
        $oficiales = $this->getOficiales();

        $this->render('inventory/create', [
            'title'     => 'Nuevo Activo',
            'flash'     => $this->getFlash(),
            'users'     => $users,
            'oficiales' => $oficiales,
            'csrf'      => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('inventario/crear');
        }

        $categoria = $_POST['categoria'] ?? '';
        $data = [
            'codigo'           => $this->assetModel->generateCode($categoria),
            'nombre'           => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'categoria'        => $categoria,
            'marca'            => htmlspecialchars(trim($_POST['marca'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'modelo'           => htmlspecialchars(trim($_POST['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'serie'            => htmlspecialchars(trim($_POST['serie'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'estado'           => $_POST['estado'] ?? 'activo',
            'responsable_id'   => !empty($_POST['responsable_id']) ? (int)$_POST['responsable_id'] : null,
            'ubicacion'        => htmlspecialchars(trim($_POST['ubicacion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'descripcion'      => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'fecha_adquisicion'=> $_POST['fecha_adquisicion'] ?? null,
            'valor'            => !empty($_POST['valor']) ? (float)$_POST['valor'] : null,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        if (empty($data['nombre'])) {
            $this->setFlash('error', 'El nombre es obligatorio.');
            $this->redirect('inventario/crear');
        }

        $activoId = $this->assetModel->insert($data);

        // If weapon, also insert into armas
        if ($categoria === 'arma' && !empty($_POST['arma_tipo'])) {
            $armaData = [
                'activo_id'           => $activoId,
                'tipo'                => $_POST['arma_tipo'] ?? 'pistola',
                'calibre'             => htmlspecialchars(trim($_POST['calibre'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'numero_serie'        => htmlspecialchars(trim($_POST['arma_serie'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'estado'              => 'operativa',
                'oficial_asignado_id' => !empty($_POST['oficial_asignado_id']) ? (int)$_POST['oficial_asignado_id'] : null,
                'municiones_asignadas'=> (int)($_POST['municiones_asignadas'] ?? 0),
                'created_at'          => date('Y-m-d H:i:s'),
            ];
            $this->weaponModel->insert($armaData);
        }

        // If vehicle, also insert into vehiculos
        if ($categoria === 'vehiculo' && !empty($_POST['vehiculo_tipo'])) {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO vehiculos (activo_id,tipo,placas,año,color,estado,kilometraje,responsable_id,created_at)
                 VALUES (:a,:t,:p,:y,:c,:e,:k,:r,:cr)"
            );
            $stmt->execute([
                ':a'  => $activoId,
                ':t'  => $_POST['vehiculo_tipo'] ?? 'patrulla',
                ':p'  => htmlspecialchars(trim($_POST['placas'] ?? ''), ENT_QUOTES, 'UTF-8'),
                ':y'  => (int)($_POST['anio'] ?? date('Y')),
                ':c'  => htmlspecialchars(trim($_POST['color'] ?? ''), ENT_QUOTES, 'UTF-8'),
                ':e'  => 'operativo',
                ':k'  => (int)($_POST['kilometraje'] ?? 0),
                ':r'  => !empty($_POST['responsable_id']) ? (int)$_POST['responsable_id'] : null,
                ':cr' => date('Y-m-d H:i:s'),
            ]);
        }

        // GPS device
        if (!empty($_POST['gps_unique_id'])) {
            try {
                $gpsData = [
                    'nombre'             => htmlspecialchars(trim($_POST['gps_nombre'] ?? $data['nombre']), ENT_QUOTES, 'UTF-8'),
                    'unique_id'          => htmlspecialchars(trim($_POST['gps_unique_id']), ENT_QUOTES, 'UTF-8'),
                    'traccar_device_id'  => !empty($_POST['gps_traccar_id']) ? (int)$_POST['gps_traccar_id'] : null,
                    'telefono'           => htmlspecialchars(trim($_POST['gps_telefono'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'modelo_dispositivo' => htmlspecialchars(trim($_POST['gps_modelo'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'categoria_traccar'  => $_POST['gps_categoria'] ?? 'car',
                    'contacto'           => htmlspecialchars(trim($_POST['gps_contacto'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'grupo_id'           => !empty($_POST['gps_grupo_id']) ? (int)$_POST['gps_grupo_id'] : null,
                    'activo'             => 1,
                ];
                $this->gpsModel->upsertForActivo($activoId, $gpsData);
            } catch (\Throwable $e) {
                // GPS table may not exist yet (migration pending) — skip silently
            }
        }

        $this->setFlash('success', 'Activo registrado correctamente.');
        $this->redirect('inventario');
    }

    public function edit(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id     = (int) ($_GET['id'] ?? 0);
        $activo = $this->assetModel->findById($id);
        if (!$activo) {
            $this->setFlash('error', 'Activo no encontrado.');
            $this->redirect('inventario');
        }

        $arma      = $this->weaponModel->queryOne("SELECT * FROM armas WHERE activo_id = :a", [':a' => $id]);
        $gpsDevice = null;
        try {
            $gpsDevice = $this->gpsModel->findByActivoId($id);
        } catch (\Throwable $e) {
            // GPS table may not exist yet (migration pending) — continue without GPS data
        }
        $users     = (new User())->getAllActive();
        $oficiales = $this->getOficiales();

        $this->render('inventory/edit', [
            'title'     => 'Editar Activo',
            'flash'     => $this->getFlash(),
            'activo'    => $activo,
            'arma'      => $arma,
            'gpsDevice' => $gpsDevice,
            'users'     => $users,
            'oficiales' => $oficiales,
            'csrf'      => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('inventario');
        }

        $id   = (int) ($_POST['id'] ?? 0);
        $data = [
            'nombre'           => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'marca'            => htmlspecialchars(trim($_POST['marca'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'modelo'           => htmlspecialchars(trim($_POST['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'serie'            => htmlspecialchars(trim($_POST['serie'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'estado'           => $_POST['estado'] ?? 'activo',
            'responsable_id'   => !empty($_POST['responsable_id']) ? (int)$_POST['responsable_id'] : null,
            'ubicacion'        => htmlspecialchars(trim($_POST['ubicacion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'descripcion'      => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'fecha_adquisicion'=> $_POST['fecha_adquisicion'] ?? null,
            'valor'            => !empty($_POST['valor']) ? (float)$_POST['valor'] : null,
        ];

        $this->assetModel->update($id, $data);

        // Update weapon details if applicable
        if (!empty($_POST['arma_id'])) {
            $armaId   = (int) $_POST['arma_id'];
            $armaData = [
                'tipo'                => $_POST['arma_tipo'] ?? 'pistola',
                'calibre'             => htmlspecialchars(trim($_POST['calibre'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'numero_serie'        => htmlspecialchars(trim($_POST['arma_serie'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'estado'              => $_POST['arma_estado'] ?? 'operativa',
                'oficial_asignado_id' => !empty($_POST['oficial_asignado_id']) ? (int)$_POST['oficial_asignado_id'] : null,
                'municiones_asignadas'=> (int)($_POST['municiones_asignadas'] ?? 0),
            ];
            $this->weaponModel->update($armaId, $armaData);
        }

        // GPS device
        if (!empty($_POST['gps_unique_id'])) {
            try {
                $gpsData = [
                    'nombre'             => htmlspecialchars(trim($_POST['gps_nombre'] ?? $data['nombre']), ENT_QUOTES, 'UTF-8'),
                    'unique_id'          => htmlspecialchars(trim($_POST['gps_unique_id']), ENT_QUOTES, 'UTF-8'),
                    'traccar_device_id'  => !empty($_POST['gps_traccar_id']) ? (int)$_POST['gps_traccar_id'] : null,
                    'telefono'           => htmlspecialchars(trim($_POST['gps_telefono'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'modelo_dispositivo' => htmlspecialchars(trim($_POST['gps_modelo'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'categoria_traccar'  => $_POST['gps_categoria'] ?? 'car',
                    'contacto'           => htmlspecialchars(trim($_POST['gps_contacto'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'grupo_id'           => !empty($_POST['gps_grupo_id']) ? (int)$_POST['gps_grupo_id'] : null,
                ];
                if (!empty($_POST['gps_device_id'])) {
                    $this->gpsModel->update((int)$_POST['gps_device_id'], $gpsData);
                } else {
                    $this->gpsModel->upsertForActivo($id, $gpsData);
                }
            } catch (\Throwable $e) {
                // GPS table may not exist yet (migration pending) — skip silently
            }
        }

        $this->setFlash('success', 'Activo actualizado correctamente.');
        $this->redirect('inventario');
    }

    public function delete(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
        if ($id) {
            $this->assetModel->delete($id);
            $this->setFlash('success', 'Activo eliminado.');
        }
        $this->redirect('inventario');
    }

    private function getOficiales(): array
    {
        $db   = Database::getInstance();
        $stmt = $db->query("SELECT * FROM oficiales WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }
}
