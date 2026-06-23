<?php
require_once __DIR__ . '/../service/SourceService.php';
require_once __DIR__ . '/../middleware/auth.php';

class SourceController {
    private SourceService $sourceService;

    public function __construct() {
        requireRole('admin', 'accountant', 'cashier');
        $this->sourceService = new SourceService(
            new SourceRepository(),
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
        $campusId = $_GET['campus_id'] ?? null;

        try {
            $sources = $this->sourceService->getByDateRange($dateFrom, $dateTo);
            $total   = $this->sourceService->getTotalByDateRange($dateFrom, $dateTo, $campusId ? (int) $campusId : null);
            require_once __DIR__ . '/../views/sources/index.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function create(): void {
        require_once __DIR__ . '/../views/sources/create.php';
    }

    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        try {
            $id = $this->sourceService->create($_POST, currentUser()['id']);
            $this->jsonSuccess(['id' => $id, 'message' => 'Source entry saved successfully.']);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->jsonError('Failed to save source entry.');
        }
    }

    private function edit(): void {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) { $this->redirect('sources'); return; }

        try {
            $source = $this->sourceService->getById($id);
            if (!$source) { $this->redirect('sources'); return; }
            require_once __DIR__ . '/../views/sources/edit.php';
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
        if ($id <= 0) { $this->jsonError('Invalid source ID.'); return; }

        try {
            $this->sourceService->update($id, $_POST, currentUser()['id']);
            $this->jsonSuccess(['message' => 'Source entry updated successfully.']);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage(), 404);
        } catch (Exception $e) {
            $this->jsonError('Failed to update source entry.');
        }
    }

    private function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { $this->jsonError('Invalid source ID.'); return; }

        try {
            $this->sourceService->delete($id, currentUser()['id']);
            $this->jsonSuccess(['message' => 'Source entry deleted.']);
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage(), 404);
        } catch (Exception $e) {
            $this->jsonError('Failed to delete source entry.');
        }
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