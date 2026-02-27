-- =============================================================================
-- IDGuns — Migración v3: Personal, Geozonas, Alertas y Catálogos
-- Ejecutar sobre la base de datos existente (residenc_idguns)
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -----------------------------------------------------------------------------
-- 1. personal — Módulo de alta de personal (oficiales, empleados, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personal` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`     VARCHAR(120) NOT NULL,
    `apellidos`  VARCHAR(120) NOT NULL DEFAULT '',
    `cargo`      VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Cargo / rango del personal',
    `email`      VARCHAR(120) DEFAULT NULL,
    `telefono`   VARCHAR(30)  DEFAULT NULL,
    `numero_empleado` VARCHAR(40) DEFAULT NULL COMMENT 'Número de placa o empleado',
    `activo`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de personal del sistema';

-- -----------------------------------------------------------------------------
-- 2. Agregar personal_id a activos (responsable desde módulo personal)
--    Mantenemos responsable_id (→ users) para compatibilidad con registros previos
-- -----------------------------------------------------------------------------
ALTER TABLE `activos`
    ADD COLUMN IF NOT EXISTS `personal_id` INT UNSIGNED DEFAULT NULL
        COMMENT 'Responsable del activo (tabla personal)' AFTER `responsable_id`;

ALTER TABLE `activos`
    ADD CONSTRAINT IF NOT EXISTS `fk_activos_personal`
        FOREIGN KEY (`personal_id`) REFERENCES `personal`(`id`) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- 3. Agregar personal_id a vehiculos
-- -----------------------------------------------------------------------------
ALTER TABLE `vehiculos`
    ADD COLUMN IF NOT EXISTS `personal_id` INT UNSIGNED DEFAULT NULL
        COMMENT 'Responsable del vehículo (tabla personal)' AFTER `responsable_id`;

ALTER TABLE `vehiculos`
    ADD CONSTRAINT IF NOT EXISTS `fk_vehiculos_personal`
        FOREIGN KEY (`personal_id`) REFERENCES `personal`(`id`) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- 4. Catalog entries: personal_cargo (cargos del personal)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('personal_cargo', 'comandante',    'Comandante',    1),
('personal_cargo', 'subcomandante', 'Subcomandante', 2),
('personal_cargo', 'teniente',      'Teniente',      3),
('personal_cargo', 'sargento',      'Sargento',      4),
('personal_cargo', 'cabo',          'Cabo',          5),
('personal_cargo', 'policia',       'Policía',       6),
('personal_cargo', 'administrativo','Administrativo',7),
('personal_cargo', 'otro',          'Otro',          8);

-- -----------------------------------------------------------------------------
-- 5. geozonas_locales — registro local de geofences sincronizados con Traccar
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `geozonas` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `traccar_id`        INT          DEFAULT NULL COMMENT 'ID de la geozona en el servidor Traccar',
    `nombre`            VARCHAR(150) NOT NULL,
    `descripcion`       TEXT         DEFAULT NULL,
    `area`              TEXT         DEFAULT NULL COMMENT 'Definición WKT o JSON de la geozona',
    `activo_id`         INT UNSIGNED DEFAULT NULL COMMENT 'Activo asociado (opcional)',
    `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_geozonas_activo`
        FOREIGN KEY (`activo_id`) REFERENCES `activos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Geozonas registradas (espejo local de Traccar)';

-- -----------------------------------------------------------------------------
-- 6. alertas_reglas — reglas de alertas y notificaciones
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alertas_reglas` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`      VARCHAR(150) NOT NULL COMMENT 'Nombre descriptivo de la regla',
    `tipo`        VARCHAR(60)  NOT NULL DEFAULT 'geofenceExit'
                  COMMENT 'Tipo Traccar: geofenceExit, geofenceEnter, speeding, deviceOffline, etc.',
    `activo_id`   INT UNSIGNED DEFAULT NULL COMMENT 'Activo al que aplica (NULL = todos)',
    `geozona_id`  INT UNSIGNED DEFAULT NULL COMMENT 'Geozona asociada (para reglas de perímetro)',
    `notificar_email` TINYINT(1) NOT NULL DEFAULT 1,
    `notificar_whatsapp` TINYINT(1) NOT NULL DEFAULT 0,
    `activo`      TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_alertas_activo`
        FOREIGN KEY (`activo_id`) REFERENCES `activos`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_alertas_geozona`
        FOREIGN KEY (`geozona_id`) REFERENCES `geozonas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Reglas de alertas y notificaciones del sistema';

-- -----------------------------------------------------------------------------
-- 7. Seed catalog: activos_categoria (agregar 'movil' por si no existía)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalogos` (`tipo`, `clave`, `etiqueta`, `orden`) VALUES
('activos_categoria', 'movil', 'Móvil / Celular', 0);

SET foreign_key_checks = 1;
