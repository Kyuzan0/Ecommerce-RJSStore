<?php

class ChatConversation extends BaseModel
{
    protected string $table = 'chat_conversations';

    public function getOrCreate(int $customerId, ?string $subject = null): array
    {
        // Find existing open conversation
        $conv = $this->db->fetchOne(
            "SELECT * FROM chat_conversations WHERE customer_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1",
            [$customerId]
        );
        if ($conv) return $conv;

        // Create new
        $id = $this->create([
            'customer_id' => $customerId,
            'subject'     => $subject ?? 'Chat Support',
            'status'      => 'open',
        ]);
        return $this->find($id);
    }

    public function getAllAdmin(?string $status = null, int $limit = 20, int $offset = 0): array
    {
        $where = '1=1';
        $params = [];
        if ($status) {
            $where .= " AND c.status = ?";
            $params[] = $status;
        }
        return $this->db->fetchAll(
            "SELECT c.*, u.name AS customer_name, u.email,
                    (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id AND is_read = 0 AND sender_role = 'customer') AS unread_count
             FROM chat_conversations c
             JOIN users u ON c.customer_id = u.id
             WHERE {$where}
             ORDER BY c.last_message_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function countUnreadAdmin(): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(DISTINCT conversation_id) AS total FROM chat_messages WHERE sender_role = 'customer' AND is_read = 0"
        );
        return $row ? (int) $row['total'] : 0;
    }
}
