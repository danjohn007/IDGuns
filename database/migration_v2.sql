-- =============================================================================
-- IDGuns - Migration: New Features
-- Version 2.0
-- Adds: catalogos table, PySpark config entries
-- Safe to run on existing databases
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -----------------------------------------------------------------------------
-- 1. catalogos — dynamic catalog entries for categories and vehicle types
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `catalogos` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tipo`       VARCHAR(60)  NOT NULL COMMENT 'activos_categoria | suministros_categoria | vehiculos_tipo',
    `clave`      VARCHAR(60)  NOT NULL COMMENT 'machine-readable key',
    `etiqueta`   VARCHAR(120) NOT NULL COMMENT 'human-readable label',
    `orden`      SMALLINT     NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_catalogos_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Dynamic catalog entries for categories and types';

-- -----------------------------------------------------------------------------
-- 2. Seed default catalog entries (mirroring existing ENUM values)
-- Use INSERT IGNORE to avoid duplicates on re-run
-- -----------------------------------------------------------------------------

-- Asset categories
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('activos_categoria', 'arma',           'Arma',              1),
('activos_categoria', 'vehiculo',        'Vehículo',          2),
('activos_categoria', 'equipo_computo',  'Equipo de Cómputo', 3),
('activos_categoria', 'equipo_oficina',  'Equipo de Oficina', 4),
('activos_categoria', 'bien_mueble',     'Bien Mueble',       5);

-- Supply categories
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('suministros_categoria', 'limpieza',    'Limpieza',    1),
('suministros_categoria', 'papeleria',   'Papelería',   2),
('suministros_categoria', 'uniforme',    'Uniforme',    3),
('suministros_categoria', 'municion',    'Munición',    4),
('suministros_categoria', 'herramienta', 'Herramienta', 5),
('suministros_categoria', 'otro',        'Otro',        6);

-- Vehicle types
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('vehiculos_tipo', 'patrulla',  'Patrulla',    1),
('vehiculos_tipo', 'moto',      'Moto',        2),
('vehiculos_tipo', 'camioneta', 'Camioneta',   3),
('vehiculos_tipo', 'otro',      'Otro',        4);

-- -----------------------------------------------------------------------------
-- 3. PySpark configuration keys (safe INSERT IGNORE)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `configuraciones` (`clave`, `valor`, `descripcion`) VALUES
('pyspark_url',   '', 'URL del API REST del servicio PySpark'),
('pyspark_token', '', 'Token de autenticación del API PySpark');

SET foreign_key_checks = 1;
