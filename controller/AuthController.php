<?php
require_once __DIR__ . '/../service/AuthService.php';

class AuthController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function handle(): void {
        $action = $_GET['action'] ?? 'showLogin';

        match($action) {
            'showLogin'  => $this->showLogin(),
            'login'      => $this->login(),
            'logout'     => $this->logout(),
            default      => $this->showLogin()
        };
    }

    private function showLogin(): void {
        if (isset($_SESSION['user'])) {
            header('Location: /sjfs/?page=dashboard');
            exit;
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }

    private function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = trim($_POST['role']     ?? '');

        if (empty($email) || empty($password) || empty($role)) {
            $this->jsonError('All fields are required.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonError('Invalid email address.');
            return;
        }

        try {
            $user = $this->authService->login($email, $password, $role);
            $_SESSION['user'] = $user;

            $this->jsonSuccess(['redirect' => '/sjfs/?page=dashboard']);

        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage());
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->jsonError('Something went wrong. Please try again.');
        }
    }

    private function logout(): void {
        session_destroy();
        header('Location: /sjfs/?page=login');
        exit;
    }

    private function jsonSuccess(array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    private function jsonError(string $message, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => false, 'message' => $message], []));
        exit;
    }
}