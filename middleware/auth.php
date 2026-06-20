<?php
function requireAuth(): void {
    if (!isset($_SESSION['user'])) {
        header('Location: /sjfs/index.php?page=login');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireAuth();
    if (!in_array($_SESSION['user']['role'], $roles)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function hasRole(string ...$roles): bool {
    $user = $_SESSION['user'] ?? null;
    if (!$user) return false;
    return in_array($user['role'], $roles, true);
}

function currentUser(): array {
    return $_SESSION['user'] ?? [];
}