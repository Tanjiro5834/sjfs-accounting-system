<?php
require_once 'repository/PayableRepository.php';
require_once 'dto/request/PayableRequest.php';
require_once 'dto/response/PayableResponse.php';
require_once 'middleware/auth.php';

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
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }

        // ✅ Fix: Define $campusId
        $campusId = getUserCampusId(); // Add this line
        $rows = $campusId  // Change $campusFilter to $campusId
            ? $this->payableRepo->findAllByCampus($campusId)
            : $this->payableRepo->findAll();
            
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getById(int $id): ?PayableResponse {
        if ($id <= 0) throw new InvalidArgumentException("Invalid payable ID");
        
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }
        
        $row = $this->payableRepo->findById($id);
        if (!$row) return null;

        $this->enforceCampusRestriction($row);
        
        return $row ? new PayableResponse($row) : null;
    }

    public function getByDateRange(string $dateFrom, string $dateTo): array {
        $this->validateDateRange($dateFrom, $dateTo);
        
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }
        
        $campusId = getUserCampusId();
        $rows = $campusId 
            ? $this->payableRepo->findByDateRangeAndCampus($dateFrom, $dateTo, $campusId)
            : $this->payableRepo->findByDateRange($dateFrom, $dateTo);
            
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getByDateRangeAndCampus(string $dateFrom, string $dateTo, int $campusId): array {
        $rows = $this->payableRepo->findByDateRangeAndCampus($dateFrom, $dateTo, $campusId);
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getTotalByDateRangeAndCampus(string $dateFrom, string $dateTo, int $campusId): float {
        return $this->payableRepo->getTotalByDateRangeAndCampus($dateFrom, $dateTo, $campusId);
    }

    public function getByBankAccount(int $bankAccountId): array {
        if ($bankAccountId <= 0) throw new InvalidArgumentException("Invalid bank account ID");
        
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }
        
        $rows = $this->payableRepo->findByBankAccount($bankAccountId);
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getByPayee(string $payee): array {
        if (empty(trim($payee))) throw new InvalidArgumentException("Payee name is required");
        
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }
        
        $rows = $this->payableRepo->findByPayee($payee);
        return empty($rows) ? [] : PayableResponse::fromArray($rows);
    }

    public function getByCheckNumber(string $checkNumber): ?PayableResponse {
        if (empty(trim($checkNumber))) throw new InvalidArgumentException("Check number is required");
        
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }
            
        $row = $this->payableRepo->findByCheckNumber($checkNumber);
        return $row ? new PayableResponse($row) : null;
    }

    public function getTotalByDateRange(string $dateFrom, string $dateTo): float {
        $this->validateDateRange($dateFrom, $dateTo);
        
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }
        
        $campusId = getUserCampusId(); // Add this line
        return $campusId  // Change $campusFilter to $campusId
            ? $this->payableRepo->getTotalByDateRangeAndCampus($dateFrom, $dateTo, $campusId)
            : $this->payableRepo->getTotalByDateRange($dateFrom, $dateTo);
    }

    public function create(array $data, int $createdBy): int {
        if ($createdBy <= 0) throw new InvalidArgumentException("Invalid user ID");
        
        if (!can('payables', 'create')) {
            throw new Exception('Access denied');
        }
        
        // Cashiers and auditors can only create (no read-only check needed)
        // Just check if they have create permission

        $request = new PayableRequest($data);
        $errors  = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        // Enforce campus restriction for cashiers and auditors
        $campusId = getUserCampusId();
        if (hasCampusRestriction() && !$campusId) {
            throw new InvalidArgumentException('No campus assigned to user');
        }

        $payableData = [
            'payee'           => $request->payee,
            'check_number'    => $request->check_number,
            'bank_account_id' => $request->bank_account_id,
            'amount'          => $request->amount,
            'transaction_date'=> $request->transaction_date,
            'remarks'         => $request->remarks,
            'created_by'      => $createdBy,
        ];
        
        // Add campus_id if user has restriction
        if (hasCampusRestriction()) {
            $payableData['campus_id'] = $campusId;
        }

        $payable = new Payable($payableData);

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
        if ($id <= 0) throw new InvalidArgumentException("Invalid payable ID");
        if ($updatedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        if (!can('payables', 'update')) {
            throw new Exception('Access denied');
        }

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
        if ($id <= 0) throw new InvalidArgumentException("Invalid payable ID");
        if ($deletedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        // Only admin can delete
        if (!hasRole('admin')) {
            throw new Exception('Only administrators can delete financial records');
        }

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

    /**
     * Get current user's campus ID
     */
    private function getUserCampusId(): ?int {
        $user = currentUser();
        return $user['campus_id'] ?? null;
    }

    private function validateDateRange(string $dateFrom, string $dateTo): void {
        if (empty($dateFrom) || empty($dateTo)) {
            throw new InvalidArgumentException("Date range is required");
        }
        if (strtotime($dateFrom) > strtotime($dateTo)) {
            throw new InvalidArgumentException("dateFrom cannot be after dateTo");
        }
    }

    private function enforceCampusRestriction(array $record): void {
        if (hasCampusRestriction()) {
            $userCampusId = getUserCampusId();
            $recordCampusId = $record['campus_id'] ?? null;
            
            if (!$userCampusId) {
                throw new Exception('No campus assigned to user');
            }
            
            if ($recordCampusId !== null && $recordCampusId !== $userCampusId) {
                throw new Exception('You can only access records from your campus');
            }
        }
    }
}