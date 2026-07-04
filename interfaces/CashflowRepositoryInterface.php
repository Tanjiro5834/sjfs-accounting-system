<?php
// interfaces/CashflowRepositoryInterface.php
interface CashflowRepositoryInterface {
    public function findAll(array $filters = []): array;
    public function getTotalsByType(array $filters = []): array; // ['income' => x, 'expense' => y]
    public function getRunningBalance(int $bankAccountId): float;
}