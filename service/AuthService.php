<?php
class AuthService {
    private UserRepositoryInterface $userRepo;

    private const ALLOWED_ROLES = ['admin', 'accountant', 'cashier', 'auditor'];

    public function __construct() {
        $this->userRepo = new UserRepository();
    }

    public function login(string $email, string $password, string $role): array {
        if (empty($email))    throw new InvalidArgumentException("Email is required");
        if (empty($password)) throw new InvalidArgumentException("Password is required");
        if (empty($role))     throw new InvalidArgumentException("Role is required");

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        if (!in_array(strtolower($role), self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException("Invalid role selected");
        }

        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            throw new RuntimeException("Invalid email or password");
        }

        if (!password_verify($password, $user['password'])) {
            throw new RuntimeException("Invalid email or password");
        }

        if ((int) $user['is_active'] !== 1) {
            throw new RuntimeException("Your account has been deactivated. Contact the administrator.");
        }

        if ($user['role'] !== strtolower($role)) {
            throw new RuntimeException("Invalid email or password");
        }

        unset($user['password']);

        return $user;
    }

    public function getCurrentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['user']);
    }

    public function hasRole(string ...$roles): bool {
        $current = $this->getCurrentUser();
        if (!$current) return false;
        return in_array($current['role'], $roles, true);
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public function hashPassword(string $password): string {
        if (strlen($password) < 8) {
            throw new InvalidArgumentException("Password must be at least 8 characters");
        }
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}