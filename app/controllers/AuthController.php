<?php
class AuthController extends BaseController
{
    public function login(): void
    {
        // Already authenticated
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }

        $error = null;

        if ($this->isPost()) {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = 'Por favor ingrese usuario y contraseña.';
            } else {
                $userModel = new User();
                $user      = $userModel->authenticate($username, $password);

                if ($user) {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_role'] = $user['rol'];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $this->redirect('dashboard');
                } else {
                    $error = 'Usuario o contraseña incorrectos.';
                }
            }
        }

        $this->render('auth/login', ['error' => $error, 'title' => 'Iniciar Sesión'], 'auth');
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
