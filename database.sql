-- ============================================
-- 🗄️ Chat Room Realtime - Database Schema
-- ============================================
-- 
-- INSTRUKSI:
-- 1. Buat database baru di phpMyAdmin: CREATE DATABASE chat_room_db;
-- 2. Pilih database tersebut
-- 3. Import file ini atau copy-paste SQL di bawah

-- Set charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- Table: users
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('teacher','student') NOT NULL DEFAULT 'student',
  `avatar` varchar(255) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_online` (`is_online`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: rooms
-- ============================================
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `room_code` varchar(20) NOT NULL UNIQUE,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `max_members` int(11) DEFAULT 50,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_code` (`room_code`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_is_active` (`is_active`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: room_members
-- ============================================
DROP TABLE IF EXISTS `room_members`;
CREATE TABLE `room_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_room_member` (`room_id`, `user_id`),
  KEY `idx_room_id` (`room_id`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: messages
-- ============================================
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text,
  `message_type` enum('text','image','file') DEFAULT 'text',
  `file_url` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_room_id` (`room_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_deleted` (`is_deleted`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: user_sessions
-- ============================================
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) NOT NULL,
  `last_activity` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample Data untuk Testing
-- ============================================

-- Sample Teacher Account
-- Email: teacher@test.com
-- Password: password123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Teacher Demo', 'teacher@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher');

-- Sample Student Account  
-- Email: student@test.com
-- Password: password123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Student Demo', 'student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Sample Room
INSERT INTO `rooms` (`name`, `description`, `room_code`, `created_by`) VALUES
('General Discussion', 'Room untuk diskusi umum', 'DEMO123', 1);

-- Add teacher to room
INSERT INTO `room_members` (`room_id`, `user_id`) VALUES (1, 1);

-- Sample Message
INSERT INTO `messages` (`room_id`, `user_id`, `message`) VALUES
(1, 1, 'Selamat datang di Chat Room Realtime! 🎉');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Indexes untuk Performance
-- ============================================
ALTER TABLE `messages` ADD INDEX `idx_room_created` (`room_id`, `created_at`);
ALTER TABLE `room_members` ADD INDEX `idx_user_rooms` (`user_id`, `room_id`);

-- ============================================
-- Views untuk Query yang Sering Digunakan
-- ============================================

-- View untuk room dengan member count
CREATE OR REPLACE VIEW `rooms_with_stats` AS
SELECT 
    r.*,
    u.name as creator_name,
    u.email as creator_email,
    COUNT(rm.user_id) as member_count,
    (SELECT COUNT(*) FROM messages m WHERE m.room_id = r.id AND m.is_deleted = 0) as message_count
FROM rooms r
LEFT JOIN users u ON r.created_by = u.id
LEFT JOIN room_members rm ON r.id = rm.room_id
WHERE r.is_active = 1
GROUP BY r.id;

-- View untuk messages dengan user info
CREATE OR REPLACE VIEW `messages_with_user` AS
SELECT 
    m.*,
    u.name as user_name,
    u.email as user_email,
    u.avatar as user_avatar,
    r.name as room_name
FROM messages m
LEFT JOIN users u ON m.user_id = u.id
LEFT JOIN rooms r ON m.room_id = r.id
WHERE m.is_deleted = 0
ORDER BY m.created_at ASC;

-- ============================================
-- Stored Procedures (Optional)
-- ============================================

DELIMITER //

-- Procedure untuk join room
CREATE PROCEDURE JoinRoom(IN p_room_id INT, IN p_user_id INT)
BEGIN
    DECLARE room_exists INT DEFAULT 0;
    DECLARE already_member INT DEFAULT 0;
    
    -- Check if room exists and is active
    SELECT COUNT(*) INTO room_exists FROM rooms WHERE id = p_room_id AND is_active = 1;
    
    IF room_exists > 0 THEN
        -- Check if user is already a member
        SELECT COUNT(*) INTO already_member FROM room_members WHERE room_id = p_room_id AND user_id = p_user_id;
        
        IF already_member = 0 THEN
            INSERT INTO room_members (room_id, user_id) VALUES (p_room_id, p_user_id);
            SELECT 'SUCCESS' as status, 'Successfully joined room' as message;
        ELSE
            SELECT 'ERROR' as status, 'Already a member of this room' as message;
        END IF;
    ELSE
        SELECT 'ERROR' as status, 'Room not found or inactive' as message;
    END IF;
END //

DELIMITER ;

-- ============================================
-- Triggers untuk Auto Updates
-- ============================================

-- Trigger untuk update user last_seen saat login
DELIMITER //
CREATE TRIGGER update_user_last_seen 
    BEFORE UPDATE ON users 
    FOR EACH ROW 
BEGIN
    IF NEW.is_online = 1 AND OLD.is_online = 0 THEN
        SET NEW.last_seen = NOW();
    END IF;
END //
DELIMITER ;

COMMIT;

-- ============================================
-- 🎉 Database Setup Complete!
-- ============================================
-- 
-- Default Accounts:
-- Teacher: teacher@test.com / password123
-- Student: student@test.com / password123
-- 
-- Sample Room Code: DEMO123
-- ============================================