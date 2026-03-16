-- Sync inbound idempotency inbox
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
  `status` ENUM('processing','processed','rejected','failed') NOT NULL DEFAULT 'processing',
  `responseCode` INT DEFAULT NULL,
  `errorMessage` TEXT,
  `retryCount` INT NOT NULL DEFAULT 0,
  `processedAt` DATETIME DEFAULT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`syncInboxId`),
  UNIQUE KEY `uq_sync_inbox_endpoint_dedupe` (`endpoint`, `dedupeKey`),
  KEY `idx_sync_inbox_status_createdAt` (`status`, `createdAt`),
  KEY `idx_sync_inbox_messageId` (`messageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
