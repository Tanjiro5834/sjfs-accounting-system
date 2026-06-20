<?php
require_once 'config/Database.php';

class SourceRepository implements SourceRepositoryInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("
            SELECT s.*,
                   c.name  AS campus_name,
                   ct.code AS type_code,
                   ct.name AS type_name,
                   ba.account_name AS bank_name,
                   u.name  AS created_by_name
            FROM sources s
            JOIN campuses c         ON c.id  = s.campus_id
            JOIN collection_types ct ON ct.id = s.collection_type_id
            JOIN bank_accounts ba   ON ba.id  = s.bank_account_id
            JOIN users u            ON u.id   = s.created_by
            ORDER BY s.transaction_date DESC, s.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        if (!$id) return null;
        $stmt = $this->db->prepare("SELECT * FROM sources WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByDateRange(string $dateFrom, string $dateTo): array {
        $stmt = $this->db->prepare("
            SELECT s.*,
                   c.name  AS campus_name,
                   ct.code AS type_code,
                   ct.name AS type_name,
                   ba.account_name AS bank_name,
                   u.name  AS created_by_name
            FROM sources s
            JOIN campuses c          ON c.id  = s.campus_id
            JOIN collection_types ct ON ct.id = s.collection_type_id
            JOIN bank_accounts ba    ON ba.id  = s.bank_account_id
            JOIN users u             ON u.id   = s.created_by
            WHERE s.transaction_date BETWEEN :date_from AND :date_to
            ORDER BY s.transaction_date DESC
        ");
        $stmt->execute([
            ':date_from' => $dateFrom,
            ':date_to'   => $dateTo,
        ]);
        return $stmt->fetchAll();
    }

    public function findByCampus(int $campusId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM sources
            WHERE campus_id = ?
            ORDER BY transaction_date DESC
        ");
        $stmt->execute([$campusId]);
        return $stmt->fetchAll();
    }

    public function findByBankAccount(int $bankAccountId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM sources
            WHERE bank_account_id = ?
            ORDER BY transaction_date DESC
        ");
        $stmt->execute([$bankAccountId]);
        return $stmt->fetchAll();
    }

    public function save(Source $source): int {
        if (empty($source->campus_id) || empty($source->collection_type_id) ||
            empty($source->bank_account_id) || empty($source->amount) ||
            empty($source->transaction_date) || empty($source->created_by)) {
            throw new InvalidArgumentException("Missing required fields");
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO sources (
                    campus_id, collection_type_id, bank_account_id,
                    amount, transaction_date, remarks, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $source->campus_id,
                $source->collection_type_id,
                $source->bank_account_id,
                $source->amount,
                $source->transaction_date,
                $source->remarks ?? null,
                $source->created_by,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, Source $source): bool {
        if (empty($source->campus_id) || empty($source->collection_type_id) ||
            empty($source->bank_account_id) || empty($source->amount) ||
            empty($source->transaction_date)) {
            throw new InvalidArgumentException("Missing required fields");
        }

        try {
            $this->db->beginTransaction();

            $oldData = $this->findById($id);
            if (!$oldData) {
                throw new RuntimeException("Source with ID {$id} not found");
            }

            $stmt = $this->db->prepare("
                UPDATE sources SET
                    campus_id          = ?,
                    collection_type_id = ?,
                    bank_account_id    = ?,
                    amount             = ?,
                    transaction_date   = ?,
                    remarks            = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $source->campus_id,
                $source->collection_type_id,
                $source->bank_account_id,
                $source->amount,
                $source->transaction_date,
                $source->remarks ?? null,
                $id,
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM sources WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->rowCount() > 0;
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTotalByDateRange(string $dateFrom, string $dateTo, int $campusId = null): float {
        $sql = "
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM sources
            WHERE transaction_date BETWEEN ? AND ?
        ";
        $params = [$dateFrom, $dateTo];

        if ($campusId !== null) {
            $sql .= " AND campus_id = ?";
            $params[] = $campusId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }
}