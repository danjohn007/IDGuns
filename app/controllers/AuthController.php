<?php
class AuthController extends BaseController
{
    public function login(): void
    {
        // Already authenticated
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }

        // Generate math captcha if not set
        if (empty($_SESSION['captcha_a']) || empty($_SESSION['captcha_b'])) {
            $_SESSION['captcha_a'] = random_int(1, 9);
            $_SESSION['captcha_b'] = random_int(1, 9);
        }

        $error = null;

        if ($this->isPost()) {
            $username       = trim($_POST['username'] ?? '');
            $password       = trim($_POST['password'] ?? '');
            $captchaAnswer  = (int) ($_POST['captcha_answer'] ?? -1);
            $expectedAnswer = (int) (($_SESSION['captcha_a'] ?? 0) + ($_SESSION['captcha_b'] ?? 0));

            // Regenerate captcha after every attempt
            $_SESSION['captcha_a'] = random_int(1, 9);
            $_SESSION['captcha_b'] = random_int(1, 9);

            if (empty($username) || empty($password)) {
                $error = 'Por favor ingrese usuario y contraseña.';
            } elseif ($captchaAnswer !== $expectedAnswer) {
                $error = 'Verificación humana incorrecta. Inténtelo de nuevo.';
            } else {
                $userModel = new User();
                $user      = $userModel->authenticate($username, $password);

                if ($user) {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_role'] = $user['rol'];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    unset($_SESSION['captcha_a'], $_SESSION['captcha_b']);
                    $this->redirect('dashboard');
                } else {
                    $error = 'Usuario o contraseña incorrectos.';
                }
            }
        }

        $this->render('auth/login', [
            'error'    => $error,
            'title'    => 'Iniciar Sesión',
            'captchaA' => $_SESSION['captcha_a'],
            'captchaB' => $_SESSION['captcha_b'],
        ], 'auth');
    }

    public function forgotPassword(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }

        $info  = null;
        $error = null;

        if ($this->isPost()) {
            $email = trim($_POST['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingrese un correo electrónico válido.';
            } else {
                // Always show success to avoid user enumeration
                $info = 'Si existe una cuenta con ese correo, recibirá instrucciones para restablecer su contraseña. Contacte al administrador del sistema.';
            }
        }

        $this->render('auth/forgot-password', [
            'title' => 'Recuperar Contraseña',
            'info'  => $info,
            'error' => $error,
        ], 'auth');
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
