<?php
class Supply extends BaseModel
{
    protected string $table = 'suministros';

    public function getAllWithStatus(int $limit = 0, int $offset = 0, array $filters = []): array
    {
        $where  = "WHERE 1=1";
        $params = [];

        if (!empty($filters['categoria'])) {
            $where .= " AND categoria = :categoria";
            $params[':categoria'] = $filters['categoria'];
        }
        if (!empty($filters['buscar'])) {
            $where .= " AND (nombre LIKE :buscar OR proveedor LIKE :buscar2)";
            $params[':buscar']  = '%' . $filters['buscar'] . '%';
            $params[':buscar2'] = '%' . $filters['buscar'] . '%';
        }
        if (!empty($filters['alerta'])) {
            $where .= " AND stock_actual <= stock_minimo";
        }

        $sql = "SELECT s.*,
                CASE
                  WHEN s.stock_actual <= s.stock_minimo THEN 'critico'
                  WHEN s.stock_actual <= (s.stock_minimo * 1.5) THEN 'bajo'
                  ELSE 'ok'
                END AS nivel_stock
                FROM suministros s
                {$where}
                ORDER BY nivel_stock ASC, s.nombre ASC";

        if ($limit > 0) $sql .= " LIMIT {$limit} OFFSET {$offset}";

        return $this->query($sql, $params);
    }

    public function getLowStock(): array
    {
        return $this->query(
            "SELECT * FROM suministros WHERE stock_actual <= stock_minimo ORDER BY stock_actual ASC"
        );
    }

    public function countLowStock(): int
    {
        $result = $this->query("SELECT COUNT(*) AS total FROM suministros WHERE stock_actual <= stock_minimo");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function getMovimientos(int $suministroId, int $limit = 20): array
    {
        return $this->query(
            "SELECT m.*, u.nombre AS responsable_nombre, o.nombre AS oficial_nombre
             FROM movimientos_almacen m
             LEFT JOIN users u ON m.responsable_id = u.id
             LEFT JOIN oficiales o ON m.oficial_id = o.id
             WHERE m.suministro_id = :sid
             ORDER BY m.fecha DESC
             LIMIT {$limit}",
            [':sid' => $suministroId]
        );
    }

    public function registrarMovimiento(array $data): int
    {
        $supply = $this->findById($data['suministro_id']);
        if (!$supply) throw new \RuntimeException("Suministro no encontrado.");

        // Update stock
        $nuevoStock = $supply['stock_actual'];
        if ($data['tipo'] === 'entrada') {
            $nuevoStock += (int) $data['cantidad'];
        } else {
            $nuevoStock -= (int) $data['cantidad'];
            if ($nuevoStock < 0) throw new \RuntimeException("Stock insuficiente.");
        }
        $this->update($data['suministro_id'], ['stock_actual' => $nuevoStock]);

        // Insert movement record
        $data['created_at'] = date('Y-m-d H:i:s');
        unset($data['suministro_id']); // will re-add below

        $movData = [
            'suministro_id'  => $supply['id'],
            'tipo'           => $data['tipo'],
            'cantidad'       => $data['cantidad'],
            'responsable_id' => $data['responsable_id'] ?? null,
            'oficial_id'     => $data['oficial_id'] ?? null,
            'motivo'         => $data['motivo'] ?? '',
            'fecha'          => $data['fecha'] ?? date('Y-m-d H:i:s'),
            'notas'          => $data['notas'] ?? '',
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        $stmt = $this->db->prepare(
            "INSERT INTO movimientos_almacen (suministro_id,tipo,cantidad,responsable_id,oficial_id,motivo,fecha,notas,created_at)
             VALUES (:suministro_id,:tipo,:cantidad,:responsable_id,:oficial_id,:motivo,:fecha,:notas,:created_at)"
        );
        foreach ($movData as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function getMovimientosRecientes(int $limit = 10): array
    {
        return $this->query(
            "SELECT m.*, s.nombre AS suministro_nombre, u.nombre AS responsable_nombre
             FROM movimientos_almacen m
             JOIN suministros s ON m.suministro_id = s.id
             LEFT JOIN users u ON m.responsable_id = u.id
             ORDER BY m.fecha DESC
             LIMIT {$limit}"
        );
    }

    public function getMovimientosPorDia(int $days = 7): array
    {
        return $this->query(
            "SELECT DATE(fecha) AS dia, tipo, SUM(cantidad) AS total
             FROM movimientos_almacen
             WHERE fecha >= DATE_SUB(NOW(), INTERVAL {$days} DAY)
             GROUP BY DATE(fecha), tipo
             ORDER BY dia ASC"
        );
    }

    public function getCategoriaLabel(string $cat): string
    {
        return match($cat) {
            'limpieza'    => 'Limpieza',
            'papeleria'   => 'Papelería',
            'uniforme'    => 'Uniformes',
            'municion'    => 'Munición',
            'herramienta' => 'Herramienta',
            'otro'        => 'Otro',
            default       => ucfirst($cat),
        };
    }
}
