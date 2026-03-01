<?php
class GpsDevice extends BaseModel
{
    protected string $table = 'dispositivos_gps';

    /** Get GPS device linked to a specific asset */
    public function findByActivoId(int $activoId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM dispositivos_gps WHERE activo_id = :a",
            [':a' => $activoId]
        );
    }

    /** Get all GPS devices with linked asset info */
    public function getAllWithAsset(): array
    {
        return $this->query(
            "SELECT g.*, a.nombre AS activo_nombre, a.categoria AS activo_categoria,
                    a.codigo AS activo_codigo
             FROM dispositivos_gps g
             INNER JOIN activos a ON g.activo_id = a.id
             WHERE g.activo = 1
             ORDER BY g.nombre ASC"
        );
    }

    /** Upsert (insert or update) GPS device for an asset */
    public function upsertForActivo(int $activoId, array $data): int
    {
        $existing = $this->findByActivoId($activoId);
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        $data['activo_id']  = $activoId;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /** Persist per-device km/Litro value */
    public function updateKmPorLitro(int $id, ?float $kmL): bool
    {
        return $this->execute(
            "UPDATE dispositivos_gps SET km_por_litro = :kml WHERE id = :id",
            [':kml' => $kmL, ':id' => $id]
        );
    }

    /**
     * Find a cached km report for a device and date range.
     * Returns the row array or null when not found.
     */
    public function findKmReporte(int $dispositivoId, string $desde, string $hasta): ?array
    {
        return $this->queryOne(
            "SELECT * FROM gps_km_reportes
             WHERE dispositivo_id = :did
               AND fecha_desde    = :desde
               AND fecha_hasta    = :hasta
             LIMIT 1",
            [':did' => $dispositivoId, ':desde' => $desde, ':hasta' => $hasta]
        );
    }

    /**
     * Insert or update a km report row (cache from Traccar API).
     * Expects keys: dispositivo_id, traccar_device_id, fecha_desde, fecha_hasta,
     *               distancia_m, engine_hours_ms, velocidad_max.
     */
    public function upsertKmReporte(array $data): bool
    {
        return $this->execute(
            "INSERT INTO gps_km_reportes
                 (dispositivo_id, traccar_device_id, fecha_desde, fecha_hasta,
                  distancia_m, engine_hours_ms, velocidad_max, consultado_at)
             VALUES
                 (:did, :tid, :desde, :hasta,
                  :dist, :eh, :vmax, NOW())
             ON DUPLICATE KEY UPDATE
                 distancia_m       = :u_dist,
                 engine_hours_ms   = :u_eh,
                 velocidad_max     = :u_vmax,
                 consultado_at     = NOW()",
            [
                ':did'    => $data['dispositivo_id'],
                ':tid'    => $data['traccar_device_id'],
                ':desde'  => $data['fecha_desde'],
                ':hasta'  => $data['fecha_hasta'],
                ':dist'   => $data['distancia_m'],
                ':eh'     => $data['engine_hours_ms'],
                ':vmax'   => $data['velocidad_max'],
                ':u_dist' => $data['distancia_m'],
                ':u_eh'   => $data['engine_hours_ms'],
                ':u_vmax' => $data['velocidad_max'],
            ]
        );
    }

    /** Traccar category labels */
    public static function getCategoryOptions(): array
    {
        return [
            'car'         => 'Automóvil',
            'motorcycle'  => 'Motocicleta',
            'truck'       => 'Camión',
            'van'         => 'Camioneta',
            'bus'         => 'Autobús',
            'bicycle'     => 'Bicicleta',
            'person'      => 'Persona',
            'plane'       => 'Avión',
            'helicopter'  => 'Helicóptero',
            'boat'        => 'Embarcación',
            'animal'      => 'Animal',
            'other'       => 'Otro',
        ];
    }
}
