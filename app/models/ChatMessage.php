<?php

class ChatMessage extends BaseModel
{
    protected string $table = 'chat_messages';

    public function getByConversation(int $conversationId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name
             FROM chat_messages m
             JOIN users u ON m.sender_id = u.id
             WHERE m.conversation_id = ?
             ORDER BY m.created_at ASC
             LIMIT {$limit}",
            [$conversationId]
        );
    }

    public function send(int $conversationId, int $senderId, string $senderRole, string $message): int
    {
        $id = $this->create([
            'conversation_id' => $conversationId,
            'sender_id'       => $senderId,
            'sender_role'     => $senderRole,
            'message'         => $message,
        ]);

        // Update conversation last_message_at
        $db = Database::getInstance();
        $db->execute(
            "UPDATE chat_conversations SET last_message_at = NOW() WHERE id = ?",
            [$conversationId]
        );

        return $id;
    }

    public function markReadByConversation(int $conversationId, string $readerRole): bool
    {
        // Mark messages from the OTHER role as read
        $otherRole = ($readerRole === 'admin') ? 'customer' : 'admin';
        return $this->db->execute(
            "UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender_role = ? AND is_read = 0",
            [$conversationId, $otherRole]
        );
    }

    public function getNewMessages(int $conversationId, int $afterId): array
    {
        return $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name
             FROM chat_messages m
             JOIN users u ON m.sender_id = u.id
             WHERE m.conversation_id = ? AND m.id > ?
             ORDER BY m.created_at ASC",
            [$conversationId, $afterId]
        );
    }

    public function countUnreadForUser(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM chat_messages m
             JOIN chat_conversations c ON m.conversation_id = c.id
             WHERE c.customer_id = ? AND m.sender_role = 'admin' AND m.is_read = 0",
            [$userId]
        );
        return $row ? (int) $row['total'] : 0;
    }
}
