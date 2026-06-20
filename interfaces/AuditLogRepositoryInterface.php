<?php
interface AuditLogRepositoryInterface {
    public function findAll(): array;
    public function findByUser(int $userId): array;
    public function findByModule(string $module): array;
    public function findByDateRange(string $dateFrom, string $dateTo): array;
    public function findByAction(string $action): array;
    public function log(AuditLog $auditLog): void;
}