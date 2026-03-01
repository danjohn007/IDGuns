-- =============================================================================
-- IDGuns — Migración v9: Índice en unique_id para búsqueda de dispositivos GPS
-- Mejora el rendimiento al buscar dispositivos por IMEI / unique_id en Traccar.
-- Compatible con MySQL 5.7.x / 8.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Agregar índice en dispositivos_gps.unique_id
--    Permite búsqueda rápida por IMEI cuando traccar_device_id no está poblado.
-- -----------------------------------------------------------------------------
SET @idx_uid_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'dispositivos_gps'
      AND INDEX_NAME   = 'idx_unique_id'
);

SET @sql_add_idx := IF(@idx_uid_exists = 0,
    'ALTER TABLE `dispositivos_gps` ADD INDEX `idx_unique_id` (`unique_id`)',
    'SELECT ''[SKIP] índice idx_unique_id ya existe'''
);

PREPARE stmt FROM @sql_add_idx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 2. Agregar índice en dispositivos_gps.traccar_device_id
--    Acelera la consulta de reportes por ID de dispositivo Traccar.
-- -----------------------------------------------------------------------------
SET @idx_tid_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'dispositivos_gps'
      AND INDEX_NAME   = 'idx_traccar_device_id'
);

SET @sql_add_tidx := IF(@idx_tid_exists = 0,
    'ALTER TABLE `dispositivos_gps` ADD INDEX `idx_traccar_device_id` (`traccar_device_id`)',
    'SELECT ''[SKIP] índice idx_traccar_device_id ya existe'''
);

PREPARE stmt FROM @sql_add_tidx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
