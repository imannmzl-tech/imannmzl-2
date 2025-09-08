<?php
/**
 * 💬 Message System untuk Chat Room Realtime
 */

require_once 'database.php';
require_once 'auth.php';
require_once 'rooms.php';

class MessageManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Send message
     */
    public function sendMessage($roomId, $message, $messageType = 'text', $fileUrl = null, $fileName = null, $fileSize = null) {
        try {
            $user = getCurrentUser();
            if (!$user) {
                throw new Exception('User not authenticated');
            }
            
            // Check if user is room member
            if (!rooms()->isRoomMember($roomId, $user['id'])) {
                throw new Exception('You are not a member of this room');
            }
            
            // Validate message
            if (empty($message) && $messageType === 'text') {
                throw new Exception('Message cannot be empty');
            }
            
            if ($messageType !== 'text' && empty($fileUrl)) {
                throw new Exception('File URL is required for non-text messages');
            }
            
            // Insert message
            $this->db->query(
                "INSERT INTO messages (room_id, user_id, message, message_type, file_url, file_name, file_size, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [$roomId, $user['id'], $message, $messageType, $fileUrl, $fileName, $fileSize]
            );
            
            $messageId = $this->db->lastInsertId();
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'message' => 'Message sent successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get room messages
     */
    public function getRoomMessages($roomId, $limit = 50, $offset = 0) {
        try {
            $user = getCurrentUser();
            if (!$user) {
                throw new Exception('User not authenticated');
            }
            
            // Check if user is room member
            if (!rooms()->isRoomMember($roomId, $user['id'])) {
                throw new Exception('You are not a member of this room');
            }
            
            return $this->db->fetchAll(
                "SELECT m.*, u.name as user_name, u.email as user_email, u.avatar as user_avatar
                 FROM messages m 
                 JOIN users u ON m.user_id = u.id
                 WHERE m.room_id = ? AND m.is_deleted = 0
                 ORDER BY m.created_at ASC
                 LIMIT ? OFFSET ?",
                [$roomId, $limit, $offset]
            );
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get recent messages (for real-time updates)
     */
    public function getRecentMessages($roomId, $lastMessageId = 0) {
        try {
            $user = getCurrentUser();
            if (!$user) {
                return [];
            }
            
            // Check if user is room member
            if (!rooms()->isRoomMember($roomId, $user['id'])) {
                return [];
            }
            
            return $this->db->fetchAll(
                "SELECT m.*, u.name as user_name, u.email as user_email, u.avatar as user_avatar
                 FROM messages m 
                 JOIN users u ON m.user_id = u.id
                 WHERE m.room_id = ? AND m.id > ? AND m.is_deleted = 0
                 ORDER BY m.created_at ASC",
                [$roomId, $lastMessageId]
            );
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Delete message
     */
    public function deleteMessage($messageId) {
        try {
            $user = getCurrentUser();
            if (!$user) {
                throw new Exception('User not authenticated');
            }
            
            // Get message
            $message = $this->db->fetch(
                "SELECT * FROM messages WHERE id = ?",
                [$messageId]
            );
            
            if (!$message) {
                throw new Exception('Message not found');
            }
            
            // Check if user owns the message or is room creator
            $room = rooms()->getRoom($message['room_id']);
            if ($message['user_id'] != $user['id'] && $room['created_by'] != $user['id']) {
                throw new Exception('You can only delete your own messages');
            }
            
            // Soft delete
            $this->db->query(
                "UPDATE messages SET is_deleted = 1 WHERE id = ?",
                [$messageId]
            );
            
            return [
                'success' => true,
                'message' => 'Message deleted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get message statistics
     */
    public function getMessageStats($roomId = null, $userId = null) {
        $conditions = [];
        $params = [];
        
        if ($roomId) {
            $conditions[] = "room_id = ?";
            $params[] = $roomId;
        }
        
        if ($userId) {
            $conditions[] = "user_id = ?";
            $params[] = $userId;
        }
        
        $conditions[] = "is_deleted = 0";
        
        $whereClause = "WHERE " . implode(" AND ", $conditions);
        
        return $this->db->fetch(
            "SELECT 
                COUNT(*) as total_messages,
                COUNT(CASE WHEN message_type = 'text' THEN 1 END) as text_messages,
                COUNT(CASE WHEN message_type = 'image' THEN 1 END) as image_messages,
                COUNT(CASE WHEN message_type = 'file' THEN 1 END) as file_messages,
                MIN(created_at) as first_message,
                MAX(created_at) as last_message
             FROM messages $whereClause",
            $params
        );
    }
    
    /**
     * Search messages
     */
    public function searchMessages($roomId, $query, $limit = 20) {
        try {
            $user = getCurrentUser();
            if (!$user) {
                return [];
            }
            
            // Check if user is room member
            if (!rooms()->isRoomMember($roomId, $user['id'])) {
                return [];
            }
            
            return $this->db->fetchAll(
                "SELECT m.*, u.name as user_name, u.email as user_email, u.avatar as user_avatar
                 FROM messages m 
                 JOIN users u ON m.user_id = u.id
                 WHERE m.room_id = ? AND m.message LIKE ? AND m.is_deleted = 0
                 ORDER BY m.created_at DESC
                 LIMIT ?",
                [$roomId, "%$query%", $limit]
            );
            
        } catch (Exception $e) {
            return [];
        }
    }
}

// Global message manager instance
$messageManager = new MessageManager();

// Helper functions
function messages() {
    global $messageManager;
    return $messageManager;
}
?>