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

        $sql = "SELECT a.*, COALESCE(NULLIF(CONCAT_WS(' ', p.cargo, p.nombre, p.apellidos), ''), u.nombre) AS responsable_nombre
                FROM activos a
                LEFT JOIN personal p ON a.personal_id = p.id
                LEFT JOIN users u ON a.responsable_id = u.id
                {$where}
                ORDER BY a.id DESC";

        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        try {
            return $this->query($sql, $params);
        } catch (\Throwable $e) {
            // personal_id column may not exist yet; fall back to users only
            $sqlFallback = "SELECT a.*, u.nombre AS responsable_nombre
                FROM activos a
                LEFT JOIN users u ON a.responsable_id = u.id
                {$where}
                ORDER BY a.id DESC";
            if ($limit > 0) $sqlFallback .= " LIMIT {$limit} OFFSET {$offset}";
            return $this->query($sqlFallback, $params);
        }
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

        // Find the highest numeric suffix already used for this prefix so that
        // different categories that share the same prefix (e.g. 'movil' → 'ACT')
        // never produce a duplicate codigo.
        $stmt = $this->db->prepare(
            "SELECT CAST(SUBSTRING(codigo, :offset) AS UNSIGNED) AS num
             FROM activos WHERE codigo LIKE :pattern
             ORDER BY num DESC LIMIT 1"
        );
        $stmt->execute([
            ':offset'  => strlen($prefix) + 2,  // skip "PREFIX-"
            ':pattern' => $prefix . '-%',
        ]);
        $row  = $stmt->fetch();
        $next = ($row ? (int) $row['num'] : 0) + 1;

        // Advance until we find a code that is not already taken (handles gaps).
        do {
            $candidate = $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            $taken     = $this->queryOne(
                "SELECT id FROM activos WHERE codigo = :c",
                [':c' => $candidate]
            );
            if ($taken) {
                $next++;
            }
        } while ($taken);

        return $candidate;
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
