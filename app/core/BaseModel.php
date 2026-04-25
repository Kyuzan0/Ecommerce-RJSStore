<?php

class BaseModel
{
    protected Database $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->db->execute(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;
        return $this->db->execute(
            "UPDATE {$this->table} SET {$set} WHERE id = ?",
            $params
        );
    }

    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function where(string $conditions, array $params = []): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE {$conditions}", $params);
    }

    public function count(string $conditions = '1', array $params = []): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE {$conditions}",
            $params
        );
        return $row ? (int) $row['total'] : 0;
    }
}
