<?php
require_once 'config/Database.php';
require_once __DIR__ . '/../interfaces/PayableRepositoryInterface.php';
require_once __DIR__ . '/../entity/Payable.php';

class PayableRepository implements PayableRepositoryInterface {
    private PDO $db;

    public function __construct(PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            ORDER BY p.transaction_date DESC, p.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function findByDateRange(string $dateFrom, string $dateTo): array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            WHERE p.transaction_date BETWEEN :date_from AND :date_to
            ORDER BY p.transaction_date DESC
        ");
        $stmt->execute([
            ':date_from' => $dateFrom,
            ':date_to'   => $dateTo,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByBankAccount(int $bankAccountId): array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            WHERE p.bank_account_id = ?
            ORDER BY p.transaction_date DESC
        ");
        $stmt->execute([$bankAccountId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByPayee(string $payee): array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            WHERE p.payee LIKE ?
            ORDER BY p.transaction_date DESC
            LIMIT 50
        ");
        $stmt->execute(['%' . $payee . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByCheckNumber(string $checkNumber): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            WHERE p.check_number = ?
        ");
        $stmt->execute([$checkNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function save(Payable $payable): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO payables 
            (payee, check_number, bank_account_id, amount, transaction_date, remarks, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $payable->payee,
                $payable->check_number,
                $payable->bank_account_id,
                $payable->amount,
                $payable->transaction_date,
                $payable->remarks,
                $payable->created_by,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, Payable $payable): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE payables SET
                    payee            = ?,
                    check_number     = ?,
                    bank_account_id  = ?,
                    amount           = ?,
                    transaction_date = ?,
                    remarks          = ?
                WHERE id = ?
            ");
            $result = $stmt->execute([
                $payable->payee,
                $payable->check_number ?? null,
                $payable->bank_account_id,
                $payable->amount,
                $payable->transaction_date,
                $payable->remarks ?? null,
                $id,
            ]);

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM payables WHERE id = ?");
            $result = $stmt->execute([$id]);
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function getTotalByDateRange(string $dateFrom, string $dateTo): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM payables
            WHERE transaction_date BETWEEN ? AND ?
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return (float) $stmt->fetchColumn();
    }

    public function findAllByCampus(int $campusId): array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, ba.campus_id, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            WHERE ba.campus_id = :campus_id
            ORDER BY p.transaction_date DESC, p.created_at DESC
        ");
        $stmt->execute(['campus_id' => $campusId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    public function findByDateRangeAndCampus(string $dateFrom, string $dateTo, int $campusId): array {
        $stmt = $this->db->prepare("
            SELECT p.*, ba.account_name AS bank_name, ba.campus_id, u.name AS created_by_name
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            JOIN users u ON u.id = p.created_by
            WHERE ba.campus_id = :campus_id
            AND p.transaction_date BETWEEN :date_from AND :date_to
            ORDER BY p.transaction_date DESC
        ");
        $stmt->execute([
            'campus_id' => $campusId,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTotalByDateRangeAndCampus(string $dateFrom, string $dateTo, int $campusId): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(p.amount), 0) as total
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
            WHERE ba.campus_id = :campus_id
            AND p.transaction_date BETWEEN :date_from AND :date_to
        ");
        $stmt->execute([
            'campus_id' => $campusId,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }
}