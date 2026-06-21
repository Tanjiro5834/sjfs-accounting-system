<?php
interface SourceRepositoryInterface {
    public function findAll(): array;
    public function findById(int $id): ?array;
    public function findByDateRange(string $dateFrom, string $dateTo): array;
    public function findByCampus(int $campusId): array;
    public function findByBankAccount(int $bankAccountId): array;
    public function save(Source $source): int;
    public function update(int $id, Source $source): bool;
    public function delete(int $id): bool;
    public function getTotalByDateRange(string $dateFrom, string $dateTo, int $campusId = null): float;
}