<?php
$currentPage   = 'banks';
$currentAction = '';
$user          = currentUser();
$campusMap     = [1 => 'Camella Campus', 2 => 'BNT Campus'];
$navItems = [
    ['page'=>'dashboard','icon'=>'ti-layout-dashboard','label'=>'Dashboard','roles'=>['admin','accountant','cashier','auditor']],
    ['page'=>'sources','icon'=>'ti-arrow-bar-to-down','label'=>'Cash in','roles'=>['admin','accountant','cashier']],
    ['page'=>'payables','icon'=>'ti-arrow-bar-up','label'=>'Cash out','roles'=>['admin','accountant','cashier']],
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
<title>Bank Accounts — SJFS</title>
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
          <span class="breadcrumb-current">Bank accounts</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Bank accounts</h1>
          <p><?= count($accounts) ?> active account<?= count($accounts) !== 1 ? 's' : '' ?></p>
        </div>
        <div class="page-header-right">
          <a href="/sjfs/?page=banks&action=create" class="btn btn-primary">
            <i class="ti ti-plus"></i> Add account
          </a>
        </div>
      </div>

      <div class="card animate-in">
        <div class="card-header">
          <div>
            <div class="card-title">All bank accounts</div>
            <div class="card-subtitle">Active accounts used for transactions</div>
          </div>
        </div>

        <?php if (empty($accounts)): ?>
          <div class="empty-state">
            <i class="ti ti-building-bank"></i>
            <h3>No bank accounts yet</h3>
            <p>Add your first bank account to start recording transactions.</p>
            <a href="/sjfs/?page=banks&action=create" class="btn btn-primary" style="margin-top:12px">
              <i class="ti ti-plus"></i> Add account
            </a>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table id="banks-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Account name</th>
                  <th>Bank</th>
                  <th>Account number</th>
                  <th>Campus</th>
                  <th>Opening balance</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($accounts as $i => $a): ?>
                  <tr>
                    <td class="td-muted"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($a->account_name) ?></strong></td>
                    <td class="td-muted"><?= htmlspecialchars($a->bank_name) ?></td>
                    <td class="td-mono"><?= htmlspecialchars($a->account_number ?? '—') ?></td>
                    <td>
                      <?php if ($a->campus_id): ?>
                        <span class="badge badge-info"><?= $campusMap[$a->campus_id] ?? 'Unknown' ?></span>
                      <?php else: ?>
                        <span class="td-muted">All</span>
                      <?php endif; ?>
                    </td>
                    <td class="td-mono">₱<?= number_format($a->opening_balance, 2) ?></td>
                    <td>
                      <span class="badge <?= $a->is_active ? 'badge-success' : 'badge-danger' ?>">
                        <?= $a->is_active ? 'Active' : 'Inactive' ?>
                      </span>
                    </td>
                    <td>
                      <div style="display:flex;gap:4px">
                        <a href="/sjfs/?page=banks&action=edit&id=<?= $a->id ?>" class="icon-btn" title="Edit">
                          <i class="ti ti-edit"></i>
                        </a>
                        <?php if ($a->is_active): ?>
                          <button class="icon-btn" title="Deactivate"
                            onclick="deactivateBank(<?= $a->id ?>, '<?= htmlspecialchars($a->account_name, ENT_QUOTES) ?>')">
                            <i class="ti ti-ban"></i>
                          </button>
                        <?php endif; ?>
                      </div>
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
<script>
function deactivateBank(id, label) {
    confirmDelete('/sjfs/?page=banks&action=deactivate', id, label, function() {
        window.location.reload();
    });
}
</script>
</body>
</html>