<?php
interface SourceRepositoryInterface{
    public function save(Source $source): int;
    public function findById(int $id): ?Source;
    public function findByDateRange(string $dateFrom, string $dateTo): array;
    public function findByCampus(int $campusId): array;
    public function findByBankAccount(int $bankAccountId): array;
    public function getTotalByDateRange(string $dateFrom, string $dateTo, int $campusId = null): float;
}