<?php
class CashflowService {
    public function __construct(
        private CashflowRepositoryInterface $cashflowRepo,
        private BankAccountRepositoryInterface $bankAccountRepo
    ) {}

    public function list(array $filters = []): array {
        return $this->cashflowRepo->findAll($filters);
    }

    public function getSummary(array $filters = []): array {
        $totals = $this->cashflowRepo->getTotalsByType($filters);
        return [
            'total_income'  => $totals['income'],
            'total_expense' => $totals['expense'],
            'balance'       => $totals['income'] - $totals['expense'],
        ];
    }

    public function getBankBalance(int $bankAccountId): float {
        return $this->cashflowRepo->getRunningBalance($bankAccountId);
    }

    public function getCategories(): array {
        // for the filter dropdown in the view
        return $this->bankAccountRepo->findAll();
    }
}