<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/ChatConversation.php';
require_once __DIR__ . '/../models/ChatMessage.php';

class ChatController extends BaseController
{
    private ChatConversation $convModel;
    private ChatMessage $msgModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->convModel = new ChatConversation();
        $this->msgModel = new ChatMessage();
    }

    /**
     * Customer chat page.
     */
    public function index()
    {
        if (!$this->auth->isCustomer()) {
            $this->redirect('/admin-chat');
            return;
        }

        $userId = $this->auth->id();
        $conv = $this->convModel->getOrCreate($userId);
        $messages = $this->msgModel->getByConversation((int) $conv['id']);
        $this->msgModel->markReadByConversation((int) $conv['id'], 'customer');

        $this->view('customer/chat', [
            'conversation' => $conv,
            'messages'     => $messages,
            'active_page'  => 'chat',
            'page_title'   => 'Live Chat',
        ], 'customer');
    }

    /**
     * API: Send message (POST).
     */
    public function apiSend(): void
    {
        $this->requirePost();
        $userId = $this->auth->id();
        $role = $this->auth->user()['role'];
        $convId = (int) ($_POST['conversation_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if ($convId <= 0 || $message === '') {
            $this->json(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
            return;
        }

        // Verify access
        $conv = $this->convModel->find($convId);
        if (!$conv) {
            $this->json(['success' => false, 'message' => 'Conversation not found.']);
            return;
        }
        if ($role === 'customer' && (int) $conv['customer_id'] !== $userId) {
            $this->json(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        $msgId = $this->msgModel->send($convId, $userId, $role, $message);

        $this->json([
            'success' => true,
            'message_id' => $msgId,
        ]);
    }

    /**
     * API: Poll for new messages (GET).
     */
    public function apiPoll(): void
    {
        $convId = (int) ($_GET['conversation_id'] ?? 0);
        $afterId = (int) ($_GET['after_id'] ?? 0);
        $userId = $this->auth->id();
        $role = $this->auth->user()['role'];

        if ($convId <= 0) {
            $this->json(['success' => false]);
            return;
        }

        // Verify access
        $conv = $this->convModel->find($convId);
        if (!$conv || ($role === 'customer' && (int) $conv['customer_id'] !== $userId)) {
            $this->json(['success' => false]);
            return;
        }

        // Mark messages as read
        $this->msgModel->markReadByConversation($convId, $role);

        $newMessages = $this->msgModel->getNewMessages($convId, $afterId);

        $this->json([
            'success'  => true,
            'messages' => $newMessages,
        ]);
    }
}
