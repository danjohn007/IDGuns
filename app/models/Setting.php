<?php
class Setting extends BaseModel
{
    protected string $table = 'configuraciones';

    public function get(string $key, string $default = ''): string
    {
        $row = $this->queryOne(
            "SELECT valor FROM configuraciones WHERE clave = :k",
            [':k' => $key]
        );
        return $row ? $row['valor'] : $default;
    }

    public function set(string $key, string $value, string $descripcion = ''): void
    {
        $exists = $this->queryOne(
            "SELECT id FROM configuraciones WHERE clave = :k",
            [':k' => $key]
        );
        if ($exists) {
            $this->execute(
                "UPDATE configuraciones SET valor = :v WHERE clave = :k",
                [':v' => $value, ':k' => $key]
            );
        } else {
            $this->execute(
                "INSERT INTO configuraciones (clave, valor, descripcion, created_at) VALUES (:k,:v,:d,:c)",
                [':k' => $key, ':v' => $value, ':d' => $descripcion, ':c' => date('Y-m-d H:i:s')]
            );
        }
    }

    public function getAllGrouped(): array
    {
        $rows   = $this->findAll([], 'clave ASC');
        $result = [];
        foreach ($rows as $row) {
            $result[$row['clave']] = $row['valor'];
        }
        return $result;
    }

    public function getIotDevices(): array
    {
        return $this->query(
            "SELECT * FROM dispositivos_iot ORDER BY nombre ASC"
        );
    }

    public function getIotDeviceById(int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM dispositivos_iot WHERE id = :id",
            [':id' => $id]
        );
    }

    public function saveIotDevice(array $data): int
    {
        if (!empty($data['id'])) {
            $id = (int) $data['id'];
            unset($data['id']);
            $this->execute(
                "UPDATE dispositivos_iot SET nombre=:nombre,tipo=:tipo,ip=:ip,puerto=:puerto,
                 usuario=:usuario,api_key=:api_key,token=:token,activo=:activo,descripcion=:descripcion
                 WHERE id=:id",
                array_merge($data, [':id' => $id])
            );
            return $id;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $cols = implode(', ', array_keys($data));
        $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        $stmt = $this->db->prepare("INSERT INTO dispositivos_iot ({$cols}) VALUES ({$phs})");
        foreach ($data as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function deleteIotDevice(int $id): bool
    {
        return $this->execute(
            "DELETE FROM dispositivos_iot WHERE id = :id",
            [':id' => $id]
        );
    }

    public function getErrors(int $limit = 50): array
    {
        return $this->query(
            "SELECT e.*, u.nombre AS usuario_nombre
             FROM errores_sistema e
             LEFT JOIN users u ON e.usuario_id = u.id
             ORDER BY e.id DESC LIMIT {$limit}"
        );
    }
}
