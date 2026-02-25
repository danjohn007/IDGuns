-- =============================================================================
-- IDGuns - Control de Armas
-- Secretaría de Seguridad Ciudadana de Querétaro
-- Schema version 1.0
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

CREATE DATABASE IF NOT EXISTS `idguns`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `idguns`;

-- -----------------------------------------------------------------------------
-- 1. users
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`     VARCHAR(120)                           NOT NULL,
    `username`   VARCHAR(60)                            NOT NULL UNIQUE,
    `email`      VARCHAR(120)                           DEFAULT NULL,
    `password`   VARCHAR(255)                           NOT NULL,
    `rol`        ENUM('superadmin','admin','almacen','bitacora') NOT NULL DEFAULT 'bitacora',
    `activo`     TINYINT(1) UNSIGNED                   NOT NULL DEFAULT 1,
    `created_at` DATETIME                               NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 2. oficiales
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficiales` (
    `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`    VARCHAR(80)  NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `placa`     VARCHAR(30)  NOT NULL,
    `rango`     VARCHAR(60)  NOT NULL DEFAULT 'Policía Municipal',
    `turno`     ENUM('matutino','vespertino','nocturno') NOT NULL DEFAULT 'matutino',
    `activo`    TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 3. activos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activos` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `codigo`           VARCHAR(30)  NOT NULL UNIQUE,
    `nombre`           VARCHAR(150) NOT NULL,
    `categoria`        ENUM('arma','vehiculo','equipo_computo','equipo_oficina','bien_mueble') NOT NULL,
    `marca`            VARCHAR(80)  DEFAULT NULL,
    `modelo`           VARCHAR(80)  DEFAULT NULL,
    `serie`            VARCHAR(80)  DEFAULT NULL,
    `color`            VARCHAR(40)  DEFAULT NULL,
    `estado`           ENUM('activo','baja','mantenimiento') NOT NULL DEFAULT 'activo',
    `responsable_id`   INT UNSIGNED DEFAULT NULL,
    `ubicacion`        VARCHAR(150) DEFAULT NULL,
    `descripcion`      TEXT         DEFAULT NULL,
    `fecha_adquisicion` DATE        DEFAULT NULL,
    `valor`            DECIMAL(12,2) DEFAULT NULL,
    `imagen`           VARCHAR(255) DEFAULT NULL,
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`responsable_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 4. armas
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `armas` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `activo_id`           INT UNSIGNED NOT NULL,
    `tipo`                ENUM('pistola','rifle','escopeta','otro') NOT NULL DEFAULT 'pistola',
    `calibre`             VARCHAR(30)  DEFAULT NULL,
    `numero_serie`        VARCHAR(80)  DEFAULT NULL,
    `estado`              ENUM('operativa','mantenimiento','baja') NOT NULL DEFAULT 'operativa',
    `oficial_asignado_id` INT UNSIGNED DEFAULT NULL,
    `municiones_asignadas` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`activo_id`)           REFERENCES `activos`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`oficial_asignado_id`) REFERENCES `oficiales`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 5. vehiculos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vehiculos` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `activo_id`      INT UNSIGNED NOT NULL,
    `tipo`           ENUM('patrulla','moto','camioneta','otro') NOT NULL DEFAULT 'patrulla',
    `placas`         VARCHAR(20)  DEFAULT NULL,
    `año`            YEAR         DEFAULT NULL,
    `color`          VARCHAR(40)  DEFAULT NULL,
    `estado`         ENUM('operativo','taller','baja') NOT NULL DEFAULT 'operativo',
    `kilometraje`    INT UNSIGNED NOT NULL DEFAULT 0,
    `responsable_id` INT UNSIGNED DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`activo_id`)      REFERENCES `activos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`responsable_id`) REFERENCES `users`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 6. suministros
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `suministros` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`          VARCHAR(150) NOT NULL,
    `categoria`       ENUM('limpieza','papeleria','uniforme','municion','herramienta','otro') NOT NULL DEFAULT 'otro',
    `unidad`          VARCHAR(30)  NOT NULL DEFAULT 'pieza',
    `stock_actual`    INT          NOT NULL DEFAULT 0,
    `stock_minimo`    INT          NOT NULL DEFAULT 10,
    `stock_maximo`    INT          NOT NULL DEFAULT 100,
    `ubicacion`       VARCHAR(150) DEFAULT NULL,
    `proveedor`       VARCHAR(120) DEFAULT NULL,
    `precio_unitario` DECIMAL(10,2) DEFAULT NULL,
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 7. movimientos_almacen
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `movimientos_almacen` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `suministro_id`  INT UNSIGNED NOT NULL,
    `tipo`           ENUM('entrada','salida') NOT NULL,
    `cantidad`       INT UNSIGNED NOT NULL,
    `responsable_id` INT UNSIGNED DEFAULT NULL,
    `oficial_id`     INT UNSIGNED DEFAULT NULL,
    `motivo`         VARCHAR(255) DEFAULT NULL,
    `fecha`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `notas`          TEXT         DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`suministro_id`)  REFERENCES `suministros`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`responsable_id`) REFERENCES `users`(`id`)       ON DELETE SET NULL,
    FOREIGN KEY (`oficial_id`)     REFERENCES `oficiales`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 8. mantenimientos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mantenimientos` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vehiculo_id` INT UNSIGNED NOT NULL,
    `tipo`        ENUM('preventivo','correctivo','accidente') NOT NULL,
    `descripcion` TEXT         DEFAULT NULL,
    `fecha_inicio` DATE        DEFAULT NULL,
    `fecha_fin`   DATE         DEFAULT NULL,
    `costo`       DECIMAL(10,2) DEFAULT NULL,
    `proveedor`   VARCHAR(120) DEFAULT NULL,
    `estado`      ENUM('pendiente','en_proceso','completado') NOT NULL DEFAULT 'pendiente',
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 9. combustible
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `combustible` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vehiculo_id`    INT UNSIGNED NOT NULL,
    `litros`         DECIMAL(8,2) NOT NULL,
    `costo`          DECIMAL(10,2) DEFAULT NULL,
    `kilometraje`    INT UNSIGNED NOT NULL DEFAULT 0,
    `responsable_id` INT UNSIGNED DEFAULT NULL,
    `fecha`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vehiculo_id`)    REFERENCES `vehiculos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`responsable_id`) REFERENCES `users`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 10. bitacora
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bitacora` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tipo`            ENUM('entrada','salida','asignacion','devolucion','incidencia') NOT NULL,
    `activo_id`       INT UNSIGNED DEFAULT NULL,
    `activo_tipo`     VARCHAR(60)  DEFAULT NULL,
    `responsable_id`  INT UNSIGNED DEFAULT NULL,
    `oficial_id`      INT UNSIGNED DEFAULT NULL,
    `descripcion`     TEXT         DEFAULT NULL,
    `estado_anterior` VARCHAR(80)  DEFAULT NULL,
    `estado_nuevo`    VARCHAR(80)  DEFAULT NULL,
    `turno`           ENUM('matutino','vespertino','nocturno') NOT NULL DEFAULT 'matutino',
    `fecha`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`activo_id`)      REFERENCES `activos`(`id`)   ON DELETE SET NULL,
    FOREIGN KEY (`responsable_id`) REFERENCES `users`(`id`)     ON DELETE SET NULL,
    FOREIGN KEY (`oficial_id`)     REFERENCES `oficiales`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 11. configuraciones
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuraciones` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `clave`       VARCHAR(80)  NOT NULL UNIQUE,
    `valor`       TEXT         DEFAULT NULL,
    `descripcion` VARCHAR(255) DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 12. dispositivos_iot
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dispositivos_iot` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`        VARCHAR(100) NOT NULL,
    `tipo`          ENUM('hikvision','shelly') NOT NULL DEFAULT 'hikvision',
    `ip`            VARCHAR(45)  DEFAULT NULL,
    `puerto`        SMALLINT UNSIGNED NOT NULL DEFAULT 80,
    `usuario`       VARCHAR(80)  DEFAULT NULL,
    `password_hash` VARCHAR(255) DEFAULT NULL,
    `api_key`       VARCHAR(255) DEFAULT NULL,
    `token`         VARCHAR(255) DEFAULT NULL,
    `activo`        TINYINT(1)   NOT NULL DEFAULT 1,
    `descripcion`   VARCHAR(255) DEFAULT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 13. dispositivos_gps
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
-- 13. errores_sistema
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `errores_sistema` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tipo`       VARCHAR(60)  NOT NULL DEFAULT 'PHP_ERROR',
    `mensaje`    TEXT         DEFAULT NULL,
    `archivo`    VARCHAR(255) DEFAULT NULL,
    `linea`      INT UNSIGNED DEFAULT NULL,
    `usuario_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- EXAMPLE DATA — Querétaro
-- =============================================================================

-- Users — see README.md for default credentials (change before production use)
-- To regenerate hashes: php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
INSERT INTO `users` (`nombre`, `username`, `email`, `password`, `rol`, `activo`) VALUES
('Comandante Arturo Ríos Mendoza',     'superadmin',  'arios@ssc-qro.gob.mx',   '$2y$10$1vozzZrFzgJ5RqLE/DCAR.bo/H.zkoFw.mhVmlRnBuv0BuATcrDwu', 'superadmin', 1),
('Subcomandante Laura Vega Torres',    'lvega',       'lvega@ssc-qro.gob.mx',    '$2y$10$AefpUaWyalL08bnUzeHYX.ughiyC9xqB6iAj.82vm8t3gRGpoJoFO', 'admin',      1),
('Tte. Samuel Herrera Castillo',       'sherrera',    'sherrera@ssc-qro.gob.mx', '$2y$10$AefpUaWyalL08bnUzeHYX.ughiyC9xqB6iAj.82vm8t3gRGpoJoFO', 'admin',      1),
('Sargento Diana López Morales',       'dlopez',      'dlopez@ssc-qro.gob.mx',   '$2y$10$P5c1ckzBeKHxzbJae4IqI.FuF9W9ttqxouP2QXadLdY/hNh9GB0Lu', 'almacen',    1),
('Cabo Ernesto Salinas Pérez',         'esalinas',    'esalinas@ssc-qro.gob.mx', '$2y$10$uh/pKp6rY4Rc8t6iLWxhmOUJ9pZ3jFb.rCakmTLvVyvrB70wRaJeG', 'bitacora',   1);

-- Oficiales
INSERT INTO `oficiales` (`nombre`, `apellidos`, `placa`, `rango`, `turno`) VALUES
('Carlos Alberto', 'Martínez Reyes',    'QRO-001', 'Inspector',            'matutino'),
('María Guadalupe', 'Sánchez Domínguez','QRO-002', 'Subinspector',         'vespertino'),
('José Luis',       'Ramírez Flores',   'QRO-003', 'Oficial de Policía',   'nocturno'),
('Ana Patricia',    'González Moreno',  'QRO-004', 'Oficial de Policía',   'matutino'),
('Roberto',         'Jiménez Vargas',   'QRO-005', 'Oficial de Policía',   'vespertino');

-- Activos — weapons
INSERT INTO `activos` (`codigo`,`nombre`,`categoria`,`marca`,`modelo`,`serie`,`estado`,`responsable_id`,`ubicacion`,`descripcion`,`fecha_adquisicion`,`valor`) VALUES
('ARM-0001','Pistola Glock 17 #001','arma','Glock','17','GBB-MX-2021-001','activo',1,'Armería Principal','Pistola semiautomática calibre 9mm asignada a turno matutino','2021-03-15',12500.00),
('ARM-0002','Pistola Glock 17 #002','arma','Glock','17','GBB-MX-2021-002','activo',2,'Armería Principal','Pistola semiautomática calibre 9mm','2021-03-15',12500.00),
('ARM-0003','Rifle Colt AR-15 #001','arma','Colt','AR-15','CAR15-QRO-001','activo',1,'Armería Seguridad','Rifle de asalto calibre 5.56x45mm para operaciones especiales','2020-08-10',45000.00),
('ARM-0004','Pistola Beretta M9 #001','arma','Beretta','M9','BER-M9-2022-001','activo',2,'Armería Principal','Pistola semiautomática calibre 9mm para supervisores','2022-01-20',15800.00),
('ARM-0005','Escopeta Mossberg 500','arma','Mossberg','500','MB500-QRO-001','mantenimiento',1,'Taller Armería','Escopeta calibre 12 en mantenimiento preventivo','2019-11-05',8900.00),
('VEH-0001','Patrulla Ford Explorer #01','vehiculo','Ford','Explorer','1FMSK8DH5MGA12345','activo',3,'Estacionamiento Norte','Unidad patrulla sector norte Querétaro','2022-06-01',320000.00),
('VEH-0002','Patrulla Dodge Charger #02','vehiculo','Dodge','Charger','2B3HADGT4NH123456','activo',3,'Estacionamiento Sur','Unidad patrulla sector sur Querétaro','2021-11-15',380000.00),
('VEH-0003','Motocicleta Honda CB500 #01','vehiculo','Honda','CB500F','ML8JC7113M5012345','activo',3,'Estacionamiento Norte','Moto de patrullaje urbano','2023-02-10',95000.00),
('EQC-0001','Laptop Dell Latitude 5420','equipo_computo','Dell','Latitude 5420','DL5420-QRO-001','activo',2,'Oficina Comando','Equipo para oficina de comandancia','2022-09-01',28000.00),
('EQO-0001','Escritorio Ejecutivo','equipo_oficina','Muebles Querétaro','EJ-2020','SIN-SERIE-001','activo',1,'Oficina Comandante','Escritorio de la comandancia general','2020-01-15',5500.00);

-- Armas
INSERT INTO `armas` (`activo_id`,`tipo`,`calibre`,`numero_serie`,`estado`,`oficial_asignado_id`,`municiones_asignadas`) VALUES
(1,'pistola','9mm',         'GBB-MX-2021-001','operativa',   1,45),
(2,'pistola','9mm',         'GBB-MX-2021-002','operativa',   2,45),
(3,'rifle',  '5.56x45mm',   'CAR15-QRO-001',  'operativa',   NULL,180),
(4,'pistola','9mm',         'BER-M9-2022-001', 'operativa',   NULL,30),
(5,'escopeta','12 gauge',   'MB500-QRO-001',   'mantenimiento',NULL,0);

-- Vehiculos
INSERT INTO `vehiculos` (`activo_id`,`tipo`,`placas`,`año`,`color`,`estado`,`kilometraje`,`responsable_id`) VALUES
(6,'patrulla','QRO-123-A',2022,'Blanco y Azul','operativo',32450,3),
(7,'patrulla','QRO-456-B',2021,'Blanco y Azul','operativo',58900,3),
(8,'moto',    'QRO-789-C',2023,'Azul',          'operativo',12300,3);

-- Suministros
INSERT INTO `suministros` (`nombre`,`categoria`,`unidad`,`stock_actual`,`stock_minimo`,`stock_maximo`,`ubicacion`,`proveedor`,`precio_unitario`) VALUES
('Munición 9mm Bala FMJ',            'municion',   'cartucho',  450,  200, 1000, 'Armería Almacén A', 'Distribuidora Seguridad Nacional', 8.50),
('Munición 5.56x45mm',               'municion',   'cartucho',  360,  150, 800,  'Armería Almacén A', 'Distribuidora Seguridad Nacional', 12.00),
('Munición 12 Gauge Perdigón',       'municion',   'cartucho',  80,   100, 500,  'Armería Almacén A', 'Distribuidora Seguridad Nacional', 15.00),
('Aceite lubricante para armas',     'limpieza',   'frasco',    25,   10,  50,   'Armería Almacén B', 'Ferretería Industrial Querétaro',  85.00),
('Parches limpieza calibre 9mm',     'limpieza',   'paquete',   8,    20,  100,  'Armería Almacén B', 'Ferretería Industrial Querétaro',  45.00),
('Uniforme Operativo Talla M',       'uniforme',   'juego',     12,   10,  50,   'Almacén Equipamiento', 'Uniformes Tácticos México',      650.00),
('Uniforme Operativo Talla L',       'uniforme',   'juego',     7,    10,  50,   'Almacén Equipamiento', 'Uniformes Tácticos México',      650.00),
('Resma Papel Carta 500 hojas',      'papeleria',  'resma',     15,   5,   50,   'Almacén Oficina',   'Papelería Central Querétaro',      95.00),
('Gasolina Magna (litros)',           'otro',       'litro',     800,  200, 2000, 'Cisterna',          'Pemex Estación 2301',              21.50),
('Llave de tiro Allen set',          'herramienta','juego',     3,    2,   10,   'Armería Taller',    'Herramientas Industriales SA',    320.00);

-- Movimientos de almacén (ejemplo)
INSERT INTO `movimientos_almacen` (`suministro_id`,`tipo`,`cantidad`,`responsable_id`,`oficial_id`,`motivo`,`fecha`) VALUES
(1,'entrada',200,4,NULL,'Compra mensual de munición',DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1,'salida', 45, 4,1,   'Asignación turno matutino - Inspector Martínez',DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2,'entrada',150,4,NULL,'Compra trimestral',DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2,'salida', 60, 4,3,   'Asignación operativo especial',DATE_SUB(NOW(), INTERVAL 3 DAY)),
(9,'entrada',500,4,NULL,'Abastecimiento mensual combustible',DATE_SUB(NOW(), INTERVAL 14 DAY)),
(9,'salida', 120,4,NULL,'Carga vehículos patrulla semana 1',DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Bitácora de ejemplo
INSERT INTO `bitacora` (`tipo`,`activo_id`,`activo_tipo`,`responsable_id`,`oficial_id`,`descripcion`,`estado_anterior`,`estado_nuevo`,`turno`,`fecha`) VALUES
('asignacion',1,'arma',5,1,'Asignación de arma al turno matutino. Inspector Martínez recibe Glock 17 #001','En armería','Asignada a oficial','matutino',DATE_SUB(NOW(), INTERVAL 1 DAY)),
('salida',     6,'vehiculo',5,3,'Salida de patrulla QRO-123-A. Turno nocturno sector norte','En estacionamiento','En patrulla','nocturno',DATE_SUB(NOW(), INTERVAL 2 DAY)),
('entrada',    6,'vehiculo',5,3,'Regreso de patrulla QRO-123-A. Sin novedades','En patrulla','En estacionamiento','nocturno',DATE_SUB(NOW(), INTERVAL 2 DAY)),
('devolucion', 1,'arma',5,1,'Devolución de arma al terminar turno matutino','Asignada a oficial','En armería','matutino',DATE_SUB(NOW(), INTERVAL 1 DAY)),
('incidencia', 5,'arma',2,NULL,'Arma enviada a mantenimiento preventivo por desgaste de mecanismo','Operativa','En mantenimiento','matutino',DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Mantenimiento de vehículo
INSERT INTO `mantenimientos` (`vehiculo_id`,`tipo`,`descripcion`,`fecha_inicio`,`fecha_fin`,`costo`,`proveedor`,`estado`) VALUES
(1,'preventivo','Cambio de aceite y filtros. Revisión de frenos.','2024-01-15','2024-01-15',2800.00,'Taller Automotriz Querétaro','completado'),
(2,'correctivo','Reparación de sistema de frenos delanteros.','2024-02-20','2024-02-22',8500.00,'Mecánica Express Querétaro','completado');

-- Combustible
INSERT INTO `combustible` (`vehiculo_id`,`litros`,`costo`,`kilometraje`,`responsable_id`,`fecha`) VALUES
(1,45.00,967.50,32000,4,DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1,50.00,1075.00,32200,4,DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2,40.00,860.00,58700,4,DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Configuraciones iniciales
INSERT INTO `configuraciones` (`clave`,`valor`,`descripcion`) VALUES
('app_nombre',         'IDGuns - Control de Armas',                       'Nombre del sistema'),
('app_telefono',       '+52 442 215 0000',                                 'Teléfono de contacto'),
('app_horario',        '24/7 — 365 días',                                  'Horario de operación'),
('app_direccion',      'Av. 5 de Febrero 101, Centro, Querétaro, Qro.',   'Dirección física'),
('color_primario',     '#4f46e5',                                           'Color principal de la interfaz'),
('color_secundario',   '#111827',                                           'Color secundario (sidebar)'),
('traccar_url',        '',                                                  'URL base del servidor Traccar'),
('traccar_usuario',    '',                                                  'Usuario de Traccar'),
('traccar_password',   '',                                                  'Contraseña del usuario Traccar');

SET foreign_key_checks = 1;
