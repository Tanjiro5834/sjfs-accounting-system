<?php
$pageTitle   = 'Dashboard — SJFS';
$dateFrom    = date('Y-m-01');
$dateTo      = date('Y-m-d');
$user        = currentUser();
$campusMap   = [1 => 'Camella Campus', 2 => 'BNT Campus'];

$sourceRepo  = new SourceRepository();
$payableRepo = new PayableRepository();
$bankRepo    = new BankAccountRepository();

$canViewPayables = can('payables', 'read');

$totalSources   = $sourceRepo->getTotalByDateRange($dateFrom, $dateTo);
$totalPayables  = $canViewPayables ? $payableRepo->getTotalByDateRange($dateFrom, $dateTo) : 0;
$netCashFlow    = $totalSources - $totalPayables;
$bankSummary    = $bankRepo->getBalanceSummary($dateFrom, $dateTo);
$recentSources  = array_slice($sourceRepo->findByDateRange($dateFrom, $dateTo), 0, 5);
$recentPayables = $canViewPayables ? array_slice($payableRepo->findByDateRange($dateFrom, $dateTo), 0, 5) : [];

$currentPage   = 'dashboard';
$currentAction = '';

$navItems = [
    ['page'=>'dashboard','icon'=>'ti-layout-dashboard','label'=>'Dashboard','roles'=>['admin','accountant','cashier','auditor']],
    ['page'=>'sources','icon'=>'ti-arrow-bar-to-down','label'=>'Cash in','roles'=>['admin','accountant','cashier']],
    ['page'=>'payables','icon'=>'ti-arrow-bar-up','label'=>'Cash out','roles'=>['admin','accountant','cashier']],
    ['page'=>'banks','icon'=>'ti-building-bank','label'=>'Bank accounts','roles'=>['admin']],
    ['section'=>'Reports'],
    ['page'=>'reports','action'=>'cashflow','icon'=>'ti-chart-bar','label'=>'Cash flow','roles'=>['admin','auditor']],
    ['page'=>'reports','action'=>'reconciliation','icon'=>'ti-scale','label'=>'Reconciliation','roles'=>['admin','accountant','auditor']],
    ['section'=>'System'],
    ['page'=>'audit','icon'=>'ti-shield-check','label'=>'Audit trail','roles'=>['admin','auditor']],
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/dist/tabler-icons.min.css">
<link rel="stylesheet" href="/sjfs/public/css/app.css?v=1">
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('sjfs_theme') || 'light');</script>
</head>
<body>

<div class="app-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <img src="/sjfs/public/images/sjfs-removebg.png" alt="SJFS logo">
      </div>
      <div class="brand-text"><strong>St. John Fisher School</strong><span>Cash Flow System</span></div>
    </div>
    <div class="campus-badge">
      <i class="ti ti-map-pin"></i>
      <span><?= $campusMap[$user['campus_id'] ?? 0] ?? 'All Campuses' ?></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($navItems as $item): ?>
        <?php if (isset($item['section'])): ?>
          <div class="nav-section"><?= $item['section'] ?></div>
        <?php else: ?>
          <?php
          if (!in_array($user['role'], $item['roles'] ?? [], true)) continue;
          $isActive = $currentPage === $item['page'] && (!isset($item['action']) || $currentAction === ($item['action'] ?? ''));
          $href = '/sjfs/?page=' . $item['page'];
          if (isset($item['action'])) $href .= '&action=' . $item['action'];
          ?>
          <a href="<?= $href ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
            <i class="ti <?= $item['icon'] ?>"></i>
            <span><?= $item['label'] ?></span>
            <?php if ($isActive): ?><div class="nav-indicator"></div><?php endif; ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
        <div class="user-details">
          <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong>
          <span><?= ucfirst($user['role'] ?? '') ?></span>
        </div>
      </div>
      <div class="sidebar-actions">
        <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
          <i class="ti ti-sun" id="theme-icon"></i>
        </button>
        <a href="/sjfs/?page=login&action=logout" class="icon-btn" title="Sign out">
          <i class="ti ti-logout"></i>
        </a>
      </div>
    </div>
  </aside>

  <div class="main-wrapper">
    <header class="topbar">
      <div class="topbar-left">
        <button class="icon-btn sidebar-toggle" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
        <div class="breadcrumb">
          <span class="breadcrumb-home">SJFS</span>
          <i class="ti ti-chevron-right"></i>
          <span class="breadcrumb-current">Dashboard</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Dashboard</h1>
          <p>Overview for <?= date('F Y') ?></p>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid animate-in">
        <div class="stat-card">
          <div class="stat-label">Total cash in</div>
          <div class="stat-value stat-positive">₱<?= number_format($totalSources, 2) ?></div>
          <div class="stat-sub">This month</div>
          <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $totalSources > 0 ? min(100, ($totalSources / max($totalSources, $totalPayables)) * 100) : 0 ?>%"></div>
          </div>
        </div>

        <?php if ($canViewPayables): ?>
        <div class="stat-card">
          <div class="stat-label">Total cash out</div>
          <div class="stat-value stat-negative">₱<?= number_format($totalPayables, 2) ?></div>
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
          <div class="stat-sub"><?= $netCashFlow >= 0 ? 'Positive' : 'Negative' ?> this month</div>
        </div>
        <?php endif; ?>

        <div class="stat-card">
          <div class="stat-label">Bank accounts</div>
          <div class="stat-value stat-neutral"><?= count($bankSummary) ?></div>
          <div class="stat-sub">Active accounts</div>
        </div>
      </div>

      <!-- RECENT TRANSACTIONS -->
      <div class="two-col animate-in" style="margin-bottom:20px">
        <div class="card">
          <div class="card-header">
            <div><div class="card-title">Recent cash in</div><div class="card-subtitle">Latest source entries</div></div>
            <a href="/sjfs/?page=sources" class="btn btn-sm"><i class="ti ti-arrow-right"></i> View all</a>
          </div>
          <?php if (empty($recentSources)): ?>
            <div class="empty-state"><i class="ti ti-inbox"></i><h3>No entries yet</h3><p>No sources recorded this month.</p></div>
          <?php else: ?>
            <div class="table-wrap">
              <table>
                <thead><tr><th>Campus</th><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
                <tbody>
                  <?php foreach ($recentSources as $s): ?>
                    <tr>
                      <td><?= htmlspecialchars($s['campus_name']) ?></td>
                      <td><span class="badge badge-success"><?= htmlspecialchars($s['type_code']) ?></span></td>
                      <td class="td-mono amount-positive">₱<?= number_format($s['amount'], 2) ?></td>
                      <td class="td-muted"><?= date('M d', strtotime($s['transaction_date'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($canViewPayables): ?>
        <div class="card">
          <div class="card-header">
            <div><div class="card-title">Recent cash out</div><div class="card-subtitle">Latest payable entries</div></div>
            <a href="/sjfs/?page=payables" class="btn btn-sm"><i class="ti ti-arrow-right"></i> View all</a>
          </div>
          <?php if (empty($recentPayables)): ?>
            <div class="empty-state"><i class="ti ti-inbox"></i><h3>No entries yet</h3><p>No payables recorded this month.</p></div>
          <?php else: ?>
            <div class="table-wrap">
              <table>
                <thead><tr><th>Payee</th><th>Bank</th><th>Amount</th><th>Date</th></tr></thead>
                <tbody>
                  <?php foreach ($recentPayables as $p): ?>
                    <tr>
                      <td><?= htmlspecialchars($p['payee']) ?></td>
                      <td class="td-muted"><?= htmlspecialchars($p['bank_name']) ?></td>
                      <td class="td-mono amount-negative">₱<?= number_format($p['amount'], 2) ?></td>
                      <td class="td-muted"><?= date('M d', strtotime($p['transaction_date'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- BANK BALANCE SUMMARY -->
      <div class="card animate-in">
        <div class="card-header">
          <div>
            <div class="card-title">Bank balance summary</div>
            <div class="card-subtitle"><?= date('F 1', strtotime($dateFrom)) ?> &mdash; <?= date('F d, Y', strtotime($dateTo)) ?></div>
          </div>
          <a href="/sjfs/?page=reports&action=reconciliation" class="btn btn-sm"><i class="ti ti-scale"></i> Full report</a>
        </div>
        <?php if (empty($bankSummary)): ?>
          <div class="empty-state"><i class="ti ti-building-bank"></i><h3>No bank accounts</h3><p>Add bank accounts to see balance summary.</p></div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Account</th><th>Bank</th><th>Opening</th><th>Cash in</th>
                  <?php if ($canViewPayables): ?><th>Cash out</th><?php endif; ?>
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
                    <?php if ($canViewPayables): ?>
                      <td class="td-mono amount-negative">-₱<?= number_format($b['total_payables'], 2) ?></td>
                    <?php endif; ?>
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

    </main>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="/sjfs/public/js/app.js"></script>
</body>
</html>