<?php
class Personal extends BaseModel
{
    protected string $table = 'personal';

    public function getAllActive(): array
    {
        return $this->query(
            "SELECT * FROM personal WHERE activo = 1 ORDER BY cargo ASC, nombre ASC, apellidos ASC"
        );
    }

    /**
     * Returns full display name: "Cargo Nombre Apellidos"
     */
    public static function fullName(array $p): string
    {
        $parts = array_filter([
            trim($p['cargo']    ?? ''),
            trim($p['nombre']   ?? ''),
            trim($p['apellidos']?? ''),
        ]);
        return implode(' ', $parts);
    }
}
