<?php
class AuditLogResponse {
    public int $id;
    public string $user_name;
    public string $action;
    public string $module;
    public int $record_id;
    public ?array $old_value;
    public ?array $new_value;
    public ?string $ip_address;
    public string $created_at;

    public function __construct(array $row) {
        $this->id         = (int) $row['id'];
        $this->user_name  = $row['user_name'];
        $this->action     = $row['action'];
        $this->module     = $row['module'];
        $this->record_id  = (int) $row['record_id'];
        $this->old_value  = isset($row['old_value']) ? json_decode($row['old_value'], true) : null;
        $this->new_value  = isset($row['new_value']) ? json_decode($row['new_value'], true) : null;
        $this->ip_address = $row['ip_address'] ?? null;
        $this->created_at = $row['created_at'];
    }

    public static function fromArray(array $rows): array {
        return array_map(fn($row) => new self($row), $rows);
    }
}