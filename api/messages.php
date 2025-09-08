<?php
/**
 * 💬 Messages API Endpoints
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/messages.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'send':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Only POST method allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $roomId = $input['room_id'] ?? '';
            $message = $input['message'] ?? '';
            $messageType = $input['message_type'] ?? 'text';
            $fileUrl = $input['file_url'] ?? null;
            $fileName = $input['file_name'] ?? null;
            $fileSize = $input['file_size'] ?? null;
            
            if (empty($roomId)) {
                throw new Exception('Room ID required');
            }
            
            $result = messages()->sendMessage($roomId, $message, $messageType, $fileUrl, $fileName, $fileSize);
            echo json_encode($result);
            break;
            
        case 'get':
            $roomId = $_GET['room_id'] ?? '';
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            
            if (empty($roomId)) {
                throw new Exception('Room ID required');
            }
            
            $roomMessages = messages()->getRoomMessages($roomId, $limit, $offset);
            echo json_encode([
                'success' => true,
                'messages' => $roomMessages
            ]);
            break;
            
        case 'recent':
            $roomId = $_GET['room_id'] ?? '';
            $lastMessageId = (int)($_GET['last_message_id'] ?? 0);
            
            if (empty($roomId)) {
                throw new Exception('Room ID required');
            }
            
            $recentMessages = messages()->getRecentMessages($roomId, $lastMessageId);
            echo json_encode([
                'success' => true,
                'messages' => $recentMessages
            ]);
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                throw new Exception('Only DELETE method allowed');
            }
            
            $messageId = $_GET['message_id'] ?? '';
            if (empty($messageId)) {
                throw new Exception('Message ID required');
            }
            
            $result = messages()->deleteMessage($messageId);
            echo json_encode($result);
            break;
            
        case 'search':
            $roomId = $_GET['room_id'] ?? '';
            $query = $_GET['q'] ?? '';
            $limit = (int)($_GET['limit'] ?? 20);
            
            if (empty($roomId)) {
                throw new Exception('Room ID required');
            }
            
            if (empty($query)) {
                throw new Exception('Search query required');
            }
            
            $searchResults = messages()->searchMessages($roomId, $query, $limit);
            echo json_encode([
                'success' => true,
                'messages' => $searchResults
            ]);
            break;
            
        case 'stats':
            $roomId = $_GET['room_id'] ?? null;
            $userId = $_GET['user_id'] ?? null;
            
            $stats = messages()->getMessageStats($roomId, $userId);
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>