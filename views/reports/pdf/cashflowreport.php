<?php
// views/reports/pdf/cashflow.php
// Expects: $report (array from CashFlowReportStrategy::generate())
?>
<!DOCTYPE html>
<html>
<head>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1A1917; }
  h1 { font-size: 16px; margin-bottom: 2px; }
  .subtitle { color: #6B6860; margin-bottom: 16px; }
  .stats { width: 100%; margin-bottom: 20px; }
  .stats td { border: 1px solid #E4E2DC; padding: 8px 12px; width: 33%; }
  .stat-label { font-size: 9px; text-transform: uppercase; color: #6B6860; display: block; }
  .stat-value { font-size: 14px; font-weight: bold; }
  table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  table.data th, table.data td { border: 1px solid #E4E2DC; padding: 6px 8px; text-align: left; font-size: 10px; }
  table.data th { background: #F2F1EE; text-transform: uppercase; font-size: 9px; }
  .positive { color: #1A5C3A; }
  .negative { color: #C0392B; }
  h3 { font-size: 12px; margin-top: 16px; margin-bottom: 6px; }
</style>
</head>
<body>
  <h1>St. John Fisher School — Cash Flow Report</h1>
  <div class="subtitle"><?= htmlspecialchars($report['date_from']) ?> to <?= htmlspecialchars($report['date_to']) ?></div>

  <table class="stats">
    <tr>
      <td><span class="stat-label">Total Sources</span><span class="stat-value positive">₱<?= number_format($report['total_sources'], 2) ?></span></td>
      <td><span class="stat-label">Total Payables</span><span class="stat-value negative">₱<?= number_format($report['total_payables'], 2) ?></span></td>
      <td><span class="stat-label">Net Cash Flow</span><span class="stat-value <?= $report['is_positive'] ? 'positive' : 'negative' ?>">₱<?= number_format($report['net_cash_flow'], 2) ?></span></td>
    </tr>
  </table>

  <h3>Sources by Campus / Type</h3>
  <table class="data">
    <thead><tr><th>Group</th><th>Count</th><th>Amount</th></tr></thead>
    <tbody>
      <?php if (empty($report['grouped_sources'])): ?>
        <tr><td colspan="3">No sources found.</td></tr>
      <?php else: ?>
        <?php foreach ($report['grouped_sources'] as $g): ?>
          <tr>
            <td><?= htmlspecialchars($g['label']) ?></td>
            <td><?= $g['count'] ?></td>
            <td>₱<?= number_format($g['amount'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <h3>Sources (Income)</h3>
  <table class="data">
    <thead><tr><th>Date</th><th>Campus</th><th>Type</th><th>Bank</th><th>Amount</th><th>Remarks</th></tr></thead>
    <tbody>
      <?php if (empty($report['sources'])): ?>
        <tr><td colspan="6">No records.</td></tr>
      <?php else: ?>
        <?php foreach ($report['sources'] as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['transaction_date']) ?></td>
            <td><?= htmlspecialchars($s['campus_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['type_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['bank_name'] ?? '') ?></td>
            <td class="positive">+₱<?= number_format($s['amount'], 2) ?></td>
            <td><?= htmlspecialchars($s['remarks'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <h3>Payables (Expense)</h3>
  <table class="data">
    <thead><tr><th>Date</th><th>Payee</th><th>Check #</th><th>Amount</th><th>Remarks</th></tr></thead>
    <tbody>
      <?php if (empty($report['payables'])): ?>
        <tr><td colspan="5">No records.</td></tr>
      <?php else: ?>
        <?php foreach ($report['payables'] as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['transaction_date']) ?></td>
            <td><?= htmlspecialchars($p['payee']) ?></td>
            <td><?= htmlspecialchars($p['check_number'] ?? '') ?></td>
            <td class="negative">-₱<?= number_format($p['amount'], 2) ?></td>
            <td><?= htmlspecialchars($p['remarks'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div style="margin-top:20px;font-size:9px;color:#9E9C96">Generated <?= date('F j, Y g:i A') ?></div>
</body>
</html>