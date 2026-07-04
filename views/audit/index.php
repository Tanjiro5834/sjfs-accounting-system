<?php
// views/audit/index.php
// Expects: $logs = ['success' => bool, 'data' => array, 'error'?: string]

$pageTitle     = 'Audit Trail — SJFS';
$currentPage   = 'audit';
$currentAction = 'index';
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

$rows = $logs['success'] ? $logs['data'] : [];

$actionBadge = [
    'CREATE' => 'badge-success',
    'UPDATE' => 'badge-info',
    'DELETE' => 'badge-danger',
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
<link rel="stylesheet" href="/sjfs/public/css/app.css">
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('sjfs_theme') || 'light');</script>
</head>
<body>

<div class="app-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon"><img src="/sjfs/public/images/sjfs-removebg.png" alt="SJFS logo"></div>
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
        <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme"><i class="ti ti-sun" id="theme-icon"></i></button>
        <a href="/sjfs/?page=login&action=logout" class="icon-btn" title="Sign out"><i class="ti ti-logout"></i></a>
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
          <span class="breadcrumb-current">Audit trail</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Audit Trail</h1>
          <p>System-wide activity log</p>
        </div>
      </div>

      <!-- Filter -->
      <form method="GET" class="filter-bar animate-in" id="audit-filter">
        <input type="hidden" name="page" value="audit">
        <select name="module" class="form-control">
          <option value="">All modules</option>
          <option value="SOURCES">Sources</option>
          <option value="PAYABLES">Payables</option>
          <option value="BANKS">Banks</option>
        </select>
        <select name="action_type" class="form-control">
          <option value="">All actions</option>
          <option value="CREATE">Create</option>
          <option value="UPDATE">Update</option>
          <option value="DELETE">Delete</option>
        </select>
        <input type="date" name="date_from" class="form-control">
        <input type="date" name="date_to" class="form-control">
        <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-filter"></i> Filter</button>
      </form>

      <?php if (!$logs['success']): ?>
        <div class="alert alert-danger">
          <i class="ti ti-alert-circle"></i>
          <span><?= htmlspecialchars($logs['error'] ?? 'Failed to load audit logs.') ?></span>
        </div>
      <?php endif; ?>

      <div class="card animate-in">
        <div class="card-header"><span class="card-title">Activity Log</span></div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Date/Time</th><th>User</th><th>Action</th><th>Module</th>
                <th>Record ID</th><th>IP Address</th><th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="7"><div class="empty-state"><p>No audit logs found.</p></div></td></tr>
              <?php else: ?>
                <?php foreach ($rows as $log): ?>
                  <tr>
                    <td class="td-mono"><?= htmlspecialchars($log['created_at']) ?></td>
                    <td><?= htmlspecialchars($log['user_id']) ?></td>
                    <td>
                      <span class="badge <?= $actionBadge[$log['action']] ?? 'badge-neutral' ?>">
                        <?= htmlspecialchars($log['action']) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($log['module']) ?></td>
                    <td class="td-mono">#<?= htmlspecialchars($log['record_id']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($log['ip_address'] ?? '') ?></td>
                    <td>
                      <button class="icon-btn view-diff"
                        data-old='<?= htmlspecialchars($log['old_value'] ?? 'null', ENT_QUOTES) ?>'
                        data-new='<?= htmlspecialchars($log['new_value'] ?? 'null', ENT_QUOTES) ?>'
                        title="View changes">
                        <i class="ti ti-eye"></i>
                      </button>
                    </td>
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

<!-- Diff Modal -->
<div id="diff-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100; align-items:center; justify-content:center;">
  <div class="card" style="max-width:600px; width:90%; max-height:80vh; overflow-y:auto;">
    <div class="card-header">
      <span class="card-title">Change Details</span>
      <button class="icon-btn" onclick="document.getElementById('diff-modal').style.display='none'"><i class="ti ti-x"></i></button>
    </div>
    <div class="two-col">
      <div>
        <div class="stat-label" style="margin-bottom:8px;">Old Value</div>
        <pre id="diff-old" class="td-mono" style="white-space:pre-wrap; font-size:11px; background:var(--surface2); padding:10px; border-radius:var(--radius-sm);"></pre>
      </div>
      <div>
        <div class="stat-label" style="margin-bottom:8px;">New Value</div>
        <pre id="diff-new" class="td-mono" style="white-space:pre-wrap; font-size:11px; background:var(--surface2); padding:10px; border-radius:var(--radius-sm);"></pre>
      </div>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="/sjfs/public/js/app.js"></script>
<script>
document.querySelectorAll('.view-diff').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('diff-old').textContent = this.dataset.old;
        document.getElementById('diff-new').textContent = this.dataset.new;
        document.getElementById('diff-modal').style.display = 'flex';
    });
});
</script>
</body>
</html>