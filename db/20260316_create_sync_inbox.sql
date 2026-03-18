-- Sync inbox with ACK low-latency tracking
-- Date: 2026-03-16

CREATE TABLE IF NOT EXISTS `sync_inbox` (
  `syncInboxId` BIGINT NOT NULL AUTO_INCREMENT,
  `endpoint` VARCHAR(64) NOT NULL,
  `dedupeKey` VARCHAR(190) NOT NULL,
  `messageId` BIGINT DEFAULT NULL,
  `aggregateType` VARCHAR(32) NOT NULL,
  `aggregateId` BIGINT DEFAULT NULL,
  `actionType` VARCHAR(16) NOT NULL,
  `payloadJson` MEDIUMTEXT,
  `status` VARCHAR(32) NOT NULL DEFAULT 'processing',
  `responseCode` INT DEFAULT NULL,
  `errorMessage` TEXT,
  `retryCount` INT NOT NULL DEFAULT 0,
  `source_entity_id` BIGINT DEFAULT NULL,
  `upstream_id` VARCHAR(64) DEFAULT NULL,
  `ack_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `ack_attempts` INT NOT NULL DEFAULT 0,
  `ack_http_code` INT DEFAULT NULL,
  `ack_last_error` TEXT,
  `ack_next_attempt_at` DATETIME DEFAULT NULL,
  `ack_at` DATETIME DEFAULT NULL,
  `doneAt` DATETIME DEFAULT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`syncInboxId`),
  UNIQUE KEY `uq_sync_inbox_endpoint_dedupe` (`endpoint`, `dedupeKey`),
  KEY `idx_sync_inbox_status_createdAt` (`status`, `createdAt`),
  KEY `idx_sync_inbox_messageId` (`messageId`),
  KEY `idx_sync_inbox_ack_status_next` (`ack_status`, `ack_next_attempt_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
