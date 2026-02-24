<?php
class User extends BaseModel
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        return $this->queryOne(
            "SELECT * FROM users WHERE username = :u AND activo = 1",
            [':u' => $username]
        );
    }

    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function getAllActive(): array
    {
        return $this->findAll(['activo' => 1], 'nombre ASC');
    }

    public function createUser(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT)
        ]);
    }

    public function getRoleLabel(string $rol): string
    {
        return match($rol) {
            'superadmin' => 'Super Administrador',
            'admin'      => 'Administrador',
            'almacen'    => 'Almacén',
            'bitacora'   => 'Bitácora',
            default      => ucfirst($rol),
        };
    }
}
