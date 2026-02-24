<?php
class Weapon extends BaseModel
{
    protected string $table = 'armas';

    public function getWithDetails(): array
    {
        return $this->query(
            "SELECT ar.*, a.nombre, a.codigo, a.marca, a.modelo, a.ubicacion,
                    o.nombre AS oficial_nombre, o.placa AS oficial_placa, o.rango AS oficial_rango
             FROM armas ar
             JOIN activos a ON ar.activo_id = a.id
             LEFT JOIN oficiales o ON ar.oficial_asignado_id = o.id
             ORDER BY ar.id DESC"
        );
    }

    public function getById(int $id): ?array
    {
        return $this->queryOne(
            "SELECT ar.*, a.nombre, a.codigo, a.marca, a.modelo, a.serie AS serie_activo,
                    a.ubicacion, a.descripcion, a.fecha_adquisicion, a.valor,
                    o.nombre AS oficial_nombre, o.placa AS oficial_placa
             FROM armas ar
             JOIN activos a ON ar.activo_id = a.id
             LEFT JOIN oficiales o ON ar.oficial_asignado_id = o.id
             WHERE ar.id = :id",
            [':id' => $id]
        );
    }

    public function countOperativas(): int
    {
        return (int) $this->query(
            "SELECT COUNT(*) AS total FROM armas WHERE estado = 'operativa'"
        )[0]['total'];
    }

    public function getTipoLabel(string $tipo): string
    {
        return match($tipo) {
            'pistola'  => 'Pistola',
            'rifle'    => 'Rifle',
            'escopeta' => 'Escopeta',
            'otro'     => 'Otro',
            default    => ucfirst($tipo),
        };
    }

    public function getEstadoLabel(string $estado): string
    {
        return match($estado) {
            'operativa'     => 'Operativa',
            'mantenimiento' => 'Mantenimiento',
            'baja'          => 'Baja',
            default         => ucfirst($estado),
        };
    }
}
