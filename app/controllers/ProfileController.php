<?php
class ProfileController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $user      = $userModel->findById($_SESSION['user_id'] ?? 0);

        if (!$user) {
            $this->setFlash('error', 'Usuario no encontrado.');
            $this->redirect('dashboard');
        }

        $this->render('profile/index', [
            'title' => 'Mi Perfil',
            'flash' => $this->getFlash(),
            'user'  => $user,
            'csrf'  => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('perfil');
        }

        $id     = (int) ($_SESSION['user_id'] ?? 0);
        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email  = htmlspecialchars(trim($_POST['email']  ?? ''), ENT_QUOTES, 'UTF-8');

        if (empty($nombre)) {
            $this->setFlash('error', 'El nombre es obligatorio.');
            $this->redirect('perfil');
        }

        $data = ['nombre' => $nombre, 'email' => $email ?: null];

        $userModel = new User();
        $userModel->update($id, $data);

        // Update session name
        $_SESSION['user_name'] = $nombre;

        $this->setFlash('success', 'Perfil actualizado correctamente.');
        $this->redirect('perfil');
    }

    public function changePassword(): void
    {
        $this->requireAuth();

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('perfil');
        }

        $id          = (int) ($_SESSION['user_id'] ?? 0);
        $current     = $_POST['password_actual']   ?? '';
        $newPass     = $_POST['password_nuevo']     ?? '';
        $confirmPass = $_POST['password_confirmar'] ?? '';

        if (empty($current) || empty($newPass) || empty($confirmPass)) {
            $this->setFlash('error', 'Todos los campos de contraseña son obligatorios.');
            $this->redirect('perfil');
        }

        if ($newPass !== $confirmPass) {
            $this->setFlash('error', 'La nueva contraseña y su confirmación no coinciden.');
            $this->redirect('perfil');
        }

        if (strlen($newPass) < 6) {
            $this->setFlash('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
            $this->redirect('perfil');
        }

        $userModel = new User();
        $user      = $userModel->findById($id);

        if (!$user || !password_verify($current, $user['password'])) {
            $this->setFlash('error', 'La contraseña actual es incorrecta.');
            $this->redirect('perfil');
        }

        $userModel->updatePassword($id, $newPass);
        $this->setFlash('success', 'Contraseña actualizada correctamente.');
        $this->redirect('perfil');
    }
}
