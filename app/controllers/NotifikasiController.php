<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Notifikasi.php';

class NotifikasiController extends BaseController
{
    private Notifikasi $notifModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->notifModel = new Notifikasi();
    }

    /**
     * API: Get notifications for current user (JSON).
     */
    public function apiGet(): void
    {
        $userId = $this->auth->id();
        $notifs = $this->notifModel->getByUser($userId, 15);
        $unread = $this->notifModel->countUnread($userId);

        $this->json([
            'success' => true,
            'unread'  => $unread,
            'items'   => $notifs,
        ]);
    }

    /**
     * API: Mark one notification as read.
     */
    public function apiRead(): void
    {
        $this->requirePost();
        $userId = $this->auth->id();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->notifModel->markAsRead($id, $userId);
        }
        $this->json(['success' => true]);
    }

    /**
     * API: Mark all as read.
     */
    public function apiReadAll(): void
    {
        $this->requirePost();
        $userId = $this->auth->id();
        $this->notifModel->markAllRead($userId);
        $this->json(['success' => true]);
    }
}
