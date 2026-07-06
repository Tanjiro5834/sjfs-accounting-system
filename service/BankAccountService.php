<?php
require_once 'repository/BankAccountRepository.php';
require_once 'dto/request/BankAccountRequest.php';
require_once 'dto/response/BankAccountResponse.php';

class BankAccountService {
    private BankAccountRepositoryInterface $bankRepo;
    private AuditLogRepositoryInterface $auditRepo;

    public function __construct(BankAccountRepositoryInterface $bankRepo, AuditLogRepositoryInterface $auditRepo) {
        $this->bankRepo  = $bankRepo;
        $this->auditRepo = $auditRepo;
    }

    public function getAll(bool $activeOnly = true): array {
        $rows = $this->bankRepo->findAll($activeOnly);
        return empty($rows) ? [] : BankAccountResponse::fromArray($rows);
    }

    public function getById(int $id): ?BankAccountResponse {
        if ($id <= 0) throw new InvalidArgumentException("Invalid bank account ID");
        $row = $this->bankRepo->findById($id);
        return $row ? new BankAccountResponse($row) : null;
    }

    public function getByCampus(int $campusId): array {
        if ($campusId <= 0) throw new InvalidArgumentException("Invalid campus ID");
        $rows = $this->bankRepo->findByCampus($campusId);
        return empty($rows) ? [] : BankAccountResponse::fromArray($rows);
    }

    public function getBalanceSummary(string $dateFrom, string $dateTo): array {
        $this->validateDateRange($dateFrom, $dateTo);
        $rows = $this->bankRepo->getBalanceSummary($dateFrom, $dateTo);
        return empty($rows) ? [] : BankAccountResponse::fromArray($rows);
    }

    public function getBalanceById(int $id, string $dateFrom, string $dateTo): ?BankAccountResponse {
        if ($id <= 0) throw new InvalidArgumentException("Invalid bank account ID");
        $this->validateDateRange($dateFrom, $dateTo);
        $row = $this->bankRepo->getBalanceById($id, $dateFrom, $dateTo);
        return $row ? new BankAccountResponse($row) : null;
    }

    public function create(array $data, int $createdBy): int {
        if ($createdBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $request = new BankAccountRequest($data);
        $errors  = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $bank = new BankAccount([
            'account_name'    => $request->account_name,
            'bank_name'       => $request->bank_name,
            'account_number'  => $request->account_number,
            'opening_balance' => $request->opening_balance,
            'campus_id'       => $request->campus_id,
        ]);

        $id = $this->bankRepo->save($bank);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $createdBy,
            'action'    => 'CREATE',
            'module'    => 'BANKS',
            'record_id' => $id,
            'old_value' => null,
            'new_value' => $bank->toArray(), 
        ]));

        return $id;
    }

    public function update(int $id, array $data, int $updatedBy): bool {
        if ($id <= 0) throw new InvalidArgumentException("Invalid bank account ID");
        if ($updatedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $existing = $this->bankRepo->findById($id);
        if (!$existing) throw new RuntimeException("Bank account with ID {$id} not found");

        $request = new BankAccountRequest($data);
        $errors  = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $bank = new BankAccount([
            'account_name'    => $request->account_name,
            'bank_name'       => $request->bank_name,
            'account_number'  => $request->account_number,
            'opening_balance' => $request->opening_balance,
            'campus_id'       => $request->campus_id,
        ]);

        $result = $this->bankRepo->update($id, $bank);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $updatedBy,
            'action'    => 'UPDATE',
            'module'    => 'BANKS',
            'record_id' => $id,
            'old_value' => $existing,
            'new_value' => $bank->toArray(), 
        ]));

        return $result;
    }

    public function deactivate(int $id, int $deactivatedBy): bool {
        if ($id <= 0) throw new InvalidArgumentException("Invalid bank account ID");
        if ($deactivatedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $existing = $this->bankRepo->findById($id);
        if (!$existing) throw new RuntimeException("Bank account with ID {$id} not found");
        if ((int) $existing['is_active'] === 0) throw new RuntimeException("Bank account already inactive");

        $result = $this->bankRepo->deactivate($id);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $deactivatedBy,
            'action'    => 'DELETE',
            'module'    => 'BANKS',
            'record_id' => $id,
            'old_value' => $existing,
            'new_value' => null,
        ]));

        return $result;
    }

    private function validateDateRange(string $dateFrom, string $dateTo): void {
        if (empty($dateFrom) || empty($dateTo)) {
            throw new InvalidArgumentException("Date range is required");
        }
        if (strtotime($dateFrom) > strtotime($dateTo)) {
            throw new InvalidArgumentException("dateFrom cannot be after dateTo");
        }
    }

    public function getAllPaginated(int $page, int $perPage, bool $activeOnly = true): array {
        $total = $this->bankRepo->countAll($activeOnly);
        $rows  = $this->bankRepo->findAllPaginated($page, $perPage, $activeOnly);

        return [
            'data'        => empty($rows) ? [] : BankAccountResponse::fromArray($rows),
            'total'       => $total,
            'page'        => $page,
            'total_pages' => (int) ceil($total / $perPage) ?: 1,
        ];
    }
}