<?php
/**
 * 🏠 Room Management System untuk Chat Room Realtime
 */

require_once 'database.php';
require_once 'auth.php';

class RoomManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create new room
     */
    public function createRoom($name, $description = '', $createdBy = null) {
        try {
            if (empty($name)) {
                throw new Exception('Room name is required');
            }
            
            if (!$createdBy) {
                $user = getCurrentUser();
                if (!$user) {
                    throw new Exception('User not authenticated');
                }
                $createdBy = $user['id'];
            }
            
            // Generate unique room code
            $roomCode = $this->generateRoomCode();
            
            // Create room
            $this->db->query(
                "INSERT INTO rooms (name, description, room_code, created_by, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$name, $description, $roomCode, $createdBy]
            );
            
            $roomId = $this->db->lastInsertId();
            
            // Add creator as member
            $this->joinRoom($roomId, $createdBy);
            
            return [
                'success' => true,
                'message' => 'Room created successfully',
                'room_id' => $roomId,
                'room_code' => $roomCode
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Join room by room code or ID
     */
    public function joinRoom($roomId, $userId = null) {
        try {
            if (!$userId) {
                $user = getCurrentUser();
                if (!$user) {
                    throw new Exception('User not authenticated');
                }
                $userId = $user['id'];
            }
            
            // If roomId is actually a room code
            if (!is_numeric($roomId)) {
                $room = $this->db->fetch(
                    "SELECT id FROM rooms WHERE room_code = ? AND is_active = 1",
                    [$roomId]
                );
                
                if (!$room) {
                    throw new Exception('Room not found');
                }
                
                $roomId = $room['id'];
            }
            
            // Check if room exists
            $room = $this->db->fetch(
                "SELECT * FROM rooms WHERE id = ? AND is_active = 1",
                [$roomId]
            );
            
            if (!$room) {
                throw new Exception('Room not found or inactive');
            }
            
            // Check if already member
            $existing = $this->db->fetch(
                "SELECT id FROM room_members WHERE room_id = ? AND user_id = ?",
                [$roomId, $userId]
            );
            
            if ($existing) {
                return [
                    'success' => true,
                    'message' => 'Already a member of this room'
                ];
            }
            
            // Check member limit
            $memberCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM room_members WHERE room_id = ?",
                [$roomId]
            )['count'];
            
            if ($memberCount >= $room['max_members']) {
                throw new Exception('Room is full');
            }
            
            // Join room
            $this->db->query(
                "INSERT INTO room_members (room_id, user_id, joined_at) VALUES (?, ?, NOW())",
                [$roomId, $userId]
            );
            
            return [
                'success' => true,
                'message' => 'Successfully joined room',
                'room_id' => $roomId
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Leave room
     */
    public function leaveRoom($roomId, $userId = null) {
        try {
            if (!$userId) {
                $user = getCurrentUser();
                if (!$user) {
                    throw new Exception('User not authenticated');
                }
                $userId = $user['id'];
            }
            
            $this->db->query(
                "DELETE FROM room_members WHERE room_id = ? AND user_id = ?",
                [$roomId, $userId]
            );
            
            return [
                'success' => true,
                'message' => 'Successfully left room'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user's rooms
     */
    public function getUserRooms($userId = null) {
        if (!$userId) {
            $user = getCurrentUser();
            if (!$user) {
                return [];
            }
            $userId = $user['id'];
        }
        
        return $this->db->fetchAll(
            "SELECT r.*, rm.joined_at, u.name as creator_name,
                    (SELECT COUNT(*) FROM room_members rm2 WHERE rm2.room_id = r.id) as member_count,
                    (SELECT COUNT(*) FROM messages m WHERE m.room_id = r.id AND m.is_deleted = 0) as message_count
             FROM rooms r 
             JOIN room_members rm ON r.id = rm.room_id 
             JOIN users u ON r.created_by = u.id
             WHERE rm.user_id = ? AND r.is_active = 1
             ORDER BY rm.joined_at DESC",
            [$userId]
        );
    }
    
    /**
     * Get room by ID
     */
    public function getRoom($roomId) {
        return $this->db->fetch(
            "SELECT r.*, u.name as creator_name, u.email as creator_email,
                    (SELECT COUNT(*) FROM room_members rm WHERE rm.room_id = r.id) as member_count
             FROM rooms r 
             JOIN users u ON r.created_by = u.id
             WHERE r.id = ? AND r.is_active = 1",
            [$roomId]
        );
    }
    
    /**
     * Get room members
     */
    public function getRoomMembers($roomId) {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.role, u.avatar, u.is_online, u.last_seen, rm.joined_at
             FROM users u 
             JOIN room_members rm ON u.id = rm.user_id 
             WHERE rm.room_id = ?
             ORDER BY rm.joined_at ASC",
            [$roomId]
        );
    }
    
    /**
     * Check if user is room member
     */
    public function isRoomMember($roomId, $userId = null) {
        if (!$userId) {
            $user = getCurrentUser();
            if (!$user) {
                return false;
            }
            $userId = $user['id'];
        }
        
        $result = $this->db->fetch(
            "SELECT id FROM room_members WHERE room_id = ? AND user_id = ?",
            [$roomId, $userId]
        );
        
        return !empty($result);
    }
    
    /**
     * Delete room (only by creator)
     */
    public function deleteRoom($roomId, $userId = null) {
        try {
            if (!$userId) {
                $user = getCurrentUser();
                if (!$user) {
                    throw new Exception('User not authenticated');
                }
                $userId = $user['id'];
            }
            
            // Check if user is creator
            $room = $this->db->fetch(
                "SELECT created_by FROM rooms WHERE id = ?",
                [$roomId]
            );
            
            if (!$room || $room['created_by'] != $userId) {
                throw new Exception('Only room creator can delete room');
            }
            
            // Soft delete
            $this->db->query(
                "UPDATE rooms SET is_active = 0 WHERE id = ?",
                [$roomId]
            );
            
            return [
                'success' => true,
                'message' => 'Room deleted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all active rooms (for students to browse)
     */
    public function getAllRooms($limit = 50) {
        return $this->db->fetchAll(
            "SELECT r.*, u.name as creator_name,
                    (SELECT COUNT(*) FROM room_members rm WHERE rm.room_id = r.id) as member_count
             FROM rooms r 
             JOIN users u ON r.created_by = u.id
             WHERE r.is_active = 1
             ORDER BY r.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Search rooms
     */
    public function searchRooms($query, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT r.*, u.name as creator_name,
                    (SELECT COUNT(*) FROM room_members rm WHERE rm.room_id = r.id) as member_count
             FROM rooms r 
             JOIN users u ON r.created_by = u.id
             WHERE r.is_active = 1 
             AND (r.name LIKE ? OR r.description LIKE ? OR r.room_code LIKE ?)
             ORDER BY r.created_at DESC
             LIMIT ?",
            ["%$query%", "%$query%", "%$query%", $limit]
        );
    }
    
    /**
     * Generate unique room code
     */
    private function generateRoomCode() {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
            $existing = $this->db->fetch(
                "SELECT id FROM rooms WHERE room_code = ?",
                [$code]
            );
        } while ($existing);
        
        return $code;
    }
}

// Global room manager instance
$roomManager = new RoomManager();

// Helper functions
function rooms() {
    global $roomManager;
    return $roomManager;
}
?>