-- ============================================================================
-- Migration v11 — Geofence event logging (alertas_eventos)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `alertas_eventos` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tipo`                VARCHAR(60)   NOT NULL COMMENT 'geofenceEnter, geofenceExit, etc.',
    `traccar_event_id`    INT           DEFAULT NULL COMMENT 'Traccar event ID (de-dup)',
    `device_name`         VARCHAR(150)  DEFAULT NULL COMMENT 'Nombre del dispositivo',
    `traccar_device_id`   INT           DEFAULT NULL COMMENT 'ID dispositivo en Traccar',
    `geozona_nombre`      VARCHAR(150)  DEFAULT NULL COMMENT 'Nombre de la geozona',
    `traccar_geofence_id` INT           DEFAULT NULL COMMENT 'ID geozona en Traccar',
    `mensaje`             TEXT          NOT NULL COMMENT 'Descripción legible del evento',
    `evento_at`           DATETIME      NOT NULL COMMENT 'Fecha/hora del evento (Traccar serverTime)',
    `created_at`          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_traccar_event` (`traccar_event_id`),
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_evento_at` (`evento_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log de eventos de geozonas y alertas desde Traccar';
