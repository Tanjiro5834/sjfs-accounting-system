<?php
require_once 'repository/SourceRepository.php';
require_once 'dto/request/SourceRequest.php';
require_once 'dto/response/SourceResponse.php';

class SourceService {
    private SourceRepositoryInterface $sourceRepo;
    private AuditLogRepositoryInterface $auditRepo;

    public function __construct(SourceRepositoryInterface $sourceRepo, AuditLogRepositoryInterface $auditRepo) {
        $this->sourceRepo = $sourceRepo;
        $this->auditRepo  = $auditRepo;
    }

    public function getAll(): array {
        $rows = $this->sourceRepo->findAll();
        return empty($rows) ? [] : SourceResponse::fromArray($rows);
    }

    public function getById(int $id): ?SourceResponse {
        if ($id <= 0) throw new InvalidArgumentException("Invalid source ID");
        $row = $this->sourceRepo->findById($id);
        return $row ? new SourceResponse($row) : null;
    }

    public function getByDateRange(string $dateFrom, string $dateTo): array {
        $this->validateDateRange($dateFrom, $dateTo);
        $rows = $this->sourceRepo->findByDateRange($dateFrom, $dateTo);
        return empty($rows) ? [] : SourceResponse::fromArray($rows);
    }

    public function getByCampus(int $campusId): array {
        if ($campusId <= 0) throw new InvalidArgumentException("Invalid campus ID");
        $rows = $this->sourceRepo->findByCampus($campusId);
        return empty($rows) ? [] : SourceResponse::fromArray($rows);
    }

    public function getByBankAccount(int $bankAccountId): array {
        if ($bankAccountId <= 0) throw new InvalidArgumentException("Invalid bank account ID");
        $rows = $this->sourceRepo->findByBankAccount($bankAccountId);
        return empty($rows) ? [] : SourceResponse::fromArray($rows);
    }

    public function getTotalByDateRange(string $dateFrom, string $dateTo, int $campusId = null): float {
        $this->validateDateRange($dateFrom, $dateTo);
        if ($campusId !== null && $campusId <= 0) {
            throw new InvalidArgumentException("Invalid campus ID");
        }
        return $this->sourceRepo->getTotalByDateRange($dateFrom, $dateTo, $campusId);
    }

    public function create(array $data, int $createdBy): int {
        if ($createdBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $request = new SourceRequest($data);
        $errors  = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $source = new Source([
            'campus_id'          => $request->campus_id,
            'collection_type_id' => $request->collection_type_id,
            'bank_account_id'    => $request->bank_account_id,
            'amount'             => $request->amount,
            'transaction_date'   => $request->transaction_date,
            'remarks'            => $request->remarks,
            'created_by'         => $createdBy,
        ]);

        $id = $this->sourceRepo->save($source);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $createdBy,
            'action'    => 'CREATE',
            'module'    => 'SOURCES',
            'record_id' => $id,
            'old_value' => null,
            'new_value' => json_encode($source->toArray()),
        ]));

        return $id;
    }

    public function update(int $id, array $data, int $updatedBy): bool {
        if ($id <= 0) throw new InvalidArgumentException("Invalid source ID");
        if ($updatedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $existing = $this->sourceRepo->findById($id);
        if (!$existing) throw new RuntimeException("Source with ID {$id} not found");

        $request = new SourceRequest($data);
        $errors  = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $source = new Source([
            'campus_id'          => $request->campus_id,
            'collection_type_id' => $request->collection_type_id,
            'bank_account_id'    => $request->bank_account_id,
            'amount'             => $request->amount,
            'transaction_date'   => $request->transaction_date,
            'remarks'            => $request->remarks,
        ]);

        $result = $this->sourceRepo->update($id, $source);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $updatedBy,
            'action'    => 'UPDATE',
            'module'    => 'SOURCES',
            'record_id' => $id,
            'old_value' => json_encode($existing),
            'new_value' => json_encode($source->toArray()),
        ]));

        return $result;
    }

    public function delete(int $id, int $deletedBy): bool {
        if ($id <= 0)        throw new InvalidArgumentException("Invalid source ID");
        if ($deletedBy <= 0) throw new InvalidArgumentException("Invalid user ID");

        $existing = $this->sourceRepo->findById($id);
        if (!$existing) throw new RuntimeException("Source with ID {$id} not found");

        $result = $this->sourceRepo->delete($id);

        $this->auditRepo->log(new AuditLog([
            'user_id'   => $deletedBy,
            'action'    => 'DELETE',
            'module'    => 'SOURCES',
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