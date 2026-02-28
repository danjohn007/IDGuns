-- =============================================================================
-- IDGuns — Migración v8: km/Litro por dispositivo GPS y categoría Móvil/Celular
-- Ejecutar sobre la base de datos existente
-- Compatible con MySQL 5.7.x / 8.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Agregar columna km_por_litro a dispositivos_gps
--    Permite registrar el rendimiento de combustible de cada dispositivo/activo
--    de forma independiente, tomando prioridad sobre el valor global del reporte.-- -----------------------------------------------------------------------------
SET @col_kml_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'dispositivos_gps'
      AND COLUMN_NAME  = 'km_por_litro'
);

SET @sql_add_kml := IF(@col_kml_exists = 0,
    'ALTER TABLE `dispositivos_gps` ADD COLUMN `km_por_litro` DECIMAL(8,2) DEFAULT NULL COMMENT ''km por litro de gasolina (valor individual, sobrescribe el global del reporte)'' AFTER `activo`',
    'SELECT ''[SKIP] km_por_litro ya existe'''
);

PREPARE stmt FROM @sql_add_kml;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 2. Asegurar que todas las categorías de activos iniciales estén en el catálogo
--    (incluye Móvil / Celular que fue añadida dinámicamente)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('activos_categoria', 'arma',          'Arma',             1),
('activos_categoria', 'vehiculo',      'Vehículo',         2),
('activos_categoria', 'equipo_computo','Equipo de Cómputo',3),
('activos_categoria', 'equipo_oficina','Equipo de Oficina', 4),
('activos_categoria', 'bien_mueble',   'Bien Mueble',      5),
('activos_categoria', 'movil',         'Móvil / Celular',  6);

-- -----------------------------------------------------------------------------
-- 3. Asegurar que activos.categoria sea VARCHAR (no ENUM) para soportar
--    categorías dinámicas del catálogo.
--    Si ya se aplicó migration_v7 esta sentencia no hace cambios; si no se
--    aplicó, la ejecuta aquí de forma idempotente.
-- -----------------------------------------------------------------------------
SET @cat_type := (
    SELECT DATA_TYPE
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'activos'
      AND COLUMN_NAME  = 'categoria'
);

SET @sql_cat := IF(@cat_type = 'enum',
    'ALTER TABLE `activos` MODIFY COLUMN `categoria` VARCHAR(80) NOT NULL DEFAULT ''otro''',
    'SELECT ''[SKIP] activos.categoria ya es VARCHAR'''
);

PREPARE stmt FROM @sql_cat;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
