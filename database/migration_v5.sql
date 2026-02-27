-- =============================================================================
-- IDGuns — Migración v5: Nuevas Categorías de Suministros y Zona Horaria
-- Ejecutar sobre la base de datos existente
-- Compatible con MySQL 5.7.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Agregar categorías de suministros: Tonner y Equipo (si no existen)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('suministros_categoria', 'tonner', 'Tonner', 6),
('suministros_categoria', 'equipo', 'Equipo', 7);

-- Reordenar 'otro' al final (orden 8) en caso de que ya exista
UPDATE `catalogos`
SET `orden` = 8
WHERE `tipo` = 'suministros_categoria' AND `clave` = 'otro';

-- -----------------------------------------------------------------------------
-- 2. Configuración de zona horaria por defecto (CDMX)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `configuraciones` (`clave`, `valor`, `descripcion`)
VALUES ('app_timezone', 'America/Mexico_City', 'Zona horaria del sistema');
