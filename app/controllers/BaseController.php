<?php
class BaseController
{
    /**
     * Render a view inside a layout.
     * The view file sets $content via output buffering, then the layout echoes it.
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        ob_start();
        $viewFile = ROOT_PATH . '/app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die("Vista no encontrada: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = ROOT_PATH . '/app/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            die("Layout no encontrado: {$layout}");
        }
        require $layoutFile;
    }

    /** HTTP redirect */
    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    /** Store flash message in session */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Retrieve and clear flash message */
    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /** Require authenticated session */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }
    }

    /** Require one of the given roles */
    protected function requireRole(array $roles): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user_role'] ?? '', $roles)) {
            $this->setFlash('error', 'No tiene permisos para acceder a esta sección.');
            $this->redirect('dashboard');
        }
    }

    /** Generate or return CSRF token */
    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Validate submitted CSRF token */
    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    protected function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
    protected function isGet(): bool  { return $_SERVER['REQUEST_METHOD'] === 'GET'; }

    /** Return a JSON response and exit */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /** Sanitize a string for HTML output */
    protected function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /** Current authenticated user array */
    protected function currentUser(): array
    {
        return [
            'id'     => $_SESSION['user_id']   ?? 0,
            'nombre' => $_SESSION['user_name']  ?? '',
            'rol'    => $_SESSION['user_role']  ?? '',
        ];
    }

    /** Paginate helper: returns current page from GET */
    protected function currentPage(): int
    {
        return max(1, (int) ($_GET['pagina'] ?? 1));
    }
}
