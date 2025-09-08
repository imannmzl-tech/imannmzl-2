<?php
/**
 * 🔥 Chat Room Realtime - Configuration
 */

// Application Configuration
define('APP_NAME', 'Chat Room Realtime');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://multinteraktif.online/chat-room-realtime');

// Upload Configuration (PHP Upload - No Firebase Storage needed)
define('UPLOAD_PATH', 'uploads/chat-images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Create upload directory if not exists
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Session Configuration
ini_set('session.cookie_lifetime', 86400); // 24 hours
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.cookie_httponly', 1); // HTTP only
ini_set('session.use_strict_mode', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS Headers (untuk Firebase)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function untuk generate random strings
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Helper function untuk sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Helper function untuk validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function untuk check file extension
function isAllowedFileType($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_EXTENSIONS);
}

// Helper function untuk format timestamp
function formatTimestamp($timestamp) {
    return date('d/m/Y H:i:s', $timestamp);
}

// Helper function untuk time ago
function timeAgo($timestamp) {
    $time = time() - $timestamp;
    
    if ($time < 60) return 'baru saja';
    if ($time < 3600) return floor($time/60) . ' menit yang lalu';
    if ($time < 86400) return floor($time/3600) . ' jam yang lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari yang lalu';
    
    return date('d/m/Y', $timestamp);
}
?>
