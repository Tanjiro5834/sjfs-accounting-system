<?php
// views/cashflow.php
// Expects: $report (array from CashFlowReportStrategy::generate())

$pageTitle     = 'Cash Flow Report — SJFS';
$currentPage   = 'reports';
$currentAction = 'cashflow';
$user          = currentUser();
$campusMap     = [1 => 'Camella Campus', 2 => 'BNT Campus'];

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
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/dist/tabler-icons.min.css">
<link rel="stylesheet" href="/sjfs/public/css/app.css?v=1">
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('sjfs_theme') || 'light');</script>
</head>
<body>

<div class="app-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <img src="/sjfs/public/images/sjfs-removebg.png" alt="SJFS logo">
      </div>
      <div class="brand-text">
        <strong>St. John Fisher School</strong>
        <span>Cash Flow System</span>
      </div>
    </div>
    <div class="campus-badge">
      <i class="ti ti-map-pin"></i>
      <span><?= $campusMap[$user['campus_id'] ?? 0] ?? 'All Campuses' ?></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($navItems as $item): ?>
        <?php if (isset($item['section'])): ?>
          <div class="nav-section"><?= htmlspecialchars($item['section']) ?></div>
        <?php else: ?>
          <?php
          if (!in_array($user['role'], $item['roles'], true)) continue;
          $isActive = $currentPage === $item['page'] && (!isset($item['action']) || $currentAction === ($item['action'] ?? ''));
          $href = '/sjfs/?page=' . $item['page'];
          if (isset($item['action'])) $href .= '&action=' . $item['action'];
          ?>
          <a href="<?= $href ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
            <i class="ti <?= $item['icon'] ?>"></i>
            <span><?= htmlspecialchars($item['label']) ?></span>
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

  <!-- MAIN -->
  <div class="main-wrapper">
    <header class="topbar">
      <div class="topbar-left">
        <button class="icon-btn sidebar-toggle" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
        <div class="breadcrumb">
          <span class="breadcrumb-home">SJFS</span>
          <i class="ti ti-chevron-right"></i>
          <i class="ti ti-chevron-right"></i>
          <span class="breadcrumb-current">Cash flow report</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Cash Flow Report</h1>
          <p><?= date('F j, Y', strtotime($report['date_from'])) ?> to <?= date('F j, Y', strtotime($report['date_to'])) ?></p>
        </div>
        <div class="page-header-right">
          <a href="/sjfs/?page=reports&action=cashflow&format=pdf&date_from=<?= htmlspecialchars($report['date_from']) ?>&date_to=<?= htmlspecialchars($report['date_to']) ?>" class="btn btn-sm">
            <i class="ti ti-file-download"></i> Export PDF
          </a>
        </div>
      </div>

      <!-- Filter -->
      <form method="GET" class="filter-bar animate-in">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="action" value="cashflow">
        <input type="date" name="date_from" class="form-control"
               value="<?= htmlspecialchars($report['date_from']) ?>" required>
        <input type="date" name="date_to" class="form-control"
               value="<?= htmlspecialchars($report['date_to']) ?>" required>
        <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-player-play"></i> Run</button>
      </form>

      <!-- Summary Cards -->
      <div class="stats-grid animate-in">
        <div class="stat-card">
          <div class="stat-label">Total Sources</div>
          <div class="stat-value stat-positive">₱<?= number_format($report['total_sources'], 2) ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Payables</div>
          <div class="stat-value stat-negative">₱<?= number_format($report['total_payables'], 2) ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Net Cash Flow</div>
          <div class="stat-value <?= $report['is_positive'] ? 'stat-positive' : 'stat-warning' ?>">
            ₱<?= number_format($report['net_cash_flow'], 2) ?>
          </div>
        </div>
      </div>

      <!-- Grouped Sources -->
      <div class="card animate-in" style="margin-bottom:16px;">
        <div class="card-header"><span class="card-title">Sources by Campus / Type</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Group</th><th>Count</th><th>Amount</th></tr></thead>
            <tbody>
              <?php if (empty($report['grouped_sources'])): ?>
                <tr><td colspan="3"><div class="empty-state"><p>No sources found.</p></div></td></tr>
              <?php else: ?>
                <?php foreach ($report['grouped_sources'] as $g): ?>
                  <tr>
                    <td><?= htmlspecialchars($g['label']) ?></td>
                    <td><?= $g['count'] ?></td>
                    <td class="amount">₱<?= number_format($g['amount'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Raw Sources -->
      <div class="card animate-in" style="margin-bottom:16px;">
        <div class="card-header"><span class="card-title">Sources (Income)</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Date</th><th>Campus</th><th>Type</th><th>Bank</th><th>Amount</th><th>Remarks</th></tr></thead>
            <tbody>
              <?php if (empty($report['sources'])): ?>
                <tr><td colspan="6"><div class="empty-state"><p>No records.</p></div></td></tr>
              <?php else: ?>
                <?php foreach ($report['sources'] as $s): ?>
                  <tr>
                    <td><?= date('F j, Y', strtotime($s['transaction_date'])) ?></td>
                    <td><?= htmlspecialchars($s['campus_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['type_code'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['bank_name'] ?? '') ?></td>
                    <td class="amount amount-positive">+₱<?= number_format($s['amount'], 2) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($s['remarks'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Raw Payables -->
      <div class="card animate-in">
        <div class="card-header"><span class="card-title">Payables (Expense)</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Date</th><th>Payee</th><th>Check #</th><th>Amount</th><th>Remarks</th></tr></thead>
            <tbody>
              <?php if (empty($report['payables'])): ?>
                <tr><td colspan="5"><div class="empty-state"><p>No records.</p></div></td></tr>
              <?php else: ?>
                <?php foreach ($report['payables'] as $p): ?>
                  <tr>
                    <td><?= date('F j, Y', strtotime($p['transaction_date'])) ?></td>
                    <td><?= htmlspecialchars($p['payee']) ?></td>
                    <td class="td-mono"><?= htmlspecialchars($p['check_number'] ?? '') ?></td>
                    <td class="amount amount-negative">-₱<?= number_format($p['amount'], 2) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($p['remarks'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="/sjfs/public/js/app.js"></script>
</body>
</html>