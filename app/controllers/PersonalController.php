<?php
class PersonalController extends BaseController
{
    private Personal $personalModel;
    private Setting  $settingModel;

    public function __construct()
    {
        $this->personalModel = new Personal();
        $this->settingModel  = new Setting();
    }

    public function index(): void
    {
        $this->requireAuth();

        $personal = $this->personalModel->getAllActive();
        $cargos   = $this->getCargos();

        $this->render('personal/index', [
            'title'    => 'Personal',
            'flash'    => $this->getFlash(),
            'personal' => $personal,
            'cargos'   => $cargos,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $cargos = $this->getCargos();

        $this->render('personal/create', [
            'title'  => 'Nuevo Personal',
            'flash'  => $this->getFlash(),
            'cargos' => $cargos,
            'csrf'   => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('personal/crear');
        }

        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        if (empty($nombre)) {
            $this->setFlash('error', 'El nombre es obligatorio.');
            $this->redirect('personal/crear');
        }

        $data = [
            'nombre'          => $nombre,
            'apellidos'       => htmlspecialchars(trim($_POST['apellidos']       ?? ''), ENT_QUOTES, 'UTF-8'),
            'cargo'           => htmlspecialchars(trim($_POST['cargo']           ?? ''), ENT_QUOTES, 'UTF-8'),
            'email'           => htmlspecialchars(trim($_POST['email']           ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'telefono'        => htmlspecialchars(trim($_POST['telefono']        ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'numero_empleado' => htmlspecialchars(trim($_POST['numero_empleado'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'activo'          => 1,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $this->personalModel->insert($data);
        $this->setFlash('success', 'Personal registrado correctamente.');
        $this->redirect('personal');
    }

    public function edit(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id      = (int) ($_GET['id'] ?? 0);
        $persona = $this->personalModel->findById($id);
        if (!$persona) {
            $this->setFlash('error', 'Registro no encontrado.');
            $this->redirect('personal');
        }

        $cargos = $this->getCargos();

        $this->render('personal/edit', [
            'title'   => 'Editar Personal',
            'flash'   => $this->getFlash(),
            'persona' => $persona,
            'cargos'  => $cargos,
            'csrf'    => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('personal');
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        if (!$id || empty($nombre)) {
            $this->setFlash('error', 'Datos inválidos.');
            $this->redirect('personal');
        }

        $data = [
            'nombre'          => $nombre,
            'apellidos'       => htmlspecialchars(trim($_POST['apellidos']       ?? ''), ENT_QUOTES, 'UTF-8'),
            'cargo'           => htmlspecialchars(trim($_POST['cargo']           ?? ''), ENT_QUOTES, 'UTF-8'),
            'email'           => htmlspecialchars(trim($_POST['email']           ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'telefono'        => htmlspecialchars(trim($_POST['telefono']        ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'numero_empleado' => htmlspecialchars(trim($_POST['numero_empleado'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'activo'          => (int) ($_POST['activo'] ?? 1),
        ];

        $this->personalModel->update($id, $data);
        $this->setFlash('success', 'Personal actualizado correctamente.');
        $this->redirect('personal');
    }

    public function delete(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $this->personalModel->update($id, ['activo' => 0]);
            $this->setFlash('success', 'Personal dado de baja.');
        }
        $this->redirect('personal');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getCargos(): array
    {
        try {
            return $this->settingModel->getCatalogByType('personal_cargo');
        } catch (\Throwable $e) {
            return [];
        }
    }
}
