-- Extend existing sync_inbox for ACK low-latency mode

SET @db := DATABASE();

-- source_entity_id
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='source_entity_id'
    ),
    'SELECT ''source_entity_id exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `source_entity_id` BIGINT DEFAULT NULL'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- upstream_id
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='upstream_id'
    ),
    'SELECT ''upstream_id exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `upstream_id` VARCHAR(64) DEFAULT NULL'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ack_status
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='ack_status'
    ),
    'SELECT ''ack_status exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `ack_status` VARCHAR(32) NOT NULL DEFAULT ''pending'''
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ack_attempts
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='ack_attempts'
    ),
    'SELECT ''ack_attempts exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `ack_attempts` INT NOT NULL DEFAULT 0'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ack_http_code
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='ack_http_code'
    ),
    'SELECT ''ack_http_code exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `ack_http_code` INT DEFAULT NULL'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ack_last_error
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='ack_last_error'
    ),
    'SELECT ''ack_last_error exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `ack_last_error` TEXT'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ack_next_attempt_at
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='ack_next_attempt_at'
    ),
    'SELECT ''ack_next_attempt_at exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `ack_next_attempt_at` DATETIME DEFAULT NULL'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ack_at
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='ack_at'
    ),
    'SELECT ''ack_at exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `ack_at` DATETIME DEFAULT NULL'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- doneAt
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND COLUMN_NAME='doneAt'
    ),
    'SELECT ''doneAt exists''',
    'ALTER TABLE `sync_inbox` ADD COLUMN `doneAt` DATETIME DEFAULT NULL'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Index ack_status + ack_next_attempt_at
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='sync_inbox' AND INDEX_NAME='idx_sync_inbox_ack_status_next'
    ),
    'SELECT ''idx_sync_inbox_ack_status_next exists''',
    'ALTER TABLE `sync_inbox` ADD INDEX `idx_sync_inbox_ack_status_next` (`ack_status`, `ack_next_attempt_at`)'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
