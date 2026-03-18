-- Extend existing sync_inbox for ACK low-latency mode

ALTER TABLE `sync_inbox`
  MODIFY COLUMN `status` VARCHAR(32) NOT NULL DEFAULT 'processing';

ALTER TABLE `sync_inbox`
  ADD COLUMN IF NOT EXISTS `source_entity_id` BIGINT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `upstream_id` VARCHAR(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `ack_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS `ack_attempts` INT NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `ack_http_code` INT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `ack_last_error` TEXT,
  ADD COLUMN IF NOT EXISTS `ack_next_attempt_at` DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `ack_at` DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `doneAt` DATETIME DEFAULT NULL;

ALTER TABLE `sync_inbox`
  ADD INDEX `idx_sync_inbox_ack_status_next` (`ack_status`, `ack_next_attempt_at`);
