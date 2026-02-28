-- =============================================================================
-- IDGuns — Migración v6: Categorías de Suministros Dinámicas y Sync de Geozonas
-- Ejecutar sobre la base de datos existente
-- Compatible con MySQL 5.7.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Cambiar columna categoria de suministros de ENUM a VARCHAR(60)
--    Esto permite almacenar cualquier categoría dada de alta en CATÁLOGOS,
--    sin quedar limitado a los valores originales del ENUM.
--    Los datos existentes se preservan (MySQL convierte ENUM a su cadena).
-- -----------------------------------------------------------------------------
ALTER TABLE `suministros`
    MODIFY COLUMN `categoria` VARCHAR(60) NOT NULL DEFAULT 'otro';

-- -----------------------------------------------------------------------------
-- 2. Asegurar que las categorías nuevas (Tonner, Equipo) existan en catalogos
--    (complementa migration_v5 en instalaciones que aún no la aplicaron)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('suministros_categoria', 'tonner', 'Tonner', 6),
('suministros_categoria', 'equipo', 'Equipo', 7);

-- Mover 'otro' al final (orden 8)
UPDATE `catalogos`
SET `orden` = 8
WHERE `tipo` = 'suministros_categoria' AND `clave` = 'otro';

-- -----------------------------------------------------------------------------
-- 3. Agregar índice único en geozonas.traccar_id para soporte de upsert
--    Permite que INSERT IGNORE / ON DUPLICATE KEY evite filas duplicadas
--    cuando se sincronizan geozonas desde Traccar.
-- -----------------------------------------------------------------------------
SET @idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'geozonas'
      AND INDEX_NAME   = 'uq_geozonas_traccar_id'
);

SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE `geozonas` ADD UNIQUE INDEX `uq_geozonas_traccar_id` (`traccar_id`)',
    'SELECT ''[SKIP] uq_geozonas_traccar_id ya existe'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
