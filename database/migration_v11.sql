-- =============================================================================
-- IDGuns — Migración v11: Transporte de datos Traccar a la DB del sistema
-- Asegura que todas las estructuras necesarias para el módulo "Reportes GPS"
-- existan y sean idempotentes (pueden ejecutarse varias veces sin error).
-- Compatible con MySQL 5.7.x / 8.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Columna km_por_litro en dispositivos_gps (introducida en migration_v8)
--    Rendimiento de combustible individual por dispositivo/activo.
-- -----------------------------------------------------------------------------
SET @col_kml := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'dispositivos_gps'
      AND COLUMN_NAME  = 'km_por_litro'
);
SET @sql_kml := IF(@col_kml = 0,
    'ALTER TABLE `dispositivos_gps`
     ADD COLUMN `km_por_litro` DECIMAL(8,2) DEFAULT NULL
     COMMENT ''km por litro de gasolina (valor individual, sobrescribe el global del reporte)''
     AFTER `activo`',
    'SELECT ''[SKIP] km_por_litro ya existe'''
);
PREPARE _s FROM @sql_kml; EXECUTE _s; DEALLOCATE PREPARE _s;

-- -----------------------------------------------------------------------------
-- 2. Índices de rendimiento en dispositivos_gps (introducidos en migration_v9)
-- -----------------------------------------------------------------------------
SET @idx_uid := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'dispositivos_gps'
      AND INDEX_NAME   = 'idx_unique_id'
);
SET @sql_uid := IF(@idx_uid = 0,
    'ALTER TABLE `dispositivos_gps` ADD INDEX `idx_unique_id` (`unique_id`)',
    'SELECT ''[SKIP] idx_unique_id ya existe'''
);
PREPARE _s FROM @sql_uid; EXECUTE _s; DEALLOCATE PREPARE _s;

SET @idx_tid := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'dispositivos_gps'
      AND INDEX_NAME   = 'idx_traccar_device_id'
);
SET @sql_tid := IF(@idx_tid = 0,
    'ALTER TABLE `dispositivos_gps` ADD INDEX `idx_traccar_device_id` (`traccar_device_id`)',
    'SELECT ''[SKIP] idx_traccar_device_id ya existe'''
);
PREPARE _s FROM @sql_tid; EXECUTE _s; DEALLOCATE PREPARE _s;

-- -----------------------------------------------------------------------------
-- 3. Tabla gps_km_reportes (introducida en migration_v10)
--    Caché de resúmenes de kilometraje consultados desde el API de Traccar.
--    Almacena distance, engineHours y maxSpeed por dispositivo y rango de fechas,
--    y se usa tanto para mostrar km en "Reportes GPS" como para el cálculo del
--    COSTO ESTIMADO de combustible.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gps_km_reportes` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dispositivo_id`    INT UNSIGNED  NOT NULL
                            COMMENT 'FK → dispositivos_gps.id',
    `traccar_device_id` INT           NOT NULL
                            COMMENT 'ID del dispositivo en el servidor Traccar',
    `fecha_desde`       DATE          NOT NULL
                            COMMENT 'Inicio del período consultado',
    `fecha_hasta`       DATE          NOT NULL
                            COMMENT 'Fin del período consultado',
    `distancia_m`       DECIMAL(14,2) DEFAULT NULL
                            COMMENT 'Distancia total en metros (campo distance de Traccar)',
    `engine_hours_ms`   BIGINT UNSIGNED DEFAULT NULL
                            COMMENT 'Horas de motor en milisegundos (campo engineHours de Traccar)',
    `velocidad_max`     DECIMAL(8,4)  DEFAULT NULL
                            COMMENT 'Velocidad máxima en nudos (campo maxSpeed de Traccar)',
    `consultado_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                            COMMENT 'Fecha/hora en que se consultó el API de Traccar',
    `updated_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_gps_km_dispositivo_periodo`
        (`dispositivo_id`, `fecha_desde`, `fecha_hasta`),
    CONSTRAINT `fk_gps_km_dispositivo`
        FOREIGN KEY (`dispositivo_id`)
        REFERENCES `dispositivos_gps`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Caché de resúmenes de kilometraje de Traccar por dispositivo y período';

-- -----------------------------------------------------------------------------
-- 4. Verificación: confirmar que las tablas y columnas clave existen
-- -----------------------------------------------------------------------------
SELECT
    'dispositivos_gps.km_por_litro'   AS columna,
    IF(COUNT(*) > 0, 'OK', 'FALTA')   AS estado
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'dispositivos_gps'
  AND COLUMN_NAME  = 'km_por_litro'

UNION ALL

SELECT
    'gps_km_reportes (tabla)' AS columna,
    IF(COUNT(*) > 0, 'OK', 'FALTA') AS estado
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'gps_km_reportes';
