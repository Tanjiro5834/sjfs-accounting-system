<?php
class CashflowController {
    public function __construct(private CashflowService $service) {}

    public function handle(): void {
        $filters = [
            'date_from'       => $_GET['date_from'] ?? null,
            'date_to'         => $_GET['date_to'] ?? null,
            'type'            => $_GET['type'] ?? null,
            'bank_account_id' => $_GET['bank_account_id'] ?? null,
        ];

        $transactions = $this->service->list($filters);
        $summary      = $this->service->getSummary($filters);
        $categories   = $this->service->getCategories(); // bank accounts, for filter

        require __DIR__ . '/../views/cashflow.php';
    }
}