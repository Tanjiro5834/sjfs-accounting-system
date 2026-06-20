<?php
require_once 'repositories/PayableRepository.php';
require_once 'dto/request/PayableRequest.php';
require_once 'dto/response/PayableResponse.php';

class PayableService {
    private PayableRepositoryInterface $payableRepo;
    private AuditLogRepositoryInterface $auditRepo;

    public function __construct(
        PayableRepositoryInterface $payableRepo,
        AuditLogRepositoryInterface $auditRepo
    ) {
        $this->payableRepo = $payableRepo;
        $this->auditRepo   = $auditRepo;
    }

    public function getAll(): array {
        $rows = $this->payableRepo->findAll();
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getById(int $id): ?PayableResponse {
        if ($id <= 0) throw new InvalidArgumentException("Invalid payable ID");
        $row = $this->payableRepo->findById($id);
        return $row ? new PayableResponse($row) : null;
    }

    public function getByDateRange(string $dateFrom, string $dateTo): array {
        $this->validateDateRange($dateFrom, $dateTo);
        $rows = $this->payableRepo->findByDateRange($dateFrom, $dateTo);
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getByBankAccount(int $bankAccountId): array {
        if ($bankAccountId <= 0) throw new InvalidArgumentException("Invalid bank account ID");
        $rows = $this->payableRepo->findByBankAccount($bankAccountId);
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getByPayee(string $payee): array {
        if (empty(trim($payee))) throw new InvalidArgumentException("Payee name is required");
        $rows = $this->payableRepo->findByPayee($payee);
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getByCheckNumber(string $checkNumber): ?PayableResponse {
        if (empty(trim($checkNumber))) throw new InvalidArgumentException("Check number is required");
        $row = $this->payableRepo->findByCheckNumber($checkNumber);
        return $row ? new PayableResponse($row) : null;
    }

    public function getTotalByDateRange(string $dateFrom, string $dateTo): float {
        $this->validateDateRange($dateFrom, $dateTo);
        return $this->payableRepo->getTotalByDateRange($dateFrom, $dateTo);
    }

    public function create(array $data, int $createdBy): int {
        if ($createdBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $request = new PayableRequest($data);
        $errors  = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $payable = new Payable([
            'payee'           => $request->payee,
            'check_number'    => $request->check_number,
            'bank_account_id' => $request->bank_account_id,
            'amount'          => $request->amount,
            'transaction_date'=> $request->transaction_date,
            'remarks'         => $request->remarks,
            'created_by'      => $createdBy,
        ]);

        $id = $this->payableRepo->save($payable);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $createdBy,
            'action'    => 'CREATE',
            'module'    => 'PAYABLES',
            'record_id' => $id,
            'old_value' => null,
            'new_value' => json_encode($payable->toArray()),
        ]));

        return $id;
    }

    public function update(int $id, array $data, int $updatedBy): bool {
        if ($id <= 0)        throw new InvalidArgumentException("Invalid payable ID");
        if ($updatedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $existing = $this->payableRepo->findById($id);
        if (!$existing) throw new RuntimeException("Payable with ID {$id} not found");

        $request = new PayableRequest($data);
        $errors  = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $payable = new Payable([
            'payee'           => $request->payee,
            'check_number'    => $request->check_number,
            'bank_account_id' => $request->bank_account_id,
            'amount'          => $request->amount,
            'transaction_date'=> $request->transaction_date,
            'remarks'         => $request->remarks,
        ]);

        $result = $this->payableRepo->update($id, $payable);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $updatedBy,
            'action'    => 'UPDATE',
            'module'    => 'PAYABLES',
            'record_id' => $id,
            'old_value' => json_encode($existing),
            'new_value' => json_encode($payable->toArray()),
        ]));

        return $result;
    }

    public function delete(int $id, int $deletedBy): bool {
        if ($id <= 0)        throw new InvalidArgumentException("Invalid payable ID");
        if ($deletedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $existing = $this->payableRepo->findById($id);
        if (!$existing) throw new RuntimeException("Payable with ID {$id} not found");

        $result = $this->payableRepo->delete($id);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $deletedBy,
            'action'    => 'DELETE',
            'module'    => 'PAYABLES',
            'record_id' => $id,
            'old_value' => json_encode($existing),
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
}