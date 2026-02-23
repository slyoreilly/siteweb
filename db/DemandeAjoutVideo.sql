CREATE TABLE `DemandeAjoutVideo` (
  `demandeId` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `typeEvenement` int(11) NOT NULL,
  `chronoDemande` bigint(20) NOT NULL,
  `cameraId` int(11) NOT NULL,
  `progression` tinyint(4) NOT NULL DEFAULT 1,
  `systemLeagueId` int(11) DEFAULT NULL,
  `chronoVideo` bigint(20) DEFAULT NULL,
  `videoNomFichier` varchar(255) DEFAULT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`demandeId`),
  KEY `idx_dav_progression_camera` (`progression`,`cameraId`),
  KEY `idx_dav_eventId` (`eventId`),
  KEY `idx_dav_chronoVideo` (`chronoVideo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
