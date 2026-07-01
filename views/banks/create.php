<?php
$currentPage   = 'banks';
$currentAction = 'create';
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
<title>Add Bank Account — SJFS</title>
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
          <a href="/sjfs/?page=banks" style="color:var(--muted);text-decoration:none">Bank accounts</a>
          <i class="ti ti-chevron-right"></i>
          <span class="breadcrumb-current">Add account</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Add bank account</h1>
          <p>Register a new bank account for transactions</p>
        </div>
        <div class="page-header-right">
          <a href="/sjfs/?page=banks" class="btn"><i class="ti ti-arrow-left"></i> Back</a>
        </div>
      </div>

      <div class="card animate-in" style="max-width:600px">
        <div class="card-header">
          <div>
            <div class="card-title">Account details</div>
            <div class="card-subtitle">Fields marked * are required</div>
          </div>
        </div>
        <div style="padding:20px 24px">
          <div id="form-error" class="alert alert-danger" style="display:none;margin-bottom:16px"></div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group">
              <label>Account name <span style="color:var(--danger)">*</span></label>
              <input type="text" id="account_name" class="form-control" placeholder="e.g. Maybank-SJFS" autocomplete="off">
            </div>
            <div class="form-group">
              <label>Bank name <span style="color:var(--danger)">*</span></label>
              <input type="text" id="bank_name" class="form-control" placeholder="e.g. Maybank" autocomplete="off">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group">
              <label>Account number</label>
              <input type="text" id="account_number" class="form-control" placeholder="e.g. 1234-5678-9012" autocomplete="off">
            </div>
            <div class="form-group">
              <label>Opening balance <span style="color:var(--danger)">*</span></label>
              <input type="number" id="opening_balance" class="form-control" placeholder="0.00" min="0" step="0.01" value="0.00">
            </div>
          </div>

          <div class="form-group">
            <label>Campus</label>
            <select id="campus_id" class="form-control">
              <option value="">All campuses</option>
              <option value="1">Camella Campus</option>
              <option value="2">BNT Campus</option>
            </select>
          </div>

          <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">
            <a href="/sjfs/?page=banks" class="btn">Cancel</a>
            <button class="btn btn-primary" onclick="submitCreate(this)">
              <i class="ti ti-check"></i> Save account
            </button>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="/sjfs/public/js/app.js"></script>
<script>
function submitCreate(btn) {
    const account_name    = document.getElementById('account_name').value.trim();
    const bank_name       = document.getElementById('bank_name').value.trim();
    const account_number  = document.getElementById('account_number').value.trim();
    const opening_balance = document.getElementById('opening_balance').value.trim();
    const campus_id       = document.getElementById('campus_id').value;
    const errBox          = document.getElementById('form-error');

    errBox.style.display = 'none';

    if (!account_name) return showError('Account name is required.');
    if (!bank_name)    return showError('Bank name is required.');
    if (opening_balance === '' || isNaN(opening_balance) || +opening_balance < 0)
        return showError('Opening balance must be 0 or greater.');

    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2"></i> Saving...';

    const body = new URLSearchParams({
        account_name, bank_name, account_number, opening_balance, campus_id
    });

    fetch('/sjfs/?page=banks&action=store', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Bank account saved.', 'success');
            setTimeout(() => window.location.href = '/sjfs/?page=banks', 800);
        } else {
            showError(data.message || 'Failed to save account.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check"></i> Save account';
        }
    })
    .catch(() => {
        showError('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check"></i> Save account';
    });

    function showError(msg) {
        errBox.textContent = msg;
        errBox.style.display = 'block';
        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
</body>
</html>