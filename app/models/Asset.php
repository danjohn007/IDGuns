<?php
class Asset extends BaseModel
{
    protected string $table = 'activos';

    public function getWithResponsable(int $limit = 0, int $offset = 0, array $filters = []): array
    {
        $where  = "WHERE 1=1";
        $params = [];

        if (!empty($filters['categoria'])) {
            $where .= " AND a.categoria = :categoria";
            $params[':categoria'] = $filters['categoria'];
        }
        if (!empty($filters['estado'])) {
            $where .= " AND a.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }
        if (!empty($filters['buscar'])) {
            $where .= " AND (a.nombre LIKE :buscar OR a.codigo LIKE :buscar2 OR a.serie LIKE :buscar3)";
            $params[':buscar']  = '%' . $filters['buscar'] . '%';
            $params[':buscar2'] = '%' . $filters['buscar'] . '%';
            $params[':buscar3'] = '%' . $filters['buscar'] . '%';
        }

        $sql = "SELECT a.*, u.nombre AS responsable_nombre
                FROM activos a
                LEFT JOIN users u ON a.responsable_id = u.id
                {$where}
                ORDER BY a.id DESC";

        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->query($sql, $params);
    }

    public function countFiltered(array $filters = []): int
    {
        $where  = "WHERE 1=1";
        $params = [];

        if (!empty($filters['categoria'])) {
            $where .= " AND categoria = :categoria";
            $params[':categoria'] = $filters['categoria'];
        }
        if (!empty($filters['estado'])) {
            $where .= " AND estado = :estado";
            $params[':estado'] = $filters['estado'];
        }
        if (!empty($filters['buscar'])) {
            $where .= " AND (nombre LIKE :buscar OR codigo LIKE :buscar2 OR serie LIKE :buscar3)";
            $params[':buscar']  = '%' . $filters['buscar'] . '%';
            $params[':buscar2'] = '%' . $filters['buscar'] . '%';
            $params[':buscar3'] = '%' . $filters['buscar'] . '%';
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM activos {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getCategoryStats(): array
    {
        return $this->query(
            "SELECT categoria, COUNT(*) AS total FROM activos GROUP BY categoria ORDER BY total DESC"
        );
    }

    public function getStatsByStatus(): array
    {
        return $this->query(
            "SELECT estado, COUNT(*) AS total FROM activos GROUP BY estado"
        );
    }

    public function generateCode(string $categoria): string
    {
        $prefix = match($categoria) {
            'arma'           => 'ARM',
            'vehiculo'       => 'VEH',
            'equipo_computo' => 'EQC',
            'equipo_oficina' => 'EQO',
            'bien_mueble'    => 'BIM',
            default          => 'ACT',
        };
        $count = $this->count(['categoria' => $categoria]);
        return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getCategoryLabel(string $cat): string
    {
        return match($cat) {
            'arma'           => 'Arma',
            'vehiculo'       => 'Vehículo',
            'equipo_computo' => 'Equipo de Cómputo',
            'equipo_oficina' => 'Equipo de Oficina',
            'bien_mueble'    => 'Bien Mueble',
            default          => ucfirst($cat),
        };
    }

    public function getStatusLabel(string $estado): string
    {
        return match($estado) {
            'activo'       => 'Activo',
            'baja'         => 'Baja',
            'mantenimiento'=> 'Mantenimiento',
            default        => ucfirst($estado),
        };
    }
}
