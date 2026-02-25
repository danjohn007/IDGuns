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
