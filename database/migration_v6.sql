-- =============================================================================
-- IDGuns — Migración v6: Categorías de Suministros Dinámicas y Sync de Geozonas
-- Ejecutar sobre la base de datos existente
-- Compatible con MySQL 5.7.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Cambiar columna categoria de suministros de ENUM a VARCHAR(60)
-- -----------------------------------------------------------------------------
ALTER TABLE `suministros`
    MODIFY COLUMN `categoria` VARCHAR(60) NOT NULL DEFAULT 'otro';

-- -----------------------------------------------------------------------------
-- 2. Asegurar que las categorías nuevas (Tonner, Equipo) existan en catalogos
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('suministros_categoria', 'tonner', 'Tonner', 6),
('suministros_categoria', 'equipo', 'Equipo', 7);

-- Mover 'otro' al final (orden 8)
UPDATE `catalogos`
SET `orden` = 8
WHERE `tipo` = 'suministros_categoria' AND `clave` = 'otro';

-- -----------------------------------------------------------------------------
-- 3. Geozonas: deduplicar traccar_id y luego crear UNIQUE INDEX
--    Nota: MySQL 5.7 PREPARE no soporta múltiples sentencias en una sola cadena,
--          por eso aquí solo se usa SQL dinámico para la sentencia ALTER TABLE.
-- -----------------------------------------------------------------------------

-- 3.1 Deduplicación (se ejecuta siempre; si no hay duplicados no hace cambios)
--     Estrategia:
--     - Por cada traccar_id duplicado, el "canon" es MIN(geozonas.id)
--     - Reasignar alertas_reglas.geozona_id al canon
--     - Borrar las filas geozonas duplicadas (id != canon)

DROP TEMPORARY TABLE IF EXISTS tmp_geozona_canon;
CREATE TEMPORARY TABLE tmp_geozona_canon (
  traccar_id INT NOT NULL,
  canon_id   INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (traccar_id),
  KEY (canon_id)
) ENGINE=MEMORY;

INSERT INTO tmp_geozona_canon (traccar_id, canon_id)
SELECT gz.traccar_id, MIN(gz.id) AS canon_id
FROM geozonas gz
WHERE gz.traccar_id IS NOT NULL
GROUP BY gz.traccar_id
HAVING COUNT(*) > 1;

-- Mover referencias en alertas_reglas (evita ON DELETE SET NULL al borrar duplicados)
UPDATE alertas_reglas ar
JOIN geozonas gz
  ON gz.id = ar.geozona_id
JOIN tmp_geozona_canon t
  ON t.traccar_id = gz.traccar_id
SET ar.geozona_id = t.canon_id
WHERE ar.geozona_id IS NOT NULL
  AND ar.geozona_id <> t.canon_id;

-- Borrar duplicados (conservar canon_id)
DELETE gz
FROM geozonas gz
JOIN tmp_geozona_canon t
  ON t.traccar_id = gz.traccar_id
WHERE gz.id <> t.canon_id;

DROP TEMPORARY TABLE IF EXISTS tmp_geozona_canon;

-- 3.2 Crear índice único solo si no existe
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
