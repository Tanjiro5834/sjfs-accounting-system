<?php
require_once __DIR__ . '/../utils/AutoModel.php';

class User {
    use AutoModel;

    private ?int $id = null;
    private string $name;
    private string $email;
    private string $password;
    private string $role;        // 'admin','accountant','cashier','auditor'
    private ?int $campus_id = null;
    private int $is_active = 1;
    private ?string $created_at = null;
}