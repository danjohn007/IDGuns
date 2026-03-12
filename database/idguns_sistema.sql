-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-03-2026 a las 13:49:37
-- Versión del servidor: 5.7.23-23
-- Versión de PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `idguns_sistema`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activos`
--

CREATE TABLE `activos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `categoria` varchar(80) NOT NULL DEFAULT 'otro',
  `marca` varchar(80) DEFAULT NULL,
  `modelo` varchar(80) DEFAULT NULL,
  `serie` varchar(80) DEFAULT NULL,
  `color` varchar(40) DEFAULT NULL,
  `estado` enum('activo','baja','mantenimiento') NOT NULL DEFAULT 'activo',
  `responsable_id` int(10) UNSIGNED DEFAULT NULL,
  `personal_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Responsable del activo (tabla personal)',
  `ubicacion` varchar(150) DEFAULT NULL,
  `descripcion` text,
  `fecha_adquisicion` date DEFAULT NULL,
  `valor` decimal(12,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `activos`
--

INSERT INTO `activos` (`id`, `codigo`, `nombre`, `categoria`, `marca`, `modelo`, `serie`, `color`, `estado`, `responsable_id`, `personal_id`, `ubicacion`, `descripcion`, `fecha_adquisicion`, `valor`, `imagen`, `created_at`) VALUES
(1, 'ARM-0001', 'Pistola Glock 17 #001', 'arma', 'Glock', '17', 'GBB-MX-2021-001', NULL, 'activo', 1, NULL, 'Armería Principal', 'Pistola semiautomática calibre 9mm asignada a turno matutino', '2021-03-15', 12500.00, NULL, '2026-02-24 09:07:18'),
(2, 'ARM-0002', 'Pistola Glock 17 #002', 'arma', 'Glock', '17', 'GBB-MX-2021-002', NULL, 'activo', 2, NULL, 'Armería Principal', 'Pistola semiautomática calibre 9mm', '2021-03-15', 12500.00, NULL, '2026-02-24 09:07:18'),
(3, 'ARM-0003', 'Rifle Colt AR-15 #001', 'arma', 'Colt', 'AR-15', 'CAR15-QRO-001', NULL, 'activo', 1, NULL, 'Armería Seguridad', 'Rifle de asalto calibre 5.56x45mm para operaciones especiales', '2020-08-10', 45000.00, NULL, '2026-02-24 09:07:18'),
(4, 'ARM-0004', 'Pistola Beretta M9 #001', 'arma', 'Beretta', 'M9', 'BER-M9-2022-001', NULL, 'activo', 2, NULL, 'Armería Principal', 'Pistola semiautomática calibre 9mm para supervisores', '2022-01-20', 15800.00, NULL, '2026-02-24 09:07:18'),
(5, 'ARM-0005', 'Escopeta Mossberg 500', 'arma', 'Mossberg', '500', 'MB500-QRO-001', NULL, 'mantenimiento', 1, NULL, 'Taller Armería', 'Escopeta calibre 12 en mantenimiento preventivo', '2019-11-05', 8900.00, NULL, '2026-02-24 09:07:18'),
(6, 'VEH-0001', 'Patrulla Ford Explorer #01', 'vehiculo', 'Ford', 'Explorer', '1FMSK8DH5MGA12345', NULL, 'activo', 3, NULL, 'Estacionamiento Norte', 'Unidad patrulla sector norte Querétaro', '2022-06-01', 320000.00, NULL, '2026-02-24 09:07:18'),
(7, 'VEH-0002', 'Patrulla Dodge Charger #02', 'vehiculo', 'Dodge', 'Charger', '2B3HADGT4NH123456', NULL, 'activo', 3, NULL, 'Estacionamiento Sur', 'Unidad patrulla sector sur Querétaro', '2021-11-15', 380000.00, NULL, '2026-02-24 09:07:18'),
(8, 'VEH-0003', 'Motocicleta Honda CB500 #01', 'vehiculo', 'Honda', 'CB500F', 'ML8JC7113M5012345', NULL, 'activo', 3, NULL, 'Estacionamiento Norte', 'Moto de patrullaje urbano', '2023-02-10', 95000.00, NULL, '2026-02-24 09:07:18'),
(9, 'EQC-0001', 'Laptop Dell Latitude 5420', 'equipo_computo', 'Dell', 'Latitude 5420', 'DL5420-QRO-001', NULL, 'activo', 2, NULL, 'Oficina Comando', 'Equipo para oficina de comandancia', '2022-09-01', 28000.00, NULL, '2026-02-24 09:07:18'),
(10, 'EQO-0001', 'Escritorio Ejecutivo', 'equipo_oficina', 'Muebles Querétaro', 'EJ-2020', 'SIN-SERIE-001', NULL, 'activo', 1, NULL, 'Oficina Comandante', 'Escritorio de la comandancia general', '2020-01-15', 5500.00, NULL, '2026-02-24 09:07:18'),
(11, 'EQC-0002', 'Iphone Dan', 'equipo_computo', 'Apple', 'Iphone 12 Pro Max', '', NULL, 'activo', NULL, NULL, '', '', '2026-02-01', NULL, NULL, '2026-02-25 17:18:50'),
(12, 'EQC-0003', 'Elias Iphone', 'equipo_computo', 'Apple', '13 Pro Max', '1234567890', NULL, 'activo', 1, NULL, '', '', '2026-02-23', NULL, NULL, '2026-02-25 17:28:06'),
(13, 'EQC-0004', 'iPhone Andrés', 'equipo_computo', 'Apple', 'Iphone 8 ProMax', '', NULL, 'activo', NULL, NULL, '', '', '2026-02-23', NULL, NULL, '2026-02-26 07:13:34'),
(14, 'EQC-0005', 'Santiago Cel', 'equipo_computo', '', '', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-26 10:34:51'),
(15, 'EQC-0006', 'Dan Raso B', 'equipo_computo', 'Apple', 'iPhone 15', '', NULL, 'baja', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-26 10:36:17'),
(16, 'EQC-0007', 'Emilio Nubia', 'equipo_computo', '', 'Android Nubia', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-26 10:44:51'),
(17, 'ACT-0001', 'Movil Fran', '', '', '', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', 20000.00, NULL, '2026-02-27 09:37:24'),
(18, 'ARM-0006', 'Pistola  Revólver', 'arma', 'Taurus', 'Mod 82', 'MX-9F3A-7421-KQ', NULL, 'activo', NULL, 5, 'Armería Principal', '', '2026-02-10', 8200.00, NULL, '2026-02-27 09:39:04'),
(25, 'ACT-0002', 'iPhone Ian', '', '', '', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-28 03:00:24'),
(26, 'ACT-0003', 'iPhone Eva', '', 'Apple', '11 Pro', '', NULL, 'activo', NULL, 3, '', '', '2026-02-20', NULL, NULL, '2026-02-28 04:14:21'),
(27, 'VEH-0004', 'Malibu 2017', 'vehiculo', 'Chevrolet', 'Malibú', '', NULL, 'activo', NULL, 3, '', '', '0000-00-00', NULL, NULL, '2026-02-28 04:49:35'),
(28, 'VEH-0005', 'Arona ID', 'vehiculo', 'SEAT', 'Arona', '', '', 'activo', NULL, 3, '', '', '0000-00-00', NULL, NULL, '2026-02-28 04:51:37'),
(29, 'VEH-0006', 'Trax ID', 'vehiculo', '', '', '', '', 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-28 13:09:54'),
(31, 'EQO-0002', 'Recepción', 'equipo_oficina', '', '', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-28 15:37:34'),
(32, 'ACT-0004', 'Rocky', 'animal', 'Pastor', 'Aleman', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-28 15:39:35'),
(33, 'ACT-0005', 'Viri Dunas', 'movil', '', '', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-02-28 17:38:16'),
(34, 'ACT-0006', 'GPS Auto 1', 'Tracker', 'Jimi IoT', 'VL103', '', NULL, 'activo', NULL, 3, 'ID', '', '2026-03-04', 1000.00, NULL, '2026-03-04 21:42:25'),
(35, 'ACT-0007', 'Huawei Chelita', 'movil', '', '', '', NULL, 'activo', NULL, NULL, '', '', '0000-00-00', NULL, NULL, '2026-03-11 08:39:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas_reglas`
--

CREATE TABLE `alertas_reglas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL COMMENT 'Nombre descriptivo de la regla',
  `tipo` varchar(60) NOT NULL DEFAULT 'geofenceExit' COMMENT 'Tipo Traccar: geofenceExit, geofenceEnter, speeding, deviceOffline, etc.',
  `activo_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Activo al que aplica (NULL = todos)',
  `geozona_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Geozona asociada (para reglas de perímetro)',
  `notificar_email` tinyint(1) NOT NULL DEFAULT '1',
  `notificar_whatsapp` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Reglas de alertas y notificaciones del sistema';

--
-- Volcado de datos para la tabla `alertas_reglas`
--

INSERT INTO `alertas_reglas` (`id`, `nombre`, `tipo`, `activo_id`, `geozona_id`, `notificar_email`, `notificar_whatsapp`, `activo`, `created_at`) VALUES
(1, 'Salida de la ciudad', 'geofenceExit', NULL, 10, 1, 1, 1, '2026-02-26 22:53:06'),
(2, 'Llegada a casa', 'geofenceEnter', 15, 3, 1, 1, 1, '2026-02-27 00:10:42'),
(3, 'Máxima velocidad', 'speeding', 12, NULL, 1, 1, 1, '2026-02-27 00:11:17'),
(4, 'Salida de Casa', 'geofenceExit', 15, 3, 1, 1, 1, '2026-02-27 09:08:03'),
(5, 'Apagado de automóvil', 'ignitionOff', NULL, NULL, 1, 0, 1, '2026-02-28 13:34:40'),
(6, 'Dispositivo encontrado sin señal', 'deviceOffline', NULL, NULL, 1, 1, 1, '2026-03-11 21:54:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `armas`
--

CREATE TABLE `armas` (
  `id` int(10) UNSIGNED NOT NULL,
  `activo_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('pistola','rifle','escopeta','otro') NOT NULL DEFAULT 'pistola',
  `calibre` varchar(30) DEFAULT NULL,
  `numero_serie` varchar(80) DEFAULT NULL,
  `estado` enum('operativa','mantenimiento','baja') NOT NULL DEFAULT 'operativa',
  `oficial_asignado_id` int(10) UNSIGNED DEFAULT NULL,
  `municiones_asignadas` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `armas`
--

INSERT INTO `armas` (`id`, `activo_id`, `tipo`, `calibre`, `numero_serie`, `estado`, `oficial_asignado_id`, `municiones_asignadas`, `created_at`) VALUES
(1, 1, 'pistola', '9mm', 'GBB-MX-2021-001', 'operativa', 1, 45, '2026-02-24 09:07:18'),
(2, 2, 'pistola', '9mm', 'GBB-MX-2021-002', 'operativa', 2, 45, '2026-02-24 09:07:18'),
(3, 3, 'rifle', '5.56x45mm', 'CAR15-QRO-001', 'operativa', NULL, 180, '2026-02-24 09:07:18'),
(4, 4, 'pistola', '9mm', 'BER-M9-2022-001', 'operativa', NULL, 30, '2026-02-24 09:07:18'),
(5, 5, 'escopeta', '12 gauge', 'MB500-QRO-001', 'mantenimiento', NULL, 0, '2026-02-24 09:07:18'),
(6, 18, 'pistola', '.22 LR', 'MX-9F3A-7421-KQ', 'operativa', 2, 0, '2026-02-27 09:39:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('entrada','salida','asignacion','devolucion','incidencia') NOT NULL,
  `activo_id` int(10) UNSIGNED DEFAULT NULL,
  `activo_tipo` varchar(60) DEFAULT NULL,
  `responsable_id` int(10) UNSIGNED DEFAULT NULL,
  `oficial_id` int(10) UNSIGNED DEFAULT NULL,
  `descripcion` text,
  `estado_anterior` varchar(80) DEFAULT NULL,
  `estado_nuevo` varchar(80) DEFAULT NULL,
  `turno` enum('matutino','vespertino','nocturno') NOT NULL DEFAULT 'matutino',
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id`, `tipo`, `activo_id`, `activo_tipo`, `responsable_id`, `oficial_id`, `descripcion`, `estado_anterior`, `estado_nuevo`, `turno`, `fecha`, `created_at`) VALUES
(1, 'asignacion', 1, 'arma', 5, 1, 'Asignación de arma al turno matutino. Inspector Martínez recibe Glock 17 #001', 'En armería', 'Asignada a oficial', 'matutino', '2026-02-23 09:07:19', '2026-02-24 09:07:19'),
(2, 'salida', 6, 'vehiculo', 5, 3, 'Salida de patrulla QRO-123-A. Turno nocturno sector norte', 'En estacionamiento', 'En patrulla', 'nocturno', '2026-02-22 09:07:19', '2026-02-24 09:07:19'),
(3, 'entrada', 6, 'vehiculo', 5, 3, 'Regreso de patrulla QRO-123-A. Sin novedades', 'En patrulla', 'En estacionamiento', 'nocturno', '2026-02-22 09:07:19', '2026-02-24 09:07:19'),
(4, 'devolucion', 1, 'arma', 5, 1, 'Devolución de arma al terminar turno matutino', 'Asignada a oficial', 'En armería', 'matutino', '2026-02-23 09:07:19', '2026-02-24 09:07:19'),
(5, 'incidencia', 5, 'arma', 2, NULL, 'Arma enviada a mantenimiento preventivo por desgaste de mecanismo', 'Operativa', 'En mantenimiento', 'matutino', '2026-02-21 09:07:19', '2026-02-24 09:07:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogos`
--

CREATE TABLE `catalogos` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(60) NOT NULL COMMENT 'activos_categoria | suministros_categoria | vehiculos_tipo',
  `clave` varchar(60) NOT NULL COMMENT 'machine-readable key',
  `etiqueta` varchar(120) NOT NULL COMMENT 'human-readable label',
  `orden` smallint(6) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Dynamic catalog entries for categories and types';

--
-- Volcado de datos para la tabla `catalogos`
--

INSERT INTO `catalogos` (`id`, `tipo`, `clave`, `etiqueta`, `orden`, `created_at`) VALUES
(6, 'suministros_categoria', 'limpieza', 'Limpieza', 1, '2026-02-25 20:49:36'),
(8, 'suministros_categoria', 'uniforme', 'Uniforme', 3, '2026-02-25 20:49:36'),
(9, 'suministros_categoria', 'municion', 'Munición', 4, '2026-02-25 20:49:36'),
(10, 'suministros_categoria', 'herramienta', 'Herramienta', 5, '2026-02-25 20:49:36'),
(15, 'vehiculos_tipo', 'otro', 'Otro', 6, '2026-02-25 20:49:36'),
(19, 'personal_cargo', 'comandante', 'Comandante', 1, '2026-02-26 21:15:03'),
(20, 'personal_cargo', 'subcomandante', 'Subcomandante', 2, '2026-02-26 21:15:03'),
(21, 'personal_cargo', 'teniente', 'Teniente', 3, '2026-02-26 21:15:03'),
(22, 'personal_cargo', 'sargento', 'Sargento', 4, '2026-02-26 21:15:03'),
(23, 'personal_cargo', 'cabo', 'Cabo', 5, '2026-02-26 21:15:03'),
(24, 'personal_cargo', 'policia', 'Policía', 6, '2026-02-26 21:15:03'),
(25, 'personal_cargo', 'administrativo', 'Administrativo', 7, '2026-02-26 21:15:03'),
(26, 'personal_cargo', 'otro', 'Otro', 10, '2026-02-26 21:15:03'),
(28, 'vehiculos_tipo', 'grua', 'Grúa', 5, '2026-02-26 22:24:33'),
(29, 'personal_cargo', 'director', 'Director', 8, '2026-02-26 22:33:26'),
(31, 'suministros_categoria', 'papeleria', 'Papelería', 2, '2026-02-27 00:07:18'),
(35, 'suministros_categoria', 'otro', 'Otro', 8, '2026-02-27 00:07:18'),
(52, 'suministros_categoria', 'tonner', 'Tonner', 6, '2026-02-27 20:01:01'),
(53, 'suministros_categoria', 'equipo', 'Equipo', 7, '2026-02-27 20:01:01'),
(54, 'vehiculos_tipo', 'auto', 'Automóvil', 4, '2026-02-28 04:43:12'),
(60, 'vehiculos_tipo', 'patrulla', 'Patrulla', 1, '2026-02-28 07:33:31'),
(61, 'vehiculos_tipo', 'moto', 'Motocicleta', 2, '2026-02-28 07:33:31'),
(62, 'vehiculos_tipo', 'camioneta', 'Camioneta', 3, '2026-02-28 07:33:31'),
(64, 'vehiculos_tipo', 'truck', 'Camión', 7, '2026-02-28 13:15:17'),
(65, 'activos_categoria', 'animal', 'Canino', 7, '2026-02-28 15:39:10'),
(66, 'activos_categoria', 'arma', 'Arma', 1, '2026-02-28 17:30:36'),
(67, 'activos_categoria', 'vehiculo', 'Vehículo', 2, '2026-02-28 17:30:36'),
(69, 'activos_categoria', 'equipo_oficina', 'Equipo de Oficina', 4, '2026-02-28 17:30:36'),
(70, 'activos_categoria', 'bien_mueble', 'Bien Mueble', 5, '2026-02-28 17:30:36'),
(71, 'activos_categoria', 'movil', 'Móvil / Celular', 6, '2026-02-28 17:30:36'),
(72, 'activos_categoria', 'computo', 'Equipo de Cómputo', 3, '2026-02-28 17:36:26'),
(73, 'activos_categoria', 'Tracker', 'GPS', 8, '2026-03-04 21:39:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combustible`
--

CREATE TABLE `combustible` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehiculo_id` int(10) UNSIGNED NOT NULL,
  `litros` decimal(8,2) NOT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `kilometraje` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `responsable_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `combustible`
--

INSERT INTO `combustible` (`id`, `vehiculo_id`, `litros`, `costo`, `kilometraje`, `responsable_id`, `fecha`, `created_at`) VALUES
(1, 1, 45.00, 967.50, 32000, 4, '2026-02-17 09:07:19', '2026-02-24 09:07:19'),
(2, 1, 50.00, 1075.00, 32200, 4, '2026-02-21 09:07:19', '2026-02-24 09:07:19'),
(3, 2, 40.00, 860.00, 58700, 4, '2026-02-19 09:07:19', '2026-02-24 09:07:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `clave` varchar(80) NOT NULL,
  `valor` text,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `descripcion`, `created_at`) VALUES
(1, 'app_nombre', 'ID Guns - Control de Armas', 'Nombre del sistema', '2026-02-24 09:07:19'),
(2, 'app_telefono', '+52 442 215 0000', 'Teléfono de contacto', '2026-02-24 09:07:19'),
(3, 'app_horario', '24/7 — 365 días', 'Horario de operación', '2026-02-24 09:07:19'),
(4, 'app_direccion', 'Av. 5 de Febrero 101, Centro, Querétaro, Qro.', 'Dirección física', '2026-02-24 09:07:19'),
(5, 'color_primario', '#30076e', 'Color principal de la interfaz', '2026-02-24 09:07:19'),
(6, 'color_secundario', '#111827', 'Color secundario (sidebar)', '2026-02-24 09:07:19'),
(7, 'logo_url', '', '', '2026-02-24 23:51:43'),
(8, 'gps_api_key', '335cf360702f47a5bff4796f3d1f95e0', '', '2026-02-25 00:03:23'),
(9, 'gps_api_url', 'https://maps.geoapify.com/v1/tile/carto/{z}/{x}/{y}.png?&amp;apiKey=335cf360702f47a5bff4796f3d1f95e0', '', '2026-02-25 00:03:23'),
(10, 'gps_intervalo', '30', '', '2026-02-25 00:03:23'),
(11, 'traccar_url', 'https://demo4.traccar.org/', 'URL base del servidor Traccar (ej: http://demo4.traccar.org)', '2026-02-25 16:01:07'),
(12, 'traccar_usuario', 'dan@impactosdigitales.com', 'Usuario administrador de Traccar', '2026-02-25 16:01:07'),
(13, 'traccar_password', 'Danjohn007', 'Contraseña del usuario Traccar', '2026-02-25 16:01:07'),
(14, 'pyspark_url', '', 'URL del API REST del servicio PySpark', '2026-02-25 20:49:36'),
(15, 'pyspark_token', '', 'Token de autenticación del API PySpark', '2026-02-25 20:49:36'),
(18, 'app_timezone', 'America/Mexico_City', 'Zona horaria del sistema', '2026-02-27 01:59:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos_gps`
--

CREATE TABLE `dispositivos_gps` (
  `id` int(10) UNSIGNED NOT NULL,
  `activo_id` int(10) UNSIGNED NOT NULL COMMENT 'Activo al que pertenece el GPS',
  `traccar_device_id` int(11) DEFAULT NULL COMMENT 'ID del dispositivo en el servidor Traccar',
  `nombre` varchar(150) NOT NULL COMMENT 'Nombre del dispositivo en Traccar',
  `unique_id` varchar(100) NOT NULL COMMENT 'Identificador único: IMEI, número de teléfono, etc.',
  `telefono` varchar(30) DEFAULT NULL COMMENT 'Número de teléfono del dispositivo',
  `modelo_dispositivo` varchar(80) DEFAULT NULL COMMENT 'Modelo del dispositivo GPS (hardware)',
  `contacto` varchar(150) DEFAULT NULL COMMENT 'Información de contacto del responsable',
  `categoria_traccar` varchar(50) NOT NULL DEFAULT 'car' COMMENT 'Categoría Traccar: car, motorcycle, truck, van, etc.',
  `grupo_id` int(11) DEFAULT NULL COMMENT 'ID de grupo en Traccar',
  `activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=habilitado, 0=deshabilitado',
  `km_por_litro` decimal(8,2) DEFAULT NULL COMMENT 'km por litro de gasolina (valor individual, sobrescribe el global del reporte)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Dispositivos GPS Traccar vinculados a activos';

--
-- Volcado de datos para la tabla `dispositivos_gps`
--

INSERT INTO `dispositivos_gps` (`id`, `activo_id`, `traccar_device_id`, `nombre`, `unique_id`, `telefono`, `modelo_dispositivo`, `contacto`, `categoria_traccar`, `grupo_id`, `activo`, `km_por_litro`, `created_at`) VALUES
(1, 11, 74462429, 'Iphone 12 Pro Max', '35 671411 499190 0', '+52 442 598 6318', '', 'Dan Raso', 'person', NULL, 1, 12.00, '2026-02-25 17:18:50'),
(2, 12, 56490555, 'Iphone Pechan', '836129870923170213231', '+52 442 123 2732', '', 'Elias Raso', 'car', NULL, 1, NULL, '2026-02-25 21:23:33'),
(3, 13, 28282563, 'iPhone Andy', '9896982681291321', '', '', '', 'person', NULL, 1, 15.00, '2026-02-26 07:14:47'),
(4, 14, 92170178, 'Santi Móvil', '0808300123', '', '', '', 'car', NULL, 1, NULL, '2026-02-26 10:34:51'),
(5, 15, 28778606, 'iPhone Dan B', '97270217004432122', '', '', '', 'car', NULL, 1, 10.00, '2026-02-26 10:36:17'),
(6, 16, 95425346, 'Emilio Cel', '12398362189162092713 21', '', '', '', 'car', NULL, 1, 20.00, '2026-02-26 10:44:51'),
(7, 18, 53307005, 'Galaxy A51 Paco', '352838657757545', '442 160 7091', '', 'Francisco Luna', 'person', NULL, 1, 15.00, '2026-02-27 10:25:04'),
(8, 25, 61995197, 'Ian Alejandro', '35 392510 466832 3', '', '', '', 'car', NULL, 1, 15.00, '2026-02-28 03:32:09'),
(9, 34, 2147483647, 'GPS Syscom 1', '869066065126097', '4424865389', 'Jimi IoT VL103', 'Dan Jonathan', 'car', NULL, 1, 15.00, '2026-03-04 21:45:10'),
(10, 35, 31393761, 'Celular Chelita', '31393761', '4423748999', '', '', 'person', NULL, 1, NULL, '2026-03-11 08:39:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos_iot`
--

CREATE TABLE `dispositivos_iot` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('hikvision','shelly') NOT NULL DEFAULT 'hikvision',
  `ip` varchar(45) DEFAULT NULL,
  `puerto` smallint(5) UNSIGNED NOT NULL DEFAULT '80',
  `usuario` varchar(80) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `errores_sistema`
--

CREATE TABLE `errores_sistema` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(60) NOT NULL DEFAULT 'PHP_ERROR',
  `mensaje` text,
  `archivo` varchar(255) DEFAULT NULL,
  `linea` int(10) UNSIGNED DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `geozonas`
--

CREATE TABLE `geozonas` (
  `id` int(10) UNSIGNED NOT NULL,
  `traccar_id` int(11) DEFAULT NULL COMMENT 'ID de la geozona en el servidor Traccar',
  `nombre` varchar(150) NOT NULL,
  `descripcion` text,
  `area` text COMMENT 'Definición WKT o JSON de la geozona',
  `activo_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Activo asociado (opcional)',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Geozonas registradas (espejo local de Traccar)';

--
-- Volcado de datos para la tabla `geozonas`
--

INSERT INTO `geozonas` (`id`, `traccar_id`, `nombre`, `descripcion`, `area`, `activo_id`, `activo`, `created_at`) VALUES
(3, 2545, 'CASA', 'Del Sol', 'CIRCLE(20.6145675 -100.4431792, 100)', NULL, 1, '2026-02-26 21:40:49'),
(10, 2553, 'CASA GRANDE', '', 'CIRCLE(20.6145675 -100.4431792,10000)', NULL, 1, '2026-02-26 22:45:10'),
(13, 2546, 'Geo-Zona DUNAS', '', 'POLYGON ((20.726163760425322 -100.25354183995621, 20.716180460361088 -100.10579468055906, 20.50637958288938 -100.07602053817149, 20.457963535689956 -100.28275420607308, 20.509010451803178 -100.25859782639947, 20.726163760425322 -100.25354183995621))', NULL, 1, '2026-02-27 19:56:19'),
(14, 2538, 'Oficina', '', 'POLYGON ((20.62297715544169 -100.39458236786007, 20.615445983191094 -100.39460382553195, 20.614702887342474 -100.38617096038914, 20.622334509946256 -100.38599929901221, 20.62297715544169 -100.39458236786007))', NULL, 1, '2026-02-27 19:56:19'),
(15, 2537, 'Querétaro', '', 'POLYGON ((20.768361290519707 -100.5154750455143, 20.76750107635047 -100.28869720024646, 20.512232294601972 -100.27259735321917, 20.51567890150119 -100.52605494498926, 20.768361290519707 -100.5154750455143))', NULL, 1, '2026-02-27 19:56:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gps_km_reportes`
--

CREATE TABLE `gps_km_reportes` (
  `id` int(10) UNSIGNED NOT NULL,
  `dispositivo_id` int(10) UNSIGNED NOT NULL COMMENT 'FK a dispositivos_gps.id',
  `traccar_device_id` int(11) NOT NULL COMMENT 'ID del dispositivo en el servidor Traccar',
  `fecha_desde` date NOT NULL COMMENT 'Inicio del período consultado',
  `fecha_hasta` date NOT NULL COMMENT 'Fin del período consultado',
  `distancia_m` decimal(14,2) DEFAULT NULL COMMENT 'Distancia total en metros (campo distance de Traccar)',
  `engine_hours_ms` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Horas de motor en milisegundos (campo engineHours de Traccar)',
  `velocidad_max` decimal(8,4) DEFAULT NULL COMMENT 'Velocidad máxima en nudos (campo maxSpeed de Traccar)',
  `consultado_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora en que se consultó el API de Traccar',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Caché de resúmenes de kilometraje de Traccar por dispositivo y período';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehiculo_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('preventivo','correctivo','accidente') NOT NULL,
  `descripcion` text,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `proveedor` varchar(120) DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','completado') NOT NULL DEFAULT 'pendiente',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `mantenimientos`
--

INSERT INTO `mantenimientos` (`id`, `vehiculo_id`, `tipo`, `descripcion`, `fecha_inicio`, `fecha_fin`, `costo`, `proveedor`, `estado`, `created_at`) VALUES
(1, 1, 'preventivo', 'Cambio de aceite y filtros. Revisión de frenos.', '2024-01-15', '2024-01-15', 2800.00, 'Taller Automotriz Querétaro', 'completado', '2026-02-24 09:07:19'),
(2, 2, 'correctivo', 'Reparación de sistema de frenos delanteros.', '2024-02-20', '2024-02-22', 8500.00, 'Mecánica Express Querétaro', 'completado', '2026-02-24 09:07:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_almacen`
--

CREATE TABLE `movimientos_almacen` (
  `id` int(10) UNSIGNED NOT NULL,
  `suministro_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `responsable_id` int(10) UNSIGNED DEFAULT NULL,
  `oficial_id` int(10) UNSIGNED DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notas` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `movimientos_almacen`
--

INSERT INTO `movimientos_almacen` (`id`, `suministro_id`, `tipo`, `cantidad`, `responsable_id`, `oficial_id`, `motivo`, `fecha`, `notas`, `created_at`) VALUES
(1, 1, 'entrada', 200, 4, NULL, 'Compra mensual de munición', '2026-02-17 09:07:19', NULL, '2026-02-24 09:07:19'),
(2, 1, 'salida', 45, 4, 1, 'Asignación turno matutino - Inspector Martínez', '2026-02-19 09:07:19', NULL, '2026-02-24 09:07:19'),
(3, 2, 'entrada', 150, 4, NULL, 'Compra trimestral', '2026-02-14 09:07:19', NULL, '2026-02-24 09:07:19'),
(4, 2, 'salida', 60, 4, 3, 'Asignación operativo especial', '2026-02-21 09:07:19', NULL, '2026-02-24 09:07:19'),
(5, 9, 'entrada', 500, 4, NULL, 'Abastecimiento mensual combustible', '2026-02-10 09:07:19', NULL, '2026-02-24 09:07:19'),
(6, 9, 'salida', 120, 4, NULL, 'Carga vehículos patrulla semana 1', '2026-02-17 09:07:19', NULL, '2026-02-24 09:07:19'),
(7, 11, 'entrada', 40, 1, 3, 'probando', '2026-02-27 00:22:33', '', '2026-02-27 00:22:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Usuario destinatario',
  `tipo` varchar(60) NOT NULL DEFAULT 'sistema' COMMENT 'Tipo de notificación',
  `mensaje` text NOT NULL COMMENT 'Contenido de la notificación',
  `url` varchar(255) DEFAULT NULL COMMENT 'URL de destino al hacer clic',
  `leido` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0=no leída, 1=leída',
  `leido_at` datetime DEFAULT NULL COMMENT 'Fecha de lectura',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Notificaciones internas del sistema';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oficiales`
--

CREATE TABLE `oficiales` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `placa` varchar(30) NOT NULL,
  `rango` varchar(60) NOT NULL DEFAULT 'Policía Municipal',
  `turno` enum('matutino','vespertino','nocturno') NOT NULL DEFAULT 'matutino',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `oficiales`
--

INSERT INTO `oficiales` (`id`, `nombre`, `apellidos`, `placa`, `rango`, `turno`, `activo`, `created_at`) VALUES
(1, 'Carlos Alberto', 'Martínez Reyes', 'QRO-001', 'Inspector', 'matutino', 1, '2026-02-24 09:07:18'),
(2, 'María Guadalupe', 'Sánchez Domínguez', 'QRO-002', 'Subinspector', 'vespertino', 1, '2026-02-24 09:07:18'),
(3, 'José Luis', 'Ramírez Flores', 'QRO-003', 'Oficial de Policía', 'nocturno', 1, '2026-02-24 09:07:18'),
(4, 'Ana Patricia', 'González Moreno', 'QRO-004', 'Oficial de Policía', 'matutino', 1, '2026-02-24 09:07:18'),
(5, 'Roberto', 'Jiménez Vargas', 'QRO-005', 'Oficial de Policía', 'vespertino', 1, '2026-02-24 09:07:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `apellidos` varchar(120) NOT NULL DEFAULT '',
  `cargo` varchar(100) NOT NULL DEFAULT '' COMMENT 'Cargo / rango del personal',
  `email` varchar(120) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `numero_empleado` varchar(40) DEFAULT NULL COMMENT 'Número de placa o empleado',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de personal del sistema';

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `nombre`, `apellidos`, `cargo`, `email`, `telefono`, `numero_empleado`, `activo`, `created_at`) VALUES
(2, 'Jonathan', 'Rios', 'Director', NULL, '4425986318', '9876', 0, '2026-02-26 22:47:28'),
(3, 'Dan', 'Raso', 'Comandante', 'dan@impactosdigitales.com', '4425986318', '1234', 1, '2026-02-26 23:15:42'),
(4, 'Jane', 'Rosas', 'Director', 'jane@impactosdigitales.com', '4421083970', 'J-001', 1, '2026-02-27 00:19:15'),
(5, 'Francisco', 'Luna Fernández', 'Administrativo', 'fco.lunafdezejemplo@gmail.com', '4421607091', '12345678', 1, '2026-02-27 09:31:47'),
(6, 'Fran', 'Sistos', 'Sargento', NULL, NULL, '752', 1, '2026-02-27 09:36:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suministros`
--

CREATE TABLE `suministros` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `categoria` varchar(60) NOT NULL DEFAULT 'otro',
  `unidad` varchar(30) NOT NULL DEFAULT 'pieza',
  `stock_actual` int(11) NOT NULL DEFAULT '0',
  `stock_minimo` int(11) NOT NULL DEFAULT '10',
  `stock_maximo` int(11) NOT NULL DEFAULT '100',
  `ubicacion` varchar(150) DEFAULT NULL,
  `proveedor` varchar(120) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `suministros`
--

INSERT INTO `suministros` (`id`, `nombre`, `categoria`, `unidad`, `stock_actual`, `stock_minimo`, `stock_maximo`, `ubicacion`, `proveedor`, `precio_unitario`, `created_at`) VALUES
(1, 'Munición 9mm Bala FMJ', 'municion', 'cartucho', 450, 200, 1000, 'Armería Almacén A', 'Distribuidora Seguridad Nacional', 8.50, '2026-02-24 09:07:19'),
(2, 'Munición 5.56x45mm', 'municion', 'cartucho', 360, 150, 800, 'Armería Almacén A', 'Distribuidora Seguridad Nacional', 12.00, '2026-02-24 09:07:19'),
(3, 'Munición 12 Gauge Perdigón', 'municion', 'cartucho', 80, 100, 500, 'Armería Almacén A', 'Distribuidora Seguridad Nacional', 15.00, '2026-02-24 09:07:19'),
(4, 'Aceite lubricante para armas', 'tonner', 'frasco', 25, 10, 50, 'Armería Almacén B', 'Ferretería Industrial Querétaro', 85.00, '2026-02-24 09:07:19'),
(5, 'Parches limpieza calibre 9mm', 'limpieza', 'paquete', 8, 20, 100, 'Armería Almacén B', 'Ferretería Industrial Querétaro', 45.00, '2026-02-24 09:07:19'),
(6, 'Uniforme Operativo Talla M', 'uniforme', 'juego', 12, 10, 50, 'Almacén Equipamiento', 'Uniformes Tácticos México', 650.00, '2026-02-24 09:07:19'),
(7, 'Uniforme Operativo Talla L', 'uniforme', 'juego', 7, 10, 50, 'Almacén Equipamiento', 'Uniformes Tácticos México', 650.00, '2026-02-24 09:07:19'),
(8, 'Resma Papel Carta 500 hojas', 'papeleria', 'resma', 15, 5, 50, 'Almacén Oficina', 'Papelería Central Querétaro', 95.00, '2026-02-24 09:07:19'),
(9, 'Gasolina Magna (litros)', 'otro', 'litro', 800, 200, 2000, 'Cisterna', 'Pemex Estación 2301', 21.50, '2026-02-24 09:07:19'),
(10, 'Llave de tiro Allen set', 'herramienta', 'juego', 3, 2, 10, 'Armería Taller', 'Herramientas Industriales SA', 320.00, '2026-02-24 09:07:19'),
(11, 'Cartucho HP', 'tonner', 'pieza', 40, 20, 100, 'Querétaro', 'ID', 300.00, '2026-02-27 00:21:12'),
(12, 'Guantes', 'equipo', 'pieza', 30, 10, 400, 'Querétaro', 'ID', 100.00, '2026-02-27 00:26:32'),
(13, 'Escobas', 'limpieza', 'pieza', 3, 20, 100, '', '', 49.00, '2026-02-27 21:28:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('superadmin','admin','almacen','bitacora') NOT NULL DEFAULT 'bitacora',
  `activo` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `nombre`, `username`, `email`, `password`, `rol`, `activo`, `created_at`) VALUES
(1, 'Directora Jane Rosas', 'superadmin', 'jane@ssc-qro.gob.mx', '$2y$10$Ihr8pDa/eENBwSnHzgoln.bHQb19Iteuf7CPsW9T87kansWMf0Ieu', 'superadmin', 1, '2026-02-24 09:07:18'),
(2, 'Subcomandante Laura Vega Torres', 'lvega', 'lvega@ssc-qro.gob.mx', '$2y$10$AefpUaWyalL08bnUzeHYX.ughiyC9xqB6iAj.82vm8t3gRGpoJoFO', 'admin', 1, '2026-02-24 09:07:18'),
(3, 'Tte. Samuel Herrera Castillo', 'sherrera', 'sherrera@ssc-qro.gob.mx', '$2y$10$AefpUaWyalL08bnUzeHYX.ughiyC9xqB6iAj.82vm8t3gRGpoJoFO', 'admin', 1, '2026-02-24 09:07:18'),
(4, 'Sargento Diana López Morales', 'dlopez', 'dlopez@ssc-qro.gob.mx', '$2y$10$P5c1ckzBeKHxzbJae4IqI.FuF9W9ttqxouP2QXadLdY/hNh9GB0Lu', 'almacen', 1, '2026-02-24 09:07:18'),
(5, 'Cabo Ernesto Salinas Pérez', 'esalinas', 'esalinas@ssc-qro.gob.mx', '$2y$10$uh/pKp6rY4Rc8t6iLWxhmOUJ9pZ3jFb.rCakmTLvVyvrB70wRaJeG', 'bitacora', 1, '2026-02-24 09:07:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(10) UNSIGNED NOT NULL,
  `activo_id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(80) NOT NULL DEFAULT 'otro',
  `placas` varchar(20) DEFAULT NULL,
  `anio` year(4) DEFAULT NULL,
  `color` varchar(40) DEFAULT NULL,
  `estado` enum('operativo','taller','baja') NOT NULL DEFAULT 'operativo',
  `kilometraje` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `responsable_id` int(10) UNSIGNED DEFAULT NULL,
  `personal_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Responsable del vehículo (tabla personal)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `activo_id`, `tipo`, `placas`, `anio`, `color`, `estado`, `kilometraje`, `responsable_id`, `personal_id`, `created_at`) VALUES
(1, 6, 'patrulla', 'QRO-123-A', '2022', 'Blanco y Azul', 'operativo', 32450, 3, NULL, '2026-02-24 09:07:19'),
(2, 7, 'patrulla', 'QRO-456-B', '2021', 'Blanco y Azul', 'operativo', 58900, 3, NULL, '2026-02-24 09:07:19'),
(3, 8, 'moto', 'QRO-789-C', '2023', 'Azul', 'operativo', 12300, 3, NULL, '2026-02-24 09:07:19'),
(4, 27, 'otro', '', '2017', '', 'operativo', 0, NULL, NULL, '2026-02-28 04:49:35'),
(5, 29, 'auto', 'TRAX 2026', '2026', '', 'operativo', 0, NULL, NULL, '2026-02-28 13:09:54');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `responsable_id` (`responsable_id`),
  ADD KEY `fk_activos_personal` (`personal_id`);

--
-- Indices de la tabla `alertas_reglas`
--
ALTER TABLE `alertas_reglas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_alertas_activo` (`activo_id`),
  ADD KEY `fk_alertas_geozona` (`geozona_id`);

--
-- Indices de la tabla `armas`
--
ALTER TABLE `armas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activo_id` (`activo_id`),
  ADD KEY `oficial_asignado_id` (`oficial_asignado_id`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activo_id` (`activo_id`),
  ADD KEY `responsable_id` (`responsable_id`),
  ADD KEY `oficial_id` (`oficial_id`);

--
-- Indices de la tabla `catalogos`
--
ALTER TABLE `catalogos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_catalogos_tipo` (`tipo`);

--
-- Indices de la tabla `combustible`
--
ALTER TABLE `combustible`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`),
  ADD KEY `responsable_id` (`responsable_id`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `dispositivos_gps`
--
ALTER TABLE `dispositivos_gps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gps_activo` (`activo_id`),
  ADD KEY `idx_unique_id` (`unique_id`),
  ADD KEY `idx_traccar_device_id` (`traccar_device_id`);

--
-- Indices de la tabla `dispositivos_iot`
--
ALTER TABLE `dispositivos_iot`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `errores_sistema`
--
ALTER TABLE `errores_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `geozonas`
--
ALTER TABLE `geozonas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_geozonas_traccar_id` (`traccar_id`),
  ADD KEY `fk_geozonas_activo` (`activo_id`);

--
-- Indices de la tabla `gps_km_reportes`
--
ALTER TABLE `gps_km_reportes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_gps_km_dispositivo_periodo` (`dispositivo_id`,`fecha_desde`,`fecha_hasta`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`);

--
-- Indices de la tabla `movimientos_almacen`
--
ALTER TABLE `movimientos_almacen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suministro_id` (`suministro_id`),
  ADD KEY `responsable_id` (`responsable_id`),
  ADD KEY `oficial_id` (`oficial_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notif_user` (`user_id`);

--
-- Indices de la tabla `oficiales`
--
ALTER TABLE `oficiales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `suministros`
--
ALTER TABLE `suministros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activo_id` (`activo_id`),
  ADD KEY `responsable_id` (`responsable_id`),
  ADD KEY `fk_vehiculos_personal` (`personal_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activos`
--
ALTER TABLE `activos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `alertas_reglas`
--
ALTER TABLE `alertas_reglas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `armas`
--
ALTER TABLE `armas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `catalogos`
--
ALTER TABLE `catalogos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `combustible`
--
ALTER TABLE `combustible`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `dispositivos_gps`
--
ALTER TABLE `dispositivos_gps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `dispositivos_iot`
--
ALTER TABLE `dispositivos_iot`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `errores_sistema`
--
ALTER TABLE `errores_sistema`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `geozonas`
--
ALTER TABLE `geozonas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT de la tabla `gps_km_reportes`
--
ALTER TABLE `gps_km_reportes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `movimientos_almacen`
--
ALTER TABLE `movimientos_almacen`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `oficiales`
--
ALTER TABLE `oficiales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `suministros`
--
ALTER TABLE `suministros`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activos`
--
ALTER TABLE `activos`
  ADD CONSTRAINT `activos_ibfk_1` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_activos_personal` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `alertas_reglas`
--
ALTER TABLE `alertas_reglas`
  ADD CONSTRAINT `fk_alertas_activo` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_alertas_geozona` FOREIGN KEY (`geozona_id`) REFERENCES `geozonas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `armas`
--
ALTER TABLE `armas`
  ADD CONSTRAINT `armas_ibfk_1` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `armas_ibfk_2` FOREIGN KEY (`oficial_asignado_id`) REFERENCES `oficiales` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bitacora_ibfk_3` FOREIGN KEY (`oficial_id`) REFERENCES `oficiales` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `combustible`
--
ALTER TABLE `combustible`
  ADD CONSTRAINT `combustible_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `combustible_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `dispositivos_gps`
--
ALTER TABLE `dispositivos_gps`
  ADD CONSTRAINT `fk_gps_activo` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `errores_sistema`
--
ALTER TABLE `errores_sistema`
  ADD CONSTRAINT `errores_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `geozonas`
--
ALTER TABLE `geozonas`
  ADD CONSTRAINT `fk_geozonas_activo` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `gps_km_reportes`
--
ALTER TABLE `gps_km_reportes`
  ADD CONSTRAINT `fk_gps_km_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos_gps` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `movimientos_almacen`
--
ALTER TABLE `movimientos_almacen`
  ADD CONSTRAINT `movimientos_almacen_ibfk_1` FOREIGN KEY (`suministro_id`) REFERENCES `suministros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimientos_almacen_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `movimientos_almacen_ibfk_3` FOREIGN KEY (`oficial_id`) REFERENCES `oficiales` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `fk_vehiculos_personal` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehiculos_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
