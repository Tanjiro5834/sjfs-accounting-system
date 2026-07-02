<?php
require_once 'interfaces/ReportStrategyInterface.php';
require_once 'interfaces/PayableRepositoryInterface.php';

class CashFlowReportStrategy implements ReportStrategyInterface {
    private SourceRepositoryInterface $sourceRepo;
    private PayableRepositoryInterface $payableRepo;

    public function __construct(SourceRepositoryInterface $sourceRepo, PayableRepositoryInterface $payableRepo) {
        $this->sourceRepo = $sourceRepo;
        $this->payableRepo = $payableRepo;
    }

    public function generate(string $dateFrom, string $dateTo, array $options = []): array {
        if (empty($dateFrom) || empty($dateTo)) {
            throw new InvalidArgumentException("Date range is required");
        }
        
        if (strtotime($dateFrom) > strtotime($dateTo)) {
            throw new InvalidArgumentException("dateFrom cannot be after dateTo");
        }

        $campusId = isset($options['campus_id']) && $options['campus_id'] > 0 ? (int) $options['campus_id'] : null;

        $sources  = $this->sourceRepo->findByDateRange($dateFrom, $dateTo);
        $payables = $this->payableRepo->findByDateRange($dateFrom, $dateTo);

        if ($campusId !== null) {
            $sources = array_filter($sources, fn($s) => (int) $s['campus_id'] === $campusId);
            $sources = array_values($sources);
        }

        $totalSources = array_sum(array_column($sources, 'amount'));
        $totalPayables = array_sum(array_column($payables, 'amount'));
        $netCashFlow = $totalSources - $totalPayables;

        $groupedSources = [];
        foreach ($sources as $source) {
            $key = $source['campus_name'] . ' - ' . $source['type_code'];
            if (!isset($groupedSources[$key])) {
                $groupedSources[$key] = [
                    'label'  => $key,
                    'amount' => 0.00,
                    'count'  => 0,
                ];
            }

            $groupedSources[$key]['amount'] += (float) $source['amount'];
            $groupedSources[$key]['count']++;
        }

        return [
            'type'           => 'CASH_FLOW',
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
            'campus_id'      => $campusId,
            'sources'        => array_values($sources),
            'payables'       => array_values($payables),
            'grouped_sources'=> array_values($groupedSources),
            'total_sources'  => round($totalSources, 2),
            'total_payables' => round($totalPayables, 2),
            'net_cash_flow'  => round($netCashFlow, 2),
            'is_positive'    => $netCashFlow >= 0,
            'generated_at'   => date('Y-m-d H:i:s'),
        ];
    }
}