-- =============================================================================
-- IDGuns — Migración v10: Caché de resúmenes de kilometraje GPS (Traccar)
-- Almacena los datos de distancia consultados desde el API de Traccar para que
-- el historial de reportes no varíe conforme avanza el tiempo de rastreo.
-- Compatible con MySQL 5.7.x / 8.x
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Crear tabla gps_km_reportes
--    Guarda el resumen devuelto por /api/reports/summary de Traccar por
--    dispositivo y rango de fechas. Los registros históricos (fecha_hasta < hoy)
--    se sirven desde aquí sin re-consultar Traccar.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gps_km_reportes` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dispositivo_id`    INT UNSIGNED NOT NULL
                            COMMENT 'FK a dispositivos_gps.id',
    `traccar_device_id` INT NOT NULL
                            COMMENT 'ID del dispositivo en el servidor Traccar',
    `fecha_desde`       DATE NOT NULL
                            COMMENT 'Inicio del período consultado',
    `fecha_hasta`       DATE NOT NULL
                            COMMENT 'Fin del período consultado',
    `distancia_m`       DECIMAL(14,2) DEFAULT NULL
                            COMMENT 'Distancia total en metros (campo distance de Traccar)',
    `engine_hours_ms`   BIGINT UNSIGNED DEFAULT NULL
                            COMMENT 'Horas de motor en milisegundos (campo engineHours de Traccar)',
    `velocidad_max`     DECIMAL(8,4) DEFAULT NULL
                            COMMENT 'Velocidad máxima en nudos (campo maxSpeed de Traccar)',
    `consultado_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                            COMMENT 'Fecha/hora en que se consultó el API de Traccar',
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_gps_km_dispositivo_periodo`
        (`dispositivo_id`, `fecha_desde`, `fecha_hasta`),
    CONSTRAINT `fk_gps_km_dispositivo`
        FOREIGN KEY (`dispositivo_id`)
        REFERENCES `dispositivos_gps`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Caché de resúmenes de kilometraje de Traccar por dispositivo y período';
