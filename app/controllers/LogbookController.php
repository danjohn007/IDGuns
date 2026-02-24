<?php
class LogbookController extends BaseController
{
    private LogEntry $logModel;

    public function __construct()
    {
        $this->logModel = new LogEntry();
    }

    public function index(): void
    {
        $this->requireAuth();

        $perPage = 20;
        $page    = $this->currentPage();
        $filters = [
            'tipo'        => $_GET['tipo']        ?? '',
            'turno'       => $_GET['turno']       ?? '',
            'oficial_id'  => $_GET['oficial_id']  ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
        ];

        $total   = $this->logModel->countFiltered($filters);
        $pages   = (int) ceil($total / $perPage);
        $offset  = ($page - 1) * $perPage;
        $entries = $this->logModel->getWithDetails($filters, $perPage, $offset);

        $oficiales = $this->getOficiales();
        $activos   = (new Asset())->findAll([], 'nombre ASC');

        $this->render('logbook/index', [
            'title'     => 'Bitácora',
            'flash'     => $this->getFlash(),
            'entries'   => $entries,
            'filters'   => $filters,
            'page'      => $page,
            'pages'     => $pages,
            'total'     => $total,
            'oficiales' => $oficiales,
            'activos'   => $activos,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['superadmin', 'admin', 'bitacora']);

        $oficiales = $this->getOficiales();
        $activos   = (new Asset())->findAll([], 'nombre ASC');

        $this->render('logbook/create', [
            'title'     => 'Nueva Entrada de Bitácora',
            'flash'     => $this->getFlash(),
            'oficiales' => $oficiales,
            'activos'   => $activos,
            'csrf'      => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireRole(['superadmin', 'admin', 'bitacora']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('bitacora/crear');
        }

        $data = [
            'tipo'            => $_POST['tipo'] ?? 'entrada',
            'activo_id'       => !empty($_POST['activo_id']) ? (int)$_POST['activo_id'] : null,
            'activo_tipo'     => htmlspecialchars(trim($_POST['activo_tipo'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'responsable_id'  => $_SESSION['user_id'] ?? null,
            'oficial_id'      => !empty($_POST['oficial_id']) ? (int)$_POST['oficial_id'] : null,
            'descripcion'     => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'estado_anterior' => htmlspecialchars(trim($_POST['estado_anterior'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'estado_nuevo'    => htmlspecialchars(trim($_POST['estado_nuevo'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'turno'           => $_POST['turno'] ?? 'matutino',
            'fecha'           => !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        if (empty($data['descripcion'])) {
            $this->setFlash('error', 'La descripción es obligatoria.');
            $this->redirect('bitacora/crear');
        }

        $this->logModel->insert($data);
        $this->setFlash('success', 'Entrada registrada en la bitácora.');
        $this->redirect('bitacora');
    }

    private function getOficiales(): array
    {
        $db   = Database::getInstance();
        $stmt = $db->query("SELECT * FROM oficiales WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }
}
