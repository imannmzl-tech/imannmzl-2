<?php
/**
 * 🔐 Hybrid Authentication System
 * PHP Authentication + Firebase Chat Integration
 */

require_once 'database.php';
require_once 'config.php';

class AuthHybrid {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        
        // Start session jika belum
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Register user baru
     */
    public function register($name, $email, $password, $role = 'student') {
        try {
            // Validate input
            if (empty($name) || empty($email) || empty($password)) {
                throw new Exception('All fields are required');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if (!in_array($role, ['teacher', 'student'])) {
                throw new Exception('Invalid role');
            }
            
            // Check if email already exists
            $existingUser = $this->db->fetch(
                "SELECT id FROM users WHERE email = ?", 
                [$email]
            );
            
            if ($existingUser) {
                throw new Exception('Email already exists');
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $this->db->query(
                "INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$name, $email, $hashedPassword, $role]
            );
            
            $userId = $this->db->lastInsertId();
            
            // Auto login after register
            $this->loginById($userId);
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId,
                'user' => $this->getCurrentUser()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required');
            }
            
            // Get user from database
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE email = ?", 
                [$email]
            );
            
            if (!$user) {
                throw new Exception('Invalid email or password');
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                throw new Exception('Invalid email or password');
            }
            
            // Update user online status
            $this->db->query(
                "UPDATE users SET is_online = 1, last_seen = NOW() WHERE id = ?",
                [$user['id']]
            );
            
            // Create session
            $this->createSession($user);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $this->sanitizeUser($user)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Login by user ID (untuk auto login setelah register)
     */
    private function loginById($userId) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?", 
            [$userId]
        );
        
        if ($user) {
            $this->db->query(
                "UPDATE users SET is_online = 1, last_seen = NOW() WHERE id = ?",
                [$userId]
            );
            $this->createSession($user);
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            // Update user offline status
            $this->db->query(
                "UPDATE users SET is_online = 0, last_seen = NOW() WHERE id = ?",
                [$_SESSION['user_id']]
            );
            
            // Delete session from database
            if (isset($_SESSION['session_id'])) {
                $this->db->query(
                    "DELETE FROM user_sessions WHERE id = ?",
                    [$_SESSION['session_id']]
                );
            }
        }
        
        // Clear session
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?", 
            [$_SESSION['user_id']]
        );
        
        return $user ? $this->sanitizeUser($user) : null;
    }
    
    /**
     * Get current user for Firebase (dengan format yang sesuai)
     */
    public function getCurrentUserForFirebase() {
        $user = $this->getCurrentUser();
        if (!$user) {
            return null;
        }
        
        return [
            'uid' => 'php_user_' . $user['id'], // Prefix untuk membedakan dengan Firebase users
            'displayName' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'id' => $user['id']
        ];
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Require login (redirect if not logged in)
     */
    public function requireLogin($redirectTo = '/workspace/login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirectTo");
            exit;
        }
    }
    
    /**
     * Require role (redirect if wrong role)
     */
    public function requireRole($role, $redirectTo = '/workspace/login.php') {
        $this->requireLogin();
        
        if (!$this->hasRole($role)) {
            header("Location: $redirectTo");
            exit;
        }
    }
    
    /**
     * Create user session
     */
    private function createSession($user) {
        $sessionId = bin2hex(random_bytes(32));
        
        // Store in database
        $this->db->query(
            "INSERT INTO user_sessions (id, user_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())",
            [
                $sessionId,
                $user['id'],
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
        
        // Store in PHP session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['session_id'] = $sessionId;
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Sanitize user data (remove password, etc.)
     */
    private function sanitizeUser($user) {
        unset($user['password']);
        return $user;
    }
    
    /**
     * Update user last activity
     */
    public function updateActivity() {
        if ($this->isLoggedIn()) {
            $this->db->query(
                "UPDATE users SET last_seen = NOW() WHERE id = ?",
                [$_SESSION['user_id']]
            );
            
            if (isset($_SESSION['session_id'])) {
                $this->db->query(
                    "UPDATE user_sessions SET last_activity = NOW() WHERE id = ?",
                    [$_SESSION['session_id']]
                );
            }
        }
    }
    
    /**
     * Get online users
     */
    public function getOnlineUsers() {
        return $this->db->fetchAll(
            "SELECT id, name, email, role, avatar, last_seen 
             FROM users 
             WHERE is_online = 1 
             ORDER BY last_seen DESC"
        );
    }
}

// Global auth instance
$authHybrid = new AuthHybrid();

// Helper functions
function auth() {
    global $authHybrid;
    return $authHybrid;
}

function isLoggedIn() {
    return auth()->isLoggedIn();
}

function getCurrentUser() {
    return auth()->getCurrentUser();
}

function getCurrentUserForFirebase() {
    return auth()->getCurrentUserForFirebase();
}

function requireLogin($redirectTo = '/workspace/login.php') {
    auth()->requireLogin($redirectTo);
}

function requireRole($role, $redirectTo = '/workspace/login.php') {
    auth()->requireRole($role, $redirectTo);
}

function hasRole($role) {
    return auth()->hasRole($role);
}

// Update activity on each request
auth()->updateActivity();
?>