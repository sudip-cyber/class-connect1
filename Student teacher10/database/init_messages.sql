-- messages table for student/teacher chat
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_type` ENUM('student','teacher') NOT NULL,
  `from_id` VARCHAR(128) NOT NULL,
  `to_type` ENUM('student','teacher') NOT NULL,
  `to_id` VARCHAR(128) NOT NULL,
  `message` TEXT,
  `attachment` VARCHAR(512) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  INDEX `idx_conv` (`from_type`,`from_id`,`to_type`,`to_id`),
  INDEX `idx_to` (`to_type`,`to_id`),
  INDEX `idx_from` (`from_type`,`from_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
