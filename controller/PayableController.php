<?php
require_once __DIR__ . '/../service/PayableService.php';
require_once __DIR__ . '/../repository/PayableRepository.php';
require_once __DIR__ . '/../repository/AuditLogRepository.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/Database.php';

class PayableController {
    private PayableService $payableService;

    public function __construct() {
        
        if (!can('payables', 'read')) {
            throw new Exception('Access denied');
        }
        
        $db = Database::getInstance()->getConnection();
        $this->payableService = new PayableService(
            new PayableRepository($db),
            new AuditLogRepository($db)
        );
    }

    public function handle(): void {
        $action = $_GET['action'] ?? 'index';

        match($action) {
            'index'  => $this->index(),
            'create' => $this->create(),
            'store'  => $this->store(),
            'edit'   => $this->edit(),
            'update' => $this->update(),
            'delete' => $this->delete(),
            default  => $this->index()
        };
    }

    private function index(): void {
        $campusId = getUserCampusId();
        $user = currentUser(); 
        $role = $user['role'] ?? ''; 

        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo   = $_GET['date_to']   ?? date('Y-m-d');
        
        if ($campusId) {
            $payables = $this->payableService->getByDateRangeAndCampus($dateFrom, $dateTo, $campusId);
            $total = $this->payableService->getTotalByDateRangeAndCampus($dateFrom, $dateTo, $campusId);
        } else {
            $payables = $this->payableService->getByDateRange($dateFrom, $dateTo);
            $total = $this->payableService->getTotalByDateRange($dateFrom, $dateTo);
        }


        $canEdit = can('payables', 'update');
        $canDelete = $role === 'admin'; // Only admin can delete
        $canCreate = can('payables', 'create');
        $isReadOnly = $role === 'auditor';
        
        require_once __DIR__ . '/../views/payables/index.php';
    }

    private function create(): void {
        if (!can('payables', 'create')) {
            throw new Exception('Access denied');
        }
        
        require_once __DIR__ . '/../views/payables/create.php';
    }

    private function store(): void {
        if (!can('payables', 'create')) {
            $this->jsonError('Access denied', 403);
            return;
        }

        try {
            $id = $this->payableService->create($_POST, currentUser()['id']);
            $this->jsonSuccess(['id' => $id, 'message' => 'Payable entry saved successfully.']);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    private function edit(): void {
        if (!can('payables', 'update')) {
            throw new Exception('Access denied');
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) { $this->redirect('payables'); return; }

        try {
            $payable = $this->payableService->getById($id);
            if (!$payable || !is_array($payable)) { $this->redirect('payables'); return; }
            
            $campusId = getUserCampusId();
            if ($campusId && isset($payable['campus_id']) && 
                $payable['campus_id'] !== $campusId) {
                throw new Exception('You can only edit payables from your campus');
            }
            
            require_once __DIR__ . '/../views/payables/edit.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        if (!can('payables', 'update')) {
            $this->jsonError('Access denied', 403);
            return;
        }

        try {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0){
                $this->jsonError('Invalid payable ID.'); 
                return; 
            }

            $payable = $this->payableService->getById($id);
            if (!$payable || !is_array($payable)) {
                $this->jsonError('Payable not found', 404);
                return;
            }

            $campusId = getUserCampusId();
            if ($campusId && isset($payable['campus_id']) && 
                $payable['campus_id'] !== $campusId) {
                $this->jsonError('You can only update payables from your campus', 403);
                return;
            }

            $this->payableService->update($id, $_POST, currentUser()['id']);
            $this->jsonSuccess(['message' => 'Payable entry updated successfully.']);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage(), 404);
        } catch (Exception $e) {
            $this->jsonError('Failed to update payable entry.');
        }
    }

    private function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        // Only admin can delete
        if (!hasRole('admin')) {
            $this->jsonError('Only administrators can delete financial records', 403);
            return;
        }

        try {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) { 
                $this->jsonError('Invalid payable ID.'); 
                return; 
            }
            
            $payable = $this->payableService->getById($id);
            if (!$payable) {
                $this->jsonError('Payable not found', 404);
                return;
            }

            $this->payableService->delete($id, currentUser()['id']);
            $this->jsonSuccess(['message' => 'Payable entry deleted.']);
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage(), 404);
        } catch (Exception $e) {
            $this->jsonError('Failed to delete payable entry.');
        }
    }

    private function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function jsonSuccess(array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    private function jsonError(string $message, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => false], ['message' => $message]));
        exit;
    }

    private function handleError(Exception $e): void {
        http_response_code(500);
        require_once __DIR__ . '/../views/error.php';
        exit;
    }

    private function redirect(string $page): void {
        header("Location: /sjfs/?page={$page}");
        exit;
    }
}