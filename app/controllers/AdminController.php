<?php
class AdminController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $db = Database::getInstance();

        $totalUsers     = $this->userModel->count();
        $totalActivos   = (new Asset())->count();
        $totalArmas     = (new Weapon())->count();
        $totalVehiculos = (new Vehicle())->count();
        $totalSuministros = (new Supply())->count();

        // Recent system activity (last 20 log entries)
        $activity = (new LogEntry())->getRecent(15);

        // Recent errors
        $errors = (new Setting())->getErrors(10);

        $this->render('admin/index', [
            'title'            => 'Administración',
            'flash'            => $this->getFlash(),
            'totalUsers'       => $totalUsers,
            'totalActivos'     => $totalActivos,
            'totalArmas'       => $totalArmas,
            'totalVehiculos'   => $totalVehiculos,
            'totalSuministros' => $totalSuministros,
            'activity'         => $activity,
            'errors'           => $errors,
        ]);
    }

    public function users(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $users = $this->userModel->findAll([], 'nombre ASC');

        $this->render('admin/users', [
            'title' => 'Gestión de Usuarios',
            'flash' => $this->getFlash(),
            'users' => $users,
            'csrf'  => $this->csrfToken(),
        ]);
    }

    public function createUser(): void
    {
        $this->requireRole(['superadmin']);

        $this->render('admin/users', [
            'title'  => 'Nuevo Usuario',
            'flash'  => $this->getFlash(),
            'users'  => $this->userModel->findAll([], 'nombre ASC'),
            'create' => true,
            'csrf'   => $this->csrfToken(),
        ]);
    }

    public function storeUser(): void
    {
        $this->requireRole(['superadmin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('admin/usuarios');
        }

        $username = trim($_POST['username'] ?? '');
        $nombre   = trim($_POST['nombre']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol      = $_POST['rol'] ?? 'bitacora';

        if (empty($username) || empty($nombre) || empty($password)) {
            $this->setFlash('error', 'Usuario, nombre y contraseña son obligatorios.');
            $this->redirect('admin/usuarios');
        }

        // Check unique username
        $exists = $this->userModel->findByUsername($username);
        if ($exists) {
            $this->setFlash('error', 'El nombre de usuario ya existe.');
            $this->redirect('admin/usuarios');
        }

        $this->userModel->createUser([
            'nombre'   => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            'email'    => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            'password' => $password,
            'rol'      => $rol,
            'activo'   => 1,
        ]);

        $this->setFlash('success', 'Usuario creado correctamente.');
        $this->redirect('admin/usuarios');
    }

    public function editUser(): void
    {
        $this->requireRole(['superadmin']);

        $id   = (int) ($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->setFlash('error', 'Usuario no encontrado.');
            $this->redirect('admin/usuarios');
        }

        $this->render('admin/users', [
            'title'    => 'Editar Usuario',
            'flash'    => $this->getFlash(),
            'users'    => $this->userModel->findAll([], 'nombre ASC'),
            'editUser' => $user,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    public function updateUser(): void
    {
        $this->requireRole(['superadmin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('admin/usuarios');
        }

        $id   = (int) ($_POST['id'] ?? 0);
        $data = [
            'nombre' => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email'  => htmlspecialchars(trim($_POST['email']  ?? ''), ENT_QUOTES, 'UTF-8'),
            'rol'    => $_POST['rol']    ?? 'bitacora',
            'activo' => (int) ($_POST['activo'] ?? 1),
        ];

        $this->userModel->update($id, $data);

        // Change password if provided
        if (!empty($_POST['password'])) {
            $this->userModel->updatePassword($id, $_POST['password']);
        }

        $this->setFlash('success', 'Usuario actualizado correctamente.');
        $this->redirect('admin/usuarios');
    }

    public function deleteUser(): void
    {
        $this->requireRole(['superadmin']);

        $id = (int) ($_GET['id'] ?? 0);
        // Cannot delete own account
        if ($id && $id !== (int) $_SESSION['user_id']) {
            $this->userModel->delete($id);
            $this->setFlash('success', 'Usuario eliminado.');
        } else {
            $this->setFlash('error', 'No puede eliminar su propio usuario.');
        }
        $this->redirect('admin/usuarios');
    }

    public function reports(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $db = Database::getInstance();

        // Asset summary by category
        $assetsByCategory = $db->query(
            "SELECT categoria, estado, COUNT(*) AS total FROM activos GROUP BY categoria, estado ORDER BY categoria"
        )->fetchAll();

        // Weapons summary
        $weaponsSummary = $db->query(
            "SELECT tipo, calibre, estado, COUNT(*) AS total FROM armas GROUP BY tipo, calibre, estado"
        )->fetchAll();

        // Vehicles summary
        $vehiclesSummary = $db->query(
            "SELECT tipo, estado, COUNT(*) AS total FROM vehiculos GROUP BY tipo, estado"
        )->fetchAll();

        // Low stock supplies
        $lowStock = $db->query(
            "SELECT nombre, categoria, stock_actual, stock_minimo FROM suministros WHERE stock_actual <= stock_minimo ORDER BY stock_actual ASC"
        )->fetchAll();

        // Monthly movements
        $monthlyMovements = $db->query(
            "SELECT DATE_FORMAT(fecha,'%Y-%m') AS mes, tipo, SUM(cantidad) AS total
             FROM movimientos_almacen
             WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY mes, tipo ORDER BY mes DESC"
        )->fetchAll();

        $this->render('admin/reports', [
            'title'            => 'Reportes',
            'flash'            => $this->getFlash(),
            'assetsByCategory' => $assetsByCategory,
            'weaponsSummary'   => $weaponsSummary,
            'vehiclesSummary'  => $vehiclesSummary,
            'lowStock'         => $lowStock,
            'monthlyMovements' => $monthlyMovements,
        ]);
    }
}
