-- message deletions table for soft-delete / delete-for-everyone support
CREATE TABLE IF NOT EXISTS `message_deletions` (
  `message_id` INT UNSIGNED NOT NULL,
  `deleted_by_type` ENUM('student','teacher') NOT NULL,
  `deleted_by_id` VARCHAR(128) NOT NULL,
  `deleted_for_everyone` TINYINT(1) DEFAULT 0,
  `deleted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`, `deleted_by_type`, `deleted_by_id`),
  INDEX `idx_deleted_message` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
