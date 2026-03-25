-- Idempotence des creations d'evenements provenant des appareils
-- Date: 2026-03-25

CREATE TABLE IF NOT EXISTS `sync_evenement_idempotence` (
  `syncEvenementIdempotenceId` BIGINT NOT NULL AUTO_INCREMENT,
  `telephone_id` VARCHAR(128) NOT NULL,
  `event_local_id` VARCHAR(128) NOT NULL,
  `instance_id` VARCHAR(128) NOT NULL,
  `event_com_id` INT DEFAULT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`syncEvenementIdempotenceId`),
  UNIQUE KEY `uq_sync_evt_idempotence_tel_event_instance` (`telephone_id`, `event_local_id`, `instance_id`),
  KEY `idx_sync_evt_idempotence_createdAt` (`createdAt`),
  KEY `idx_sync_evt_idempotence_event_com_id` (`event_com_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

