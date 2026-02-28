-- =============================================================================
-- IDGuns — Migración v7: Categorías Dinámicas de Activos/Vehículos y renombrado de columna 'año'
-- Ejecutar sobre la base de datos existente
-- Compatible con MySQL 5.7.x / 8.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Cambiar columna 'categoria' de activos de ENUM a VARCHAR(80)
--    Esto permite registrar categorías dinámicas definidas en el catálogo
-- -----------------------------------------------------------------------------
ALTER TABLE `activos`
    MODIFY COLUMN `categoria` VARCHAR(80) NOT NULL DEFAULT 'otro';

-- -----------------------------------------------------------------------------
-- 2. Cambiar columna 'tipo' de vehiculos de ENUM a VARCHAR(80)
--    Esto permite registrar tipos de vehículo dinámicos del catálogo
-- -----------------------------------------------------------------------------
ALTER TABLE `vehiculos`
    MODIFY COLUMN `tipo` VARCHAR(80) NOT NULL DEFAULT 'otro';

-- -----------------------------------------------------------------------------
-- 3. Renombrar columna 'año' → 'anio' en vehiculos
--    El carácter ñ en el nombre de columna causa problemas con PDO (parámetros
--    nombrados no admiten caracteres no-ASCII) lo que generaba el error:
--    SQLSTATE[42000]: Syntax error or access violation: 1064 ...near '?, ?, ...'
-- -----------------------------------------------------------------------------
SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'vehiculos'
      AND COLUMN_NAME  = 'año'
);

SET @sql_rename := IF(@col_exists > 0,
    'ALTER TABLE `vehiculos` CHANGE COLUMN `año` `anio` YEAR DEFAULT NULL',
    'SELECT ''[SKIP] columna anio ya existe'''
);

PREPARE stmt FROM @sql_rename;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 4. Asegurar que los catálogos de categorías de activos existan
--    (valores por defecto del schema original)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('activos_categoria', 'arma',          'Arma',               1),
('activos_categoria', 'vehiculo',      'Vehículo',           2),
('activos_categoria', 'equipo_computo','Equipo de Cómputo',  3),
('activos_categoria', 'equipo_oficina','Equipo de Oficina',  4),
('activos_categoria', 'bien_mueble',   'Bien Mueble',        5);

-- -----------------------------------------------------------------------------
-- 5. Asegurar que los catálogos de tipos de vehículo existan
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('vehiculos_tipo', 'patrulla',  'Patrulla',    1),
('vehiculos_tipo', 'moto',      'Motocicleta', 2),
('vehiculos_tipo', 'camioneta', 'Camioneta',   3),
('vehiculos_tipo', 'otro',      'Otro',        4);
