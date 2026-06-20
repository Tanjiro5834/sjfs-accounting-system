<?php
require_once __DIR__ . '/../servicesPayableService.php';
require_once __DIR__ . '/../repository/PayableRepository.php';
require_once __DIR__ . '/../middleware/auth.php';

class PayableController {
    private PayableService $payableService;

    public function __construct() {
        requireRole('admin', 'accountant');
        $this->payableService = new PayableService(
            new PayableRepository(),
            new AuditLogRepository()
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
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo   = $_GET['date_to']   ?? date('Y-m-d');

        try {
            $payables = $this->payableService->getByDateRange($dateFrom, $dateTo);
            $total    = $this->payableService->getTotalByDateRange($dateFrom, $dateTo);
            require_once __DIR__ . '/../views/payables/index.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function create(): void {
        require_once __DIR__ . '/../views/payables/create.php';
    }

    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        try {
            $id = $this->payableService->create($_POST, currentUser()['id']);
            $this->jsonSuccess(['id' => $id, 'message' => 'Payable entry saved successfully.']);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->jsonError('Failed to save payable entry.');
        }
    }

    private function edit(): void {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) { $this->redirect('payables'); return; }

        try {
            $payable = $this->payableService->getById($id);
            if (!$payable) { $this->redirect('payables'); return; }
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

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { $this->jsonError('Invalid payable ID.'); return; }

        try {
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

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { $this->jsonError('Invalid payable ID.'); return; }

        try {
            $this->payableService->delete($id, currentUser()['id']);
            $this->jsonSuccess(['message' => 'Payable entry deleted.']);
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage(), 404);
        } catch (Exception $e) {
            $this->jsonError('Failed to delete payable entry.');
        }
    }

    private function jsonSuccess(array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, ...$data]);
        exit;
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

    private function redirect(string $page): void {
        header("Location: /sjfs/?page={$page}");
        exit;
    }
}