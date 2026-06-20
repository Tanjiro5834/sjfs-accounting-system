<?php
require_once 'interfaces/ReportStrategyInterface.php';
require_once 'interfaces/BankAccountRepositoryInterface.php';

class ReconciliationReportStrategy implements ReportStrategyInterface {
    private BankAccountRepositoryInterface $bankRepo;

    public function __construct(BankAccountRepositoryInterface $bankRepo) {
        $this->bankRepo = $bankRepo;
    }

    public function generate(string $dateFrom, string $dateTo, array $options = []): array {
        if (empty($dateFrom) || empty($dateTo)) {
            throw new InvalidArgumentException("Date range is required");
        }
        if (strtotime($dateFrom) > strtotime($dateTo)) {
            throw new InvalidArgumentException("dateFrom cannot be after dateTo");
        }

        $accounts = $this->bankRepo->getBalanceSummary($dateFrom, $dateTo);
        if (empty($accounts)) return [
            'type'           => 'RECONCILIATION',
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
            'accounts'       => [],
            'total_opening'  => 0.00,
            'total_sources'  => 0.00,
            'total_payables' => 0.00,
            'total_ending'   => 0.00,
            'generated_at'   => date('Y-m-d H:i:s'),
        ];

        $totalOpening  = 0.00;
        $totalSources  = 0.00;
        $totalPayables = 0.00;
        $totalEnding   = 0.00;

        $processedAccounts = array_map(function ($account) use (
            &$totalOpening, &$totalSources, &$totalPayables, &$totalEnding
        ) {
            $opening  = (float) ($account['opening_balance'] ?? 0);
            $sources  = (float) ($account['total_sources']   ?? 0);
            $payables = (float) ($account['total_payables']  ?? 0);
            $ending   = $opening + $sources - $payables;

            $totalOpening  += $opening;
            $totalSources  += $sources;
            $totalPayables += $payables;
            $totalEnding   += $ending;

            return [
                'id'              => $account['id'],
                'account_name'    => $account['account_name'],
                'bank_name'       => $account['bank_name'],
                'opening_balance' => round($opening, 2),
                'total_sources'   => round($sources, 2),
                'total_payables'  => round($payables, 2),
                'ending_balance'  => round($ending, 2),
                'is_positive'     => $ending >= 0,
            ];
        }, $accounts);

        return [
            'type'           => 'RECONCILIATION',
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
            'accounts'       => $processedAccounts,
            'total_opening'  => round($totalOpening, 2),
            'total_sources'  => round($totalSources, 2),
            'total_payables' => round($totalPayables, 2),
            'total_ending'   => round($totalEnding, 2),
            'is_positive'    => $totalEnding >= 0,
            'generated_at'   => date('Y-m-d H:i:s'),
        ];
    }
}