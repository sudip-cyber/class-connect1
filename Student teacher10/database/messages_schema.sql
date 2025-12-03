-- Messaging database schema for Student teacher10
-- Creates the messaging database and tables used by the app's chat system.

CREATE DATABASE IF NOT EXISTS `student_teacher_messages`
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

USE `student_teacher_messages`;

-- Messages table: stores each message and optional attachment URL/path
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_type` VARCHAR(32) NOT NULL,
  `from_id` VARCHAR(64) NOT NULL,
  `to_type` VARCHAR(32) NOT NULL,
  `to_id` VARCHAR(64) NOT NULL,
  `message` LONGTEXT,
  `attachment` VARCHAR(512) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  INDEX `idx_to` (`to_type`,`to_id`),
  INDEX `idx_from` (`from_type`,`from_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Message deletions: records per-user deletions and delete-for-everyone markers
CREATE TABLE IF NOT EXISTS `message_deletions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `message_id` INT UNSIGNED NOT NULL,
  `deleted_by_type` VARCHAR(32) NOT NULL,
  `deleted_by_id` VARCHAR(64) NOT NULL,
  `deleted_for_everyone` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_message_user` (`message_id`,`deleted_by_type`,`deleted_by_id`),
  INDEX `idx_message_id` (`message_id`),
  CONSTRAINT `fk_msgdel_message` FOREIGN KEY (`message_id`) REFERENCES `messages`(`message_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: an index to speed searching deletions by user
CREATE INDEX IF NOT EXISTS `idx_deleted_by` ON `message_deletions` (`deleted_by_type`,`deleted_by_id`);

-- Example seed (optional): create a canonical group message row
-- INSERT INTO messages (from_type, from_id, to_type, to_id, message) VALUES ('teacher','system','group','all','Welcome to the all-staff group chat');
