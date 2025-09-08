-- ============================================
-- 🔐 Chat Room Realtime - Authentication Only Database Schema
-- ============================================
-- 
-- INSTRUKSI:
-- 1. Buat database baru di phpMyAdmin
-- 2. Import file ini (hanya untuk authentication)
-- 3. Firebase tetap digunakan untuk chat, rooms, messages

-- Set charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- Table: users (untuk authentication saja)
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('teacher','student') NOT NULL DEFAULT 'student',
  `avatar` varchar(255) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_online` (`is_online`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: user_sessions (untuk session management)
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

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 🎉 Database Setup Complete!
-- ============================================
-- 
-- Default Accounts:
-- Teacher: teacher@test.com / password123
-- Student: student@test.com / password123
-- 
-- Firebase tetap digunakan untuk:
-- - Rooms management
-- - Messages (real-time chat)
-- - File uploads
-- ============================================