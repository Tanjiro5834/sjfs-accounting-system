<?php
$pageTitle = 'Dashboard — SJFS';
$dateFrom  = date('Y-m-01');
$dateTo    = date('Y-m-d');
$user      = currentUser();

// fetch summary data
$sourceRepo  = new SourceRepository();
$payableRepo = new PayableRepository();
$bankRepo    = new BankAccountRepository();

$totalSources   = $sourceRepo->getTotalByDateRange($dateFrom, $dateTo);
$totalPayables  = $payableRepo->getTotalByDateRange($dateFrom, $dateTo);
$netCashFlow    = $totalSources - $totalPayables;
$bankSummary    = $bankRepo->getBalanceSummary($dateFrom, $dateTo);
$recentSources  = array_slice($sourceRepo->findByDateRange($dateFrom, $dateTo), 0, 5);
$recentPayables = array_slice($payableRepo->findByDateRange($dateFrom, $dateTo), 0, 5);
?>
<?php require_once __DIR__ . '/layout/header.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.php'; ?>

<div class="page-header animate-in">
    <div class="page-header-left">
        <h1>Dashboard</h1>
        <p>Overview for <?= date('F Y') ?></p>
    </div>
    <div class="page-header-right">
        <span class="topbar-date">
            <i class="ti ti-calendar"></i>
            <?= date('F d, Y') ?>
        </span>
    </div>
</div>

<!-- STAT CARDS -->
<div class="stats-grid animate-in">
    <div class="stat-card">
        <div class="stat-label">Total cash in</div>
        <div class="stat-value stat-positive">
            ₱<?= number_format($totalSources, 2) ?>
        </div>
        <div class="stat-sub">This month</div>
        <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $totalSources > 0 ? min(100, ($totalSources / max($totalSources, $totalPayables)) * 100) : 0 ?>%"></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total cash out</div>
        <div class="stat-value stat-negative">
            ₱<?= number_format($totalPayables, 2) ?>
        </div>
        <div class="stat-sub">This month</div>
        <div class="progress-bar">
            <div class="progress-fill" style="background:var(--danger);width:<?= $totalPayables > 0 ? min(100, ($totalPayables / max($totalSources, $totalPayables)) * 100) : 0 ?>%"></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Net cash flow</div>
        <div class="stat-value <?= $netCashFlow >= 0 ? 'stat-positive' : 'stat-negative' ?>">
            ₱<?= number_format(abs($netCashFlow), 2) ?>
        </div>
        <div class="stat-sub">
            <?= $netCashFlow >= 0 ? 'Positive' : 'Negative' ?> this month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Bank accounts</div>
        <div class="stat-value stat-neutral"><?= count($bankSummary) ?></div>
        <div class="stat-sub">Active accounts</div>
    </div>
</div>

<!-- RECENT TRANSACTIONS -->
<div class="two-col animate-in" style="margin-bottom:20px">

    <!-- Recent Sources -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Recent cash in</div>
                <div class="card-subtitle">Latest source entries</div>
            </div>
            <a href="/sjfs/?page=sources" class="btn btn-sm">
                <i class="ti ti-arrow-right"></i> View all
            </a>
        </div>
        <?php if (empty($recentSources)): ?>
            <div class="empty-state">
                <i class="ti ti-inbox"></i>
                <h3>No entries yet</h3>
                <p>No sources recorded this month.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Campus</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSources as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['campus_name']) ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= htmlspecialchars($s['type_code']) ?>
                                    </span>
                                </td>
                                <td class="td-mono amount-positive">
                                    ₱<?= number_format($s['amount'], 2) ?>
                                </td>
                                <td class="td-muted">
                                    <?= date('M d', strtotime($s['transaction_date'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Payables -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Recent cash out</div>
                <div class="card-subtitle">Latest payable entries</div>
            </div>
            <a href="/sjfs/?page=payables" class="btn btn-sm">
                <i class="ti ti-arrow-right"></i> View all
            </a>
        </div>
        <?php if (empty($recentPayables)): ?>
            <div class="empty-state">
                <i class="ti ti-inbox"></i>
                <h3>No entries yet</h3>
                <p>No payables recorded this month.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Payee</th>
                            <th>Bank</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPayables as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['payee']) ?></td>
                                <td class="td-muted">
                                    <?= htmlspecialchars($p['bank_name']) ?>
                                </td>
                                <td class="td-mono amount-negative">
                                    ₱<?= number_format($p['amount'], 2) ?>
                                </td>
                                <td class="td-muted">
                                    <?= date('M d', strtotime($p['transaction_date'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- BANK BALANCE SUMMARY -->
<div class="card animate-in">
    <div class="card-header">
        <div>
            <div class="card-title">Bank balance summary</div>
            <div class="card-subtitle">
                <?= date('F 1', strtotime($dateFrom)) ?> &mdash; <?= date('F d, Y', strtotime($dateTo)) ?>
            </div>
        </div>
        <a href="/sjfs/?page=reports&action=reconciliation" class="btn btn-sm">
            <i class="ti ti-scale"></i> Full report
        </a>
    </div>
    <?php if (empty($bankSummary)): ?>
        <div class="empty-state">
            <i class="ti ti-building-bank"></i>
            <h3>No bank accounts</h3>
            <p>Add bank accounts to see balance summary.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Bank</th>
                        <th>Opening</th>
                        <th>Cash in</th>
                        <th>Cash out</th>
                        <th>Ending balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bankSummary as $b): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['account_name']) ?></strong></td>
                            <td class="td-muted"><?= htmlspecialchars($b['bank_name']) ?></td>
                            <td class="td-mono">₱<?= number_format($b['opening_balance'], 2) ?></td>
                            <td class="td-mono amount-positive">+₱<?= number_format($b['total_sources'], 2) ?></td>
                            <td class="td-mono amount-negative">-₱<?= number_format($b['total_payables'], 2) ?></td>
                            <td class="td-mono">
                                <span class="<?= $b['ending_balance'] >= 0 ? 'amount-positive' : 'amount-negative' ?>">
                                    ₱<?= number_format($b['ending_balance'], 2) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>