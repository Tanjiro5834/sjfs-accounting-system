<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../interfaces/UserRepositoryInterface.php';
require_once __DIR__ . '/../entity/User.php';

class UserRepository implements UserRepositoryInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?array {
        if ($id <= 0) return null;
        $stmt = $this->db->prepare("
            SELECT id, name, email, role, campus_id, is_active, created_at
            FROM users
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array {
        if (empty($email)) return null;
        $stmt = $this->db->prepare("
            SELECT id, name, email, password, role, campus_id, is_active, created_at
            FROM users
            WHERE email = ? AND is_active = 1
        ");
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("
            SELECT id, name, email, role, campus_id, is_active, created_at
            FROM users
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function save(User $user): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role, campus_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user->name,
                strtolower(trim($user->email)),
                $user->password,
                $user->role,
                $user->campus_id ?? null,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, User $user): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE users SET
                    name      = ?,
                    email     = ?,
                    role      = ?,
                    campus_id = ?
                WHERE id = ?
            ");
            $result = $stmt->execute([
                $user->name,
                strtolower(trim($user->email)),
                $user->role,
                $user->campus_id ?? null,
                $id,
            ]);

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function deactivate(int $id): bool {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $result = $stmt->execute([$id]);
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}