<?php
require_once 'repository/AuditLogRepository.php';
require_once 'entity/AuditLog.php';

class AuditLogService {
    private AuditLogRepository $auditRepo;

    public function __construct() {
        $this->auditRepo = new AuditLogRepository();
    }

    public function getAll(): array {
        return $this->auditRepo->findAll();
    }

    public function getByUser(int $userId): array {
        return $this->auditRepo->findByUser($userId);
    }

    public function getByModule(string $module): array {
        return $this->auditRepo->findByModule($module);
    }

    public function getByDateRange(string $dateFrom, string $dateTo): array {
        return $this->auditRepo->findByDateRange($dateFrom, $dateTo);
    }

    public function getByAction(string $action): array {
        return $this->auditRepo->findByAction($action);
    }

    public function log(
        int $userId,
        string $action,
        string $module,
        int $recordId,
        ?array $oldValue = null,
        ?array $newValue = null
    ): void {
        try {
            $auditLog = new AuditLog();
            $auditLog->user_id   = $userId;
            $auditLog->action    = $action;
            $auditLog->module    = $module;
            $auditLog->record_id = $recordId;
            $auditLog->old_value = $oldValue;
            $auditLog->new_value = $newValue;
            $auditLog->ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

            $this->auditRepo->log($auditLog);

        } catch (Exception $e) {
            throw new RuntimeException("Audit log failed: " . $e->getMessage(), 0, $e);
        }
    }

    public function getAllPaginated(int $page = 1, int $perPage = 20): array {
        $total = $this->auditRepo->countAll();
        $data  = $this->auditRepo->findAllPaginated($page, $perPage);

        return [
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}