<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/ChatConversation.php';
require_once __DIR__ . '/../models/ChatMessage.php';

class AdminChatController extends BaseController
{
    private ChatConversation $convModel;
    private ChatMessage $msgModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->convModel = new ChatConversation();
        $this->msgModel = new ChatMessage();
    }

    /**
     * Admin chat list — all conversations.
     */
    public function index()
    {
        $conversations = $this->convModel->getAllAdmin(null, 50, 0);

        $this->view('admin/chat/index', [
            'conversations' => $conversations,
            'active_page'   => 'chat',
            'page_title'    => 'Live Chat',
        ], 'admin');
    }

    /**
     * Admin view a specific conversation.
     */
    public function view_conv($id)
    {
        $convId = (int) $id;
        $conv = $this->convModel->find($convId);
        if (!$conv) {
            flash('error', 'Conversation tidak ditemukan.');
            $this->redirect('/admin-chat');
            return;
        }

        $messages = $this->msgModel->getByConversation($convId);
        $this->msgModel->markReadByConversation($convId, 'admin');

        // Get customer info
        $customer = $this->db->fetchOne("SELECT name, email FROM users WHERE id = ?", [(int) $conv['customer_id']]);

        $this->view('admin/chat/conversation', [
            'conversation' => $conv,
            'messages'     => $messages,
            'customer'     => $customer,
            'active_page'  => 'chat',
            'page_title'   => 'Chat - ' . ($customer['name'] ?? 'Customer'),
        ], 'admin');
    }
}
