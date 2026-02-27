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

    public function import(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $this->render('personal/import', [
            'title' => 'Importar Personal desde CSV',
            'flash' => $this->getFlash(),
            'csrf'  => $this->csrfToken(),
        ]);
    }

    public function processImport(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('personal/importar');
        }

        if (empty($_FILES['archivo']['tmp_name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Seleccione un archivo CSV válido.');
            $this->redirect('personal/importar');
        }

        $file = $_FILES['archivo']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->setFlash('error', 'No se pudo leer el archivo.');
            $this->redirect('personal/importar');
        }

        $imported = 0;
        $errors   = 0;
        $row      = 0;

        // Skip header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->setFlash('error', 'El archivo CSV está vacío o es inválido.');
            $this->redirect('personal/importar');
        }

        while (($line = fgetcsv($handle)) !== false) {
            $row++;
            if (count($line) < 2) {
                $errors++;
                continue;
            }
            $nombre = htmlspecialchars(trim($line[0] ?? ''), ENT_QUOTES, 'UTF-8');
            if (empty($nombre)) {
                $errors++;
                continue;
            }
            $data = [
                'nombre'          => $nombre,
                'apellidos'       => htmlspecialchars(trim($line[1] ?? ''), ENT_QUOTES, 'UTF-8'),
                'cargo'           => htmlspecialchars(trim($line[2] ?? ''), ENT_QUOTES, 'UTF-8'),
                'email'           => !empty(trim($line[3] ?? '')) ? htmlspecialchars(trim($line[3]), ENT_QUOTES, 'UTF-8') : null,
                'telefono'        => !empty(trim($line[4] ?? '')) ? htmlspecialchars(trim($line[4]), ENT_QUOTES, 'UTF-8') : null,
                'numero_empleado' => !empty(trim($line[5] ?? '')) ? htmlspecialchars(trim($line[5]), ENT_QUOTES, 'UTF-8') : null,
                'activo'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
            ];
            try {
                $this->personalModel->insert($data);
                $imported++;
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        fclose($handle);

        if ($imported > 0) {
            $this->setFlash('success', "Importación completada: {$imported} registro(s) importado(s)" . ($errors > 0 ? ", {$errors} error(es) omitido(s)." : '.'));
        } else {
            $this->setFlash('error', "No se importó ningún registro. Verifique el formato del archivo CSV.");
        }

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
