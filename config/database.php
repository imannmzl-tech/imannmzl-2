<?php
/**
 * 🗄️ Database Configuration untuk Chat Room Realtime
 * 
 * INSTRUKSI SETUP:
 * 1. Buat database baru di phpMyAdmin (contoh: chat_room_db)
 * 2. Update konfigurasi di bawah ini sesuai dengan hosting Anda
 * 3. Import file database.sql yang akan saya buat
 */

// Database Configuration - Sesuaikan dengan hosting Anda
define('DB_HOST', 'localhost');        // Host database (biasanya localhost)
define('DB_NAME', 'n1567943_chat-room-realtime_db');     // Nama database Anda
define('DB_USER', 'n1567943_chat-room-realtime_user');   // Username database
define('DB_PASS', 'Gitar222@@@');      // Password database
define('DB_CHARSET', 'utf8mb4');

// Error reporting untuk development (disable di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            die("❌ Database Connection Error: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
            throw new Exception("Database error occurred");
        }
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
}

// Helper function untuk mendapatkan database instance
function getDB() {
    return Database::getInstance();
}

// Test database connection
try {
    $db = getDB();
    // echo "✅ Database connected successfully!";
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
?>