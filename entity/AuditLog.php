<?php
require_once __DIR__ . '/../utils/AutoModel.php';
class AuditLog {
    use AutoModel;

    private ?int $id = null;
    private int $user_id;
    private string $action;
    private string $module;
    private int $record_id;
    private mixed $old_value = null;
    private mixed $new_value = null;
    private ?string $ip_address = null;
    private ?string $created_at = null;
}