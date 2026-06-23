<?php
$currentPage   = 'payables';
$currentAction = '';
$user          = currentUser();
$campusMap     = [1 => 'Camella Campus', 2 => 'BNT Campus'];
$navItems = [
    ['page'=>'dashboard','icon'=>'ti-layout-dashboard','label'=>'Dashboard','roles'=>['admin','accountant','cashier','auditor']],
    ['page'=>'sources','icon'=>'ti-arrow-bar-to-down','label'=>'Cash in','roles'=>['admin','accountant','cashier']],
    ['page'=>'payables','icon'=>'ti-arrow-bar-up','label'=>'Cash out','roles'=>['admin','accountant']],
    ['page'=>'banks','icon'=>'ti-building-bank','label'=>'Bank accounts','roles'=>['admin']],
    ['section'=>'Reports'],
    ['page'=>'reports','action'=>'cashflow','icon'=>'ti-chart-bar','label'=>'Cash flow','roles'=>['admin','accountant','auditor']],
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
<title>Cash Out — SJFS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/dist/tabler-icons.min.css">
<link rel="stylesheet" href="/sjfs/public/css/app.css">
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('sjfs_theme') || 'light');</script>
</head>
<body>

<div class="app-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon"><i class="ti ti-building-bank"></i></div>
      <div class="brand-text"><strong>SJFS</strong><span>Cash Flow System</span></div>
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

  <div class="main-wrapper">
    <header class="topbar">
      <div class="topbar-left">
        <button class="icon-btn sidebar-toggle" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
        <div class="breadcrumb">
          <span class="breadcrumb-home">SJFS</span>
          <i class="ti ti-chevron-right"></i>
          <span class="breadcrumb-current">Cash out</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Cash out</h1>
          <p>Payable entries for <?= date('F Y') ?></p>
        </div>
        <div class="page-header-right">
          <?php if (hasRole('admin', 'accountant')): ?>
            <a href="/sjfs/?page=payables&action=create" class="btn btn-primary">
              <i class="ti ti-plus"></i> Add entry
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid animate-in" style="grid-template-columns:repeat(3,1fr)">
        <div class="stat-card">
          <div class="stat-label">Total this period</div>
          <div class="stat-value stat-negative">₱<?= number_format($total, 2) ?></div>
          <div class="stat-sub"><?= count($payables) ?> transactions</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">From</div>
          <div class="stat-value stat-neutral" style="font-size:20px"><?= date('M d, Y', strtotime($dateFrom)) ?></div>
          <div class="stat-sub">Start of period</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">To</div>
          <div class="stat-value stat-neutral" style="font-size:20px"><?= date('M d, Y', strtotime($dateTo)) ?></div>
          <div class="stat-sub">End of period</div>
        </div>
      </div>

      <!-- FILTERS -->
      <div class="card animate-in" style="margin-bottom:16px">
        <form method="GET" action="/sjfs/">
          <input type="hidden" name="page" value="payables">
          <div class="filter-bar">
            <div class="form-group" style="margin:0">
              <label>From</label>
              <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="form-group" style="margin:0">
              <label>To</label>
              <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div style="align-self:flex-end;display:flex;gap:6px">
              <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i> Filter</button>
              <a href="/sjfs/?page=payables" class="btn"><i class="ti ti-x"></i> Clear</a>
            </div>
          </div>
        </form>
      </div>

      <!-- TABLE -->
      <div class="card animate-in">
        <div class="card-header">
          <div>
            <div class="card-title">Payable entries</div>
            <div class="card-subtitle"><?= date('M d', strtotime($dateFrom)) ?> — <?= date('M d, Y', strtotime($dateTo)) ?></div>
          </div>
          <button class="btn btn-sm" onclick="exportCSV()"><i class="ti ti-download"></i> Export CSV</button>
        </div>

        <?php if (empty($payables)): ?>
          <div class="empty-state">
            <i class="ti ti-inbox"></i>
            <h3>No entries found</h3>
            <p>No payable entries for the selected period.</p>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table id="payables-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Payee</th>
                  <th>Check #</th>
                  <th>Bank account</th>
                  <th>Amount</th>
                  <th>Remarks</th>
                  <th>Logged by</th>
                  <?php if (hasRole('admin', 'accountant')): ?>
                    <th>Actions</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payables as $i => $p): ?>
                  <tr>
                    <td class="td-muted"><?= $i + 1 ?></td>
                    <td class="td-mono"><?= date('M d, Y', strtotime($p->transaction_date)) ?></td>
                    <td><strong><?= htmlspecialchars($p->payee) ?></strong></td>
                    <td class="td-mono"><?= htmlspecialchars($p->check_number ?? '—') ?></td>
                    <td class="td-muted"><?= htmlspecialchars($p->bank_name) ?></td>
                    <td class="td-mono amount-negative">₱<?= number_format($p->amount, 2) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($p->remarks ?? '—') ?></td>
                    <td class="td-muted"><?= htmlspecialchars($p->created_by_name) ?></td>
                    <?php if (hasRole('admin', 'accountant')): ?>
                      <td>
                        <div style="display:flex;gap:4px">
                          <a href="/sjfs/?page=payables&action=edit&id=<?= $p->id ?>" class="icon-btn" title="Edit">
                            <i class="ti ti-edit"></i>
                          </a>
                          <?php if (hasRole('admin')): ?>
                            <button class="icon-btn" title="Delete"
                              onclick="deletePayable(<?= $p->id ?>, '<?= htmlspecialchars($p->payee, ENT_QUOTES) ?>')">
                              <i class="ti ti-trash"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div style="display:flex;justify-content:flex-end;padding:12px 14px;border-top:1px solid var(--border)">
            <span style="font-size:13px;color:var(--muted);margin-right:16px">Total</span>
            <span class="td-mono amount-negative" style="font-size:15px;font-weight:600">₱<?= number_format($total, 2) ?></span>
          </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="/sjfs/public/js/app.js"></script>
<script>
function deletePayable(id, label) {
    confirmDelete('/sjfs/?page=payables&action=delete', id, label, function() {
        window.location.reload();
    });
}
function exportCSV() {
    var rows = [['#','Date','Payee','Check #','Bank','Amount','Remarks','Logged by']];
    document.querySelectorAll('#payables-table tbody tr').forEach(function(tr, i) {
        var c = tr.querySelectorAll('td');
        rows.push([i+1, c[1].textContent.trim(), c[2].textContent.trim(), c[3].textContent.trim(),
                   c[4].textContent.trim(), c[5].textContent.trim(), c[6].textContent.trim(), c[7].textContent.trim()]);
    });
    var csv = rows.map(function(r) {
        return r.map(function(c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(',');
    }).join('\n');
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
    a.download = 'payables_<?= date('Y-m-d') ?>.csv';
    a.click();
}
</script>
</body>
</html>