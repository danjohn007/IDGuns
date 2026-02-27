-- =============================================================================
-- IDGuns — Migración v4: Notificaciones, Perfil de Usuario y Catálogos
-- Ejecutar sobre la base de datos existente
-- Compatible con MySQL 5.7.x
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -----------------------------------------------------------------------------
-- 1. notificaciones — sistema de notificaciones internas del sistema
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notificaciones` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED         NOT NULL COMMENT 'Usuario destinatario',
    `tipo`       VARCHAR(60)          NOT NULL DEFAULT 'sistema' COMMENT 'Tipo de notificación',
    `mensaje`    TEXT                 NOT NULL COMMENT 'Contenido de la notificación',
    `url`        VARCHAR(255)         DEFAULT NULL COMMENT 'URL de destino al hacer clic',
    `leido`      TINYINT(1) UNSIGNED  NOT NULL DEFAULT 0 COMMENT '0=no leída, 1=leída',
    `leido_at`   DATETIME             DEFAULT NULL COMMENT 'Fecha de lectura',
    `created_at` DATETIME             NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notif_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Notificaciones internas del sistema';

-- -----------------------------------------------------------------------------
-- 2. Agregar columna email a users si no existe (para recuperación de contraseña)
--    (ya existe en el schema original, pero por compatibilidad con instalaciones antiguas)
-- -----------------------------------------------------------------------------
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'email'
);

SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `email` VARCHAR(120) DEFAULT NULL AFTER `username`',
  'SELECT ''[SKIP] users.email ya existe'';'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 3. Catálogo de suministros — asegurar que existan las categorías básicas
--    (para que el formulario de Nuevo Suministro cargue categorías)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('suministros_categoria', 'limpieza',    'Limpieza',    1),
('suministros_categoria', 'papeleria',   'Papelería',   2),
('suministros_categoria', 'uniforme',    'Uniformes',   3),
('suministros_categoria', 'municion',    'Munición',    4),
('suministros_categoria', 'herramienta', 'Herramienta', 5),
('suministros_categoria', 'otro',        'Otro',        6);

-- -----------------------------------------------------------------------------
-- 4. Catálogos adicionales — activos_categoria básicos si no existen
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('activos_categoria', 'arma',           'Arma',              1),
('activos_categoria', 'vehiculo',       'Vehículo',          2),
('activos_categoria', 'equipo_computo', 'Equipo de Cómputo', 3),
('activos_categoria', 'equipo_oficina', 'Equipo de Oficina', 4),
('activos_categoria', 'bien_mueble',    'Bien Mueble',       5);

-- -----------------------------------------------------------------------------
-- 5. Catálogos adicionales — vehiculos_tipo básicos si no existen
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('vehiculos_tipo', 'patrulla',   'Patrulla',   1),
('vehiculos_tipo', 'moto',       'Motocicleta',2),
('vehiculos_tipo', 'camioneta',  'Camioneta',  3),
('vehiculos_tipo', 'otro',       'Otro',       4);

-- -----------------------------------------------------------------------------
-- 6. Configuraciones de apariencia — asegurar que existan valores por defecto
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `configuraciones` (`clave`, `valor`, `descripcion`) VALUES
('color_primario',   '#4f46e5', 'Color principal de la interfaz'),
('color_secundario', '#111827', 'Color secundario (sidebar)');

SET foreign_key_checks = 1;
