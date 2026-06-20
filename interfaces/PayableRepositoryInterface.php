<?php
interface PayableRepositoryInterface {
    public function findAll(): array;
    public function findById(int $id): ?array;
    public function findByDateRange(string $dateFrom, string $dateTo): array;
    public function findByBankAccount(int $bankAccountId): array;
    public function findByPayee(string $payee): array;
    public function findByCheckNumber(string $checkNumber): ?array;
    public function save(Payable $payable): int;
    public function update(int $id, Payable $payable): bool;
    public function delete(int $id): bool;
    public function getTotalByDateRange(string $dateFrom, string $dateTo): float;
}