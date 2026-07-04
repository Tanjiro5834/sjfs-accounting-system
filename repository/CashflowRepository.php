<?php
require_once __DIR__ . '/../interfaces/CashflowRepositoryInterface.php';

class CashflowRepository implements CashflowRepositoryInterface {
    public function __construct(private PDO $db) {}

    private function baseUnion(): string {
        return "
            SELECT 'income' AS type, s.id, s.transaction_date, s.amount, s.remarks,
                   s.bank_account_id, ba.account_name AS bank_name, ct.name AS category
            FROM sources s
            JOIN bank_accounts ba ON ba.id = s.bank_account_id
            JOIN collection_types ct ON ct.id = s.collection_type_id

            UNION ALL

            SELECT 'expense' AS type, p.id, p.transaction_date, p.amount, p.remarks,
                   p.bank_account_id, ba.account_name AS bank_name, p.payee AS category
            FROM payables p
            JOIN bank_accounts ba ON ba.id = p.bank_account_id
        ";
    }

    public function findAll(array $filters = []): array {
        $sql = "SELECT * FROM (" . $this->baseUnion() . ") cf WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND transaction_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND transaction_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND type = :type";
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['bank_account_id'])) {
            $sql .= " AND bank_account_id = :bank_account_id";
            $params['bank_account_id'] = $filters['bank_account_id'];
        }

        $sql .= " ORDER BY transaction_date DESC, id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalsByType(array $filters = []): array {
        $sql = "SELECT type, COALESCE(SUM(amount), 0) AS total
                FROM (" . $this->baseUnion() . ") cf
                WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND transaction_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND transaction_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " GROUP BY type";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $totals = ['income' => 0.0, 'expense' => 0.0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $totals[$row['type']] = (float)$row['total'];
        }
        return $totals;
    }

    public function getRunningBalance(int $bankAccountId): float {
        $sql = "SELECT
                    ba.opening_balance
                    + COALESCE((SELECT SUM(amount) FROM sources WHERE bank_account_id = :bid1), 0)
                    - COALESCE((SELECT SUM(amount) FROM payables WHERE bank_account_id = :bid2), 0)
                    AS balance
                FROM bank_accounts ba
                WHERE ba.id = :bid3";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid1' => $bankAccountId, 'bid2' => $bankAccountId, 'bid3' => $bankAccountId]);
        return (float)$stmt->fetchColumn();
    }
}