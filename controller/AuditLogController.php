<?php
require_once __DIR__ . '/../service/AuditLogService.php';
require_once __DIR__ . '/../middleware/auth.php';

class AuditController {
    private AuditLogService $auditService;

    public function __construct() {
        requireRole('admin', 'auditor');
        $this->auditService = new AuditLogService(
            new AuditLogRepository()
        );
    }

    public function handle(): void {
        $action = $_GET['action'] ?? 'index';

        match($action) {
            'index'  => $this->index(),
            'filter' => $this->filter(),
            default  => $this->index()
        };
    }

    private function index(): void {
        try {
            $logs = $this->auditService->getAll();
            require_once __DIR__ . '/../views/audit/index.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function filter(): void {
        $userId   = isset($_GET['user_id'])  && $_GET['user_id']  > 0 ? (int) $_GET['user_id']  : null;
        $module   = $_GET['module']   ?? null;
        $action   = $_GET['action_type']   ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo   = $_GET['date_to']   ?? null;

        try {
            $logs = match(true) {
                $userId   !== null => $this->auditService->getByUser($userId),
                $module   !== null => $this->auditService->getByModule($module),
                $action   !== null => $this->auditService->getByAction($action),
                $dateFrom !== null => $this->auditService->getByDateRange($dateFrom, $dateTo ?? date('Y-m-d')),
                default            => $this->auditService->getAll()
            };
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $logs]);
            exit;
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch audit logs.');
        }
    }

    private function jsonError(string $message, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    private function handleError(Exception $e): void {
        http_response_code(500);
        require_once __DIR__ . '/../views/error.php';
        exit;
    }
}