<?php
require_once 'config/Database.php';

class BankAccountRepository implements BankAccountRepositoryInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(bool $activeOnly = true): array {
        $sql = "SELECT * FROM bank_accounts";
        if ($activeOnly) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY bank_name, account_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM bank_accounts WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCampus(int $campusId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM bank_accounts
            WHERE campus_id = ? AND is_active = 1
            ORDER BY bank_name, account_name
        ");
        $stmt->execute([$campusId]);
        return $stmt->fetchAll();
    }

    public function save(BankAccount $bankAccount): int {
        try {
            if (empty($bankAccount->account_name) || empty($bankAccount->bank_name)) {
                throw new InvalidArgumentException("Missing required fields");
            }

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO bank_accounts
                    (account_name, bank_name, account_number, opening_balance, campus_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $bankAccount->account_name,
                $bankAccount->bank_name,
                $bankAccount->account_number ?? null,
                $bankAccount->opening_balance ?? 0.00,
                $bankAccount->campus_id ?? null,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, BankAccount $bankAccount): bool {
        try {
            if (empty($bankAccount->account_name) || empty($bankAccount->bank_name)) {
                throw new InvalidArgumentException("Missing required fields");
            }

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE bank_accounts SET
                    account_name    = ?,
                    bank_name       = ?,
                    account_number  = ?,
                    opening_balance = ?,
                    campus_id       = ?
                WHERE id = ?
            ");
            $result = $stmt->execute([
                $bankAccount->account_name,
                $bankAccount->bank_name,
                $bankAccount->account_number ?? null,
                $bankAccount->opening_balance ?? 0.00,
                $bankAccount->campus_id ?? null,
                $id,
            ]);

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deactivate(int $id): bool {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                UPDATE bank_accounts SET is_active = 0 WHERE id = ?
            ");
            $result = $stmt->execute([$id]);
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getBalanceSummary(string $dateFrom, string $dateTo): array {
        $stmt = $this->db->prepare("
            SELECT
                ba.id,
                ba.account_name,
                ba.bank_name,
                ba.opening_balance,
                COALESCE(s.total, 0) AS total_sources,
                COALESCE(p.total, 0) AS total_payables,
                ba.opening_balance
                    + COALESCE(s.total, 0)
                    - COALESCE(p.total, 0) AS ending_balance
            FROM bank_accounts ba
            LEFT JOIN (
                SELECT bank_account_id, SUM(amount) AS total
                FROM sources
                WHERE transaction_date BETWEEN :from1 AND :to1
                GROUP BY bank_account_id
            ) s ON s.bank_account_id = ba.id
            LEFT JOIN (
                SELECT bank_account_id, SUM(amount) AS total
                FROM payables
                WHERE transaction_date BETWEEN :from2 AND :to2
                GROUP BY bank_account_id
            ) p ON p.bank_account_id = ba.id
            WHERE ba.is_active = 1
            ORDER BY ba.bank_name, ba.account_name
        ");
        $stmt->execute([
            ':from1' => $dateFrom,
            ':to1'   => $dateTo,
            ':from2' => $dateFrom,
            ':to2'   => $dateTo,
        ]);
        return $stmt->fetchAll();
    }

    public function getBalanceById(int $id, string $dateFrom, string $dateTo): array {
        $stmt = $this->db->prepare("
            SELECT
                ba.id,
                ba.account_name,
                ba.bank_name,
                ba.opening_balance,
                COALESCE(s.total, 0) AS total_sources,
                COALESCE(p.total, 0) AS total_payables,
                ba.opening_balance
                    + COALESCE(s.total, 0)
                    - COALESCE(p.total, 0) AS ending_balance
            FROM bank_accounts ba
            LEFT JOIN (
                SELECT bank_account_id, SUM(amount) AS total
                FROM sources
                WHERE bank_account_id = :id1
                AND transaction_date BETWEEN :from1 AND :to1
                GROUP BY bank_account_id
            ) s ON s.bank_account_id = ba.id
            LEFT JOIN (
                SELECT bank_account_id, SUM(amount) AS total
                FROM payables
                WHERE bank_account_id = :id2
                AND transaction_date BETWEEN :from2 AND :to2
                GROUP BY bank_account_id
            ) p ON p.bank_account_id = ba.id
            WHERE ba.id = :id3
        ");
        $stmt->execute([
            ':id1'   => $id,
            ':from1' => $dateFrom,
            ':to1'   => $dateTo,
            ':id2'   => $id,
            ':from2' => $dateFrom,
            ':to2'   => $dateTo,
            ':id3'   => $id,
        ]);
        return $stmt->fetch() ?: [];
    }
}