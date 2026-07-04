<?php
function requireAuth(): void {
    if (!isset($_SESSION['user'])) {
        header('Location: /sjfs/index.php?page=login');
        exit;
    }
}

function requireRole(string ...$roles): void {
    if (!in_array(currentUser()['role'], $roles, true)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
}

function hasRole(string ...$roles): bool {
    $user = $_SESSION['user'] ?? null;
    if (!$user) return false;
    return in_array($user['role'], $roles, true);
}

function hasAnyRole(array $roles): bool {
    $user = currentUser();
    return $user && in_array($user['role'], $roles, true);
}

function currentUser(): array {
    return $_SESSION['user'] ?? [];
}

function hasCampusRestriction(): bool {
    $user = currentUser();
    return $user && in_array($user['role'], ['cashier', 'auditor']);
}

function getUserCampusId(): ?int {
    $user = currentUser();
    if (!$user) return null;
    
    // Cashiers and Auditors are restricted to their campus
    if (in_array($user['role'], ['cashier', 'auditor'])) {
        return $user['campus_id'] ?? null;
    }
    
    // Admin and Accountant see all
    return null;
}

function authorize(string $module, string $action): void {
    if (!can($module, $action)) {
        http_response_code(403);
        throw new Exception('Access denied');
    }
}

function can(string $module, string $action): bool {
    $user = currentUser();
    if (!$user) return false;
    
    $role = $user['role'];
    
    // Admin can do everything
    if ($role === 'admin') return true;
    
    // Define permissions per role
    $permissions = [
        'accountant' => [
            'sources' => ['read', 'create', 'update'],
            'payables' => ['read', 'create', 'update'],
            'reports' => ['reconciliation'],
            'dashboard' => ['read'],
        ],
        'cashier' => [
            'sources' => ['read', 'create'], // Own campus only
            'payables' => ['read', 'create'], // Own campus only
            'dashboard' => ['read'],
        ],
        'auditor' => [
            'sources' => ['read'],
            'payables' => ['read'],
            'reports' => ['cashflow', 'reconciliation'],
            'audit' => ['read'],
            'dashboard' => ['read'],
        ],
    ];
    
    // Check if role has access to this module
    if (!isset($permissions[$role][$module])) {
        return false;
    }
    
    // Check if action is allowed
    return in_array($action, $permissions[$role][$module], true);
}


