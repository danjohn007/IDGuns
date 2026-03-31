-- Migration v12: Add traccar_notification_id to alertas_reglas
-- This stores the Traccar notification ID so IDGuns can manage notifications
-- via the Traccar API without needing to visit the Traccar web interface.

ALTER TABLE `alertas_reglas`
    ADD COLUMN `traccar_notification_id` INT UNSIGNED DEFAULT NULL
    COMMENT 'ID de la notificación creada en Traccar'
    AFTER `geozona_id`;
