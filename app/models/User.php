<?php

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE email = ?", [$email]);
    }

    public function emailExists(string $email): bool
    {
        $row = $this->db->fetchOne("SELECT id FROM {$this->table} WHERE email = ?", [$email]);
        return $row !== null;
    }

    public function createUser(string $name, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $hash,
            'role'     => 'customer',
        ]);
    }

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        return $this->update($id, ['password' => $hashedPassword]);
    }

    /**
     * Verify password with bcrypt + MD5 auto-upgrade fallback.
     */
    public function verifyPassword(string $inputPassword, array $user): bool
    {
        if (password_verify($inputPassword, $user['password'])) {
            return true;
        }

        // MD5 legacy fallback with auto-upgrade
        if ($user['password'] === md5($inputPassword)) {
            $newHash = password_hash($inputPassword, PASSWORD_DEFAULT);
            $this->updatePassword((int) $user['id'], $newHash);
            return true;
        }

        return false;
    }
}
