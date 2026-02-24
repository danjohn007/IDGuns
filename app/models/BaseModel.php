<?php
class BaseModel
{
    protected PDO    $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Return all rows matching conditions */
    public function findAll(array $conditions = [], string $orderBy = 'id DESC', int $limit = 0, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM {$this->table}" . $where;
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) $sql .= " OFFSET {$offset}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Return a single row by id */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Count rows matching conditions */
    public function count(array $conditions = []): int
    {
        [$where, $params] = $this->buildWhere($conditions);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}" . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Insert a row and return new id */
    public function insert(array $data): int
    {
        $cols  = implode(', ', array_keys($data));
        $phs   = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        $stmt  = $this->db->prepare("INSERT INTO {$this->table} ({$cols}) VALUES ({$phs})");
        foreach ($data as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    /** Update a row by id */
    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$sets} WHERE id = :id");
        foreach ($data as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Delete a row by id */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** Return paginated result: ['data'=>[], 'total'=>int, 'pages'=>int] */
    public function paginate(int $page, int $perPage, array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $total  = $this->count($conditions);
        $pages  = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $data   = $this->findAll($conditions, $orderBy, $perPage, $offset);
        return compact('data', 'total', 'pages');
    }

    /** Execute arbitrary SQL and return all rows */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Execute arbitrary SQL and return one row */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Execute arbitrary SQL (INSERT/UPDATE/DELETE) */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // -----------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------
    private function buildWhere(array $conditions): array
    {
        if (empty($conditions)) return ['', []];
        $wheres = [];
        $params = [];
        foreach ($conditions as $k => $v) {
            $placeholder      = ':' . str_replace('.', '_', $k);
            $wheres[]         = "{$k} = {$placeholder}";
            $params[$placeholder] = $v;
        }
        return [' WHERE ' . implode(' AND ', $wheres), $params];
    }
}
