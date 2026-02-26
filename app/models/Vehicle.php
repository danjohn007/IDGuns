<?php
class Vehicle extends BaseModel
{
    protected string $table = 'vehiculos';

    public function getWithDetails(array $filters = [], int $limit = 0, int $offset = 0): array
    {
        $where  = "WHERE 1=1";
        $params = [];

        if (!empty($filters['estado'])) {
            $where .= " AND v.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }
        if (!empty($filters['tipo'])) {
            $where .= " AND v.tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        if (!empty($filters['buscar'])) {
            $where .= " AND (a.nombre LIKE :buscar OR a.codigo LIKE :buscar2 OR v.placas LIKE :buscar3 OR u.nombre LIKE :buscar4)";
            $params[':buscar']  = '%' . $filters['buscar'] . '%';
            $params[':buscar2'] = '%' . $filters['buscar'] . '%';
            $params[':buscar3'] = '%' . $filters['buscar'] . '%';
            $params[':buscar4'] = '%' . $filters['buscar'] . '%';
        }

        $sql = "SELECT v.*, a.nombre, a.codigo, a.marca, a.modelo, a.color,
                       u.nombre AS responsable_nombre
                FROM vehiculos v
                JOIN activos a ON v.activo_id = a.id
                LEFT JOIN users u ON v.responsable_id = u.id
                {$where}
                ORDER BY v.id DESC";

        if ($limit > 0) $sql .= " LIMIT {$limit} OFFSET {$offset}";

        return $this->query($sql, $params);
    }

    public function getById(int $id): ?array
    {
        return $this->queryOne(
            "SELECT v.*, a.nombre, a.codigo, a.marca, a.modelo, a.color,
                    a.descripcion, a.fecha_adquisicion, a.valor, a.ubicacion,
                    u.nombre AS responsable_nombre
             FROM vehiculos v
             JOIN activos a ON v.activo_id = a.id
             LEFT JOIN users u ON v.responsable_id = u.id
             WHERE v.id = :id",
            [':id' => $id]
        );
    }

    public function countOperativos(): int
    {
        $result = $this->query("SELECT COUNT(*) AS total FROM vehiculos WHERE estado = 'operativo'");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function getMantenimientos(int $vehiculoId): array
    {
        return $this->query(
            "SELECT * FROM mantenimientos WHERE vehiculo_id = :vid ORDER BY fecha_inicio DESC",
            [':vid' => $vehiculoId]
        );
    }

    public function getCombustible(int $vehiculoId, int $limit = 10): array
    {
        return $this->query(
            "SELECT c.*, u.nombre AS responsable_nombre
             FROM combustible c
             LEFT JOIN users u ON c.responsable_id = u.id
             WHERE c.vehiculo_id = :vid
             ORDER BY c.fecha DESC
             LIMIT {$limit}",
            [':vid' => $vehiculoId]
        );
    }

    public function getEstadoLabel(string $estado): string
    {
        return match($estado) {
            'operativo' => 'Operativo',
            'taller'    => 'En Taller',
            'baja'      => 'Baja',
            default     => ucfirst($estado),
        };
    }

    public function getTipoLabel(string $tipo): string
    {
        return match($tipo) {
            'patrulla'  => 'Patrulla',
            'moto'      => 'Motocicleta',
            'camioneta' => 'Camioneta',
            'otro'      => 'Otro',
            default     => ucfirst($tipo),
        };
    }
}
