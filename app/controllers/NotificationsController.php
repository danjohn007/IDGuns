<?php
class NotificationsController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $notificaciones = [];
        try {
            $db = Database::getInstance();
            $notificaciones = $db->prepare(
                "SELECT * FROM notificaciones WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50"
            );
            $notificaciones->execute([':uid' => $_SESSION['user_id'] ?? 0]);
            $notificaciones = $notificaciones->fetchAll();

            // Mark all as read
            $db->prepare("UPDATE notificaciones SET leido = 1, leido_at = :now WHERE user_id = :uid AND leido = 0")
               ->execute([':now' => date('Y-m-d H:i:s'), ':uid' => $_SESSION['user_id'] ?? 0]);
        } catch (\Throwable $e) {
            // table may not exist
        }

        $this->render('notifications/index', [
            'title'          => 'Notificaciones',
            'flash'          => $this->getFlash(),
            'notificaciones' => $notificaciones,
        ]);
    }
}
