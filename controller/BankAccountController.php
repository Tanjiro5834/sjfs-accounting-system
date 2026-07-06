<?php
class BankAccountController {
    private BankAccountService $bankService;

    public function __construct() {
        requireRole('admin');
        $this->bankService = new BankAccountService(
            new BankAccountRepository(),
            new AuditLogRepository()
        );
    }

    public function handle(): void {
        $action = $_GET['action'] ?? 'index';

        match($action){
            'index'      => $this->index(),
            'create'     => $this->create(),
            'store'      => $this->store(),
            'edit'       => $this->edit(),
            'update'     => $this->update(),
            'deactivate' => $this->deactivate(),
            'balance'    => $this->balance(),
            default      => $this->index()
        };
    }

    private function index(): void {
        $pageNum = max(1, (int) ($_GET['p'] ?? 1));

        try {
            $result         = $this->bankService->getAllPaginated($pageNum, 20);
            $accounts       = $result['data'];
            $currentPageNum = $result['page'];
            $totalPages     = $result['total_pages'];
            require_once __DIR__ . '/../views/banks/index.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function create(): void {
        require_once __DIR__ . '/../views/banks/create.php';
    }

    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        try {
            $id = $this->bankService->create($_POST, currentUser()['id']);
            $this->jsonSuccess(['id' => $id, 'message' => 'Bank account added successfully.']);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    private function edit(): void {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) { $this->redirect('banks'); return; }

        try {
            $account = $this->bankService->getById($id);
            if (!$account) { $this->redirect('banks'); return; }
            require_once __DIR__ . '/../views/banks/edit.php';
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
        if ($id <= 0) { $this->jsonError('Invalid bank account ID.'); return; }

        try {
            $this->bankService->update($id, $_POST, currentUser()['id']);
            $this->jsonSuccess(['message' => 'Bank account updated successfully.']);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage(), 404);
        } catch (Exception $e) {
            $this->jsonError('Failed to update bank account.');
        }
    }

    private function deactivate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { $this->jsonError('Invalid bank account ID.'); return; }

        try {
            $this->bankService->deactivate($id, currentUser()['id']);
            $this->jsonSuccess(['message' => 'Bank account deactivated.']);
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage(), 404);
        } catch (Exception $e) {
            $this->jsonError('Failed to deactivate bank account.');
        }
    }

    private function balance(): void {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo   = $_GET['date_to']   ?? date('Y-m-d');

        try {
            $summary = $this->bankService->getBalanceSummary($dateFrom, $dateTo);
            $this->jsonSuccess(['data' => $summary]);
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch balance summary.');
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