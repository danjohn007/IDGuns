-- =============================================================================
-- IDGuns — Migración: Módulo de Geolocalización GPS (Traccar)
-- Ejecutar sobre la base de datos existente (residenc_idguns)
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -----------------------------------------------------------------------------
-- Tabla: dispositivos_gps
-- Almacena los datos del dispositivo GPS (Traccar) enlazado a cada activo
-- Campos basados en la API de Traccar: https://www.traccar.org/api-reference/
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dispositivos_gps` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `activo_id`         INT UNSIGNED NOT NULL COMMENT 'Activo al que pertenece el GPS',
    `traccar_device_id` INT          DEFAULT NULL COMMENT 'ID del dispositivo en el servidor Traccar',
    `nombre`            VARCHAR(150) NOT NULL COMMENT 'Nombre del dispositivo en Traccar',
    `unique_id`         VARCHAR(100) NOT NULL COMMENT 'Identificador único: IMEI, número de teléfono, etc.',
    `telefono`          VARCHAR(30)  DEFAULT NULL COMMENT 'Número de teléfono del dispositivo',
    `modelo_dispositivo` VARCHAR(80) DEFAULT NULL COMMENT 'Modelo del dispositivo GPS (hardware)',
    `contacto`          VARCHAR(150) DEFAULT NULL COMMENT 'Información de contacto del responsable',
    `categoria_traccar` VARCHAR(50)  NOT NULL DEFAULT 'car' COMMENT 'Categoría Traccar: car, motorcycle, truck, van, etc.',
    `grupo_id`          INT          DEFAULT NULL COMMENT 'ID de grupo en Traccar',
    `activo`            TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=habilitado, 0=deshabilitado',
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_gps_activo` FOREIGN KEY (`activo_id`) REFERENCES `activos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Dispositivos GPS Traccar vinculados a activos';

-- -----------------------------------------------------------------------------
-- Configuraciones Traccar (INSERT IGNORE para no sobreescribir valores existentes)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `configuraciones` (`clave`, `valor`, `descripcion`) VALUES
('traccar_url',      '',  'URL base del servidor Traccar (ej: http://demo4.traccar.org)'),
('traccar_usuario',  '',  'Usuario administrador de Traccar'),
('traccar_password', '',  'Contraseña del usuario Traccar');

SET foreign_key_checks = 1;
