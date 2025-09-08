<?php
/**
 * 🏠 Room Management API Endpoints
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/rooms.php';

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
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Only POST method allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? '';
            $description = $input['description'] ?? '';
            
            $result = rooms()->createRoom($name, $description);
            echo json_encode($result);
            break;
            
        case 'join':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Only POST method allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $roomCode = $input['room_code'] ?? '';
            
            $result = rooms()->joinRoom($roomCode);
            echo json_encode($result);
            break;
            
        case 'leave':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Only POST method allowed');
            }
            
            $roomId = $_GET['room_id'] ?? '';
            if (empty($roomId)) {
                throw new Exception('Room ID required');
            }
            
            $result = rooms()->leaveRoom($roomId);
            echo json_encode($result);
            break;
            
        case 'my-rooms':
            $rooms = rooms()->getUserRooms();
            echo json_encode([
                'success' => true,
                'rooms' => $rooms
            ]);
            break;
            
        case 'all-rooms':
            $allRooms = rooms()->getAllRooms();
            echo json_encode([
                'success' => true,
                'rooms' => $allRooms
            ]);
            break;
            
        case 'room-info':
            $roomId = $_GET['room_id'] ?? '';
            if (empty($roomId)) {
                throw new Exception('Room ID required');
            }
            
            $room = rooms()->getRoom($roomId);
            $members = rooms()->getRoomMembers($roomId);
            
            if (!$room) {
                throw new Exception('Room not found');
            }
            
            echo json_encode([
                'success' => true,
                'room' => $room,
                'members' => $members
            ]);
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                throw new Exception('Only DELETE method allowed');
            }
            
            $roomId = $_GET['room_id'] ?? '';
            if (empty($roomId)) {
                throw new Exception('Room ID required');
            }
            
            $result = rooms()->deleteRoom($roomId);
            echo json_encode($result);
            break;
            
        case 'search':
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                throw new Exception('Search query required');
            }
            
            $searchResults = rooms()->searchRooms($query);
            echo json_encode([
                'success' => true,
                'rooms' => $searchResults
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