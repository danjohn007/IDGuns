-- =============================================================================
-- Migración v11 (sin information_schema): idempotente por HANDLER
-- =============================================================================
SET NAMES utf8mb4;

DROP PROCEDURE IF EXISTS `migration_v11`;
DELIMITER $$

CREATE PROCEDURE `migration_v11`()
BEGIN
  -- -------------------------
  -- 1) Columna km_por_litro
  -- -------------------------
  BEGIN
    DECLARE CONTINUE HANDLER FOR 1060 BEGIN END; -- Duplicate column name
    ALTER TABLE `dispositivos_gps`
      ADD COLUMN `km_por_litro` DECIMAL(8,2) DEFAULT NULL
      COMMENT 'km por litro de gasolina (valor individual, sobrescribe el global del reporte)'
      AFTER `activo`;
  END;

  -- -------------------------
  -- 2) Índices
  -- -------------------------
  BEGIN
    DECLARE CONTINUE HANDLER FOR 1061 BEGIN END; -- Duplicate key name
    ALTER TABLE `dispositivos_gps`
      ADD INDEX `idx_unique_id` (`unique_id`);
  END;

  BEGIN
    DECLARE CONTINUE HANDLER FOR 1061 BEGIN END; -- Duplicate key name
    ALTER TABLE `dispositivos_gps`
      ADD INDEX `idx_traccar_device_id` (`traccar_device_id`);
  END;

  -- -------------------------
  -- 3) Tabla gps_km_reportes
  -- -------------------------
  CREATE TABLE IF NOT EXISTS `gps_km_reportes` (
      `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `dispositivo_id`    INT UNSIGNED  NOT NULL COMMENT 'FK → dispositivos_gps.id',
      `traccar_device_id` INT           NOT NULL COMMENT 'ID del dispositivo en el servidor Traccar',
      `fecha_desde`       DATE          NOT NULL COMMENT 'Inicio del período consultado',
      `fecha_hasta`       DATE          NOT NULL COMMENT 'Fin del período consultado',
      `distancia_m`       DECIMAL(14,2) DEFAULT NULL COMMENT 'Distancia total en metros (campo distance de Traccar)',
      `engine_hours_ms`   BIGINT UNSIGNED DEFAULT NULL COMMENT 'Horas de motor en milisegundos (campo engineHours de Traccar)',
      `velocidad_max`     DECIMAL(8,4)  DEFAULT NULL COMMENT 'Velocidad máxima en nudos (campo maxSpeed de Traccar)',
      `consultado_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora en que se consultó el API de Traccar',
      `updated_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY `uk_gps_km_dispositivo_periodo` (`dispositivo_id`, `fecha_desde`, `fecha_hasta`),
      CONSTRAINT `fk_gps_km_dispositivo`
          FOREIGN KEY (`dispositivo_id`)
          REFERENCES `dispositivos_gps`(`id`)
          ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    COMMENT='Caché de resúmenes de kilometraje de Traccar por dispositivo y período';
END$$

DELIMITER ;

CALL `migration_v11`();
DROP PROCEDURE IF EXISTS `migration_v11`;
