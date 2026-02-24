<?php
class LogEntry extends BaseModel
{
    protected string $table = 'bitacora';

    public function getWithDetails(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where  = "WHERE 1=1";
        $params = [];

        if (!empty($filters['tipo'])) {
            $where .= " AND b.tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        if (!empty($filters['turno'])) {
            $where .= " AND b.turno = :turno";
            $params[':turno'] = $filters['turno'];
        }
        if (!empty($filters['oficial_id'])) {
            $where .= " AND b.oficial_id = :oficial_id";
            $params[':oficial_id'] = $filters['oficial_id'];
        }
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(b.fecha) >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(b.fecha) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql = "SELECT b.*,
                       u.nombre AS responsable_nombre,
                       o.nombre AS oficial_nombre, o.placa AS oficial_placa,
                       a.nombre AS activo_nombre, a.codigo AS activo_codigo
                FROM bitacora b
                LEFT JOIN users u    ON b.responsable_id = u.id
                LEFT JOIN oficiales o ON b.oficial_id = o.id
                LEFT JOIN activos a  ON b.activo_id = a.id
                {$where}
                ORDER BY b.fecha DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $this->query($sql, $params);
    }

    public function countFiltered(array $filters = []): int
    {
        $where  = "WHERE 1=1";
        $params = [];

        if (!empty($filters['tipo']))       { $where .= " AND tipo = :tipo";           $params[':tipo']       = $filters['tipo']; }
        if (!empty($filters['turno']))      { $where .= " AND turno = :turno";         $params[':turno']      = $filters['turno']; }
        if (!empty($filters['oficial_id'])) { $where .= " AND oficial_id = :oficial_id"; $params[':oficial_id'] = $filters['oficial_id']; }
        if (!empty($filters['fecha_desde'])) { $where .= " AND DATE(fecha) >= :fd"; $params[':fd'] = $filters['fecha_desde']; }
        if (!empty($filters['fecha_hasta'])) { $where .= " AND DATE(fecha) <= :fh"; $params[':fh'] = $filters['fecha_hasta']; }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bitacora {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getRecent(int $limit = 8): array
    {
        return $this->query(
            "SELECT b.*, u.nombre AS responsable_nombre,
                    o.nombre AS oficial_nombre, a.nombre AS activo_nombre
             FROM bitacora b
             LEFT JOIN users u     ON b.responsable_id = u.id
             LEFT JOIN oficiales o ON b.oficial_id = o.id
             LEFT JOIN activos a   ON b.activo_id = a.id
             ORDER BY b.fecha DESC LIMIT {$limit}"
        );
    }

    public function getTipoLabel(string $tipo): string
    {
        return match($tipo) {
            'entrada'    => 'Entrada',
            'salida'     => 'Salida',
            'asignacion' => 'Asignación',
            'devolucion' => 'Devolución',
            'incidencia' => 'Incidencia',
            default      => ucfirst($tipo),
        };
    }

    public function getTipoColor(string $tipo): string
    {
        return match($tipo) {
            'entrada'    => 'green',
            'salida'     => 'blue',
            'asignacion' => 'indigo',
            'devolucion' => 'yellow',
            'incidencia' => 'red',
            default      => 'gray',
        };
    }

    public function getTurnoLabel(string $turno): string
    {
        return match($turno) {
            'matutino'   => 'Matutino',
            'vespertino' => 'Vespertino',
            'nocturno'   => 'Nocturno',
            default      => ucfirst($turno),
        };
    }
}
