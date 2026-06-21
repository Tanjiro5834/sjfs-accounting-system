<?php
interface BankAccountRepositoryInterface {
    public function findAll(bool $activeOnly = true): array;
    public function findById(int $id): ?array;
    public function findByCampus(int $campusId): array;
    public function save(BankAccount $bankAccount): int;
    public function update(int $id, BankAccount $bankAccount): bool;
    public function deactivate(int $id): bool;
    public function getBalanceSummary(string $dateFrom, string $dateTo): array;
    public function getBalanceById(int $id, string $dateFrom, string $dateTo): array;
}