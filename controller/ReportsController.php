<?php
require_once __DIR__ . '/../vendor/autoload.php';

class ReportsController {
    private array $strategies;

    public function __construct(
        CashFlowReportStrategy $cashFlowStrategy,
        ReconciliationReportStrategy $reconciliationStrategy
    ) {
        $this->strategies = [
            'cashflow'       => $cashFlowStrategy,
            'reconciliation' => $reconciliationStrategy,
        ];
    }

    public function handle(): void {
        $action = $_GET['action'] ?? '';

        if (!isset($this->strategies[$action])) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo   = $_GET['date_to']   ?? date('Y-m-d');
        $options  = ['campus_id' => $_GET['campus_id'] ?? null];

        try {
            $report = $this->strategies[$action]->generate($dateFrom, $dateTo, $options);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo htmlspecialchars($e->getMessage());
            return;
        }

        if (($_GET['format'] ?? '') === 'pdf') {
            $this->renderPdf($action, $report, "{$action}-report_{$dateFrom}_to_{$dateTo}.pdf");
            return;
        }

        $viewMap = [
            'cashflow'       => __DIR__ . '/../views/reports/cashflow.php',
            'reconciliation' => __DIR__ . '/../views/reports/reconciliation.php',
        ];

        require $viewMap[$action];
    }

    private function renderPdf(string $action, array $report, string $filename): void {
        $pdfViewMap = [
            'cashflow'       => __DIR__ . '/../views/reports/pdf/cashflowreport.php',
            'reconciliation' => __DIR__ . '/../views/reports/pdf/reconciliation.php',
        ];

        ob_start();
        require $pdfViewMap[$action];
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->setChroot(realpath(__DIR__ . '/..'));

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}