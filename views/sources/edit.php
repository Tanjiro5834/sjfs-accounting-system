<?php
$db = Database::getInstance()->getConnection();
$campuses = $db->query("SELECT * FROM campuses WHERE is_active = 1")->fetchAll();
$types = $db->query("SELECT * FROM collection_types WHERE is_active = 1")->fetchAll();
$banks = $db->query("SELECT * FROM bank_accounts WHERE is_active = 1 ORDER BY bank_name, account_name")->fetchAll();

$rawStmt = $db->prepare("SELECT * FROM sources WHERE id = ?");
$rawStmt->execute([$source->id]);
$raw = $rawStmt->fetch(PDO::FETCH_ASSOC);

$currentPage = 'sources';
$currentAction = 'edit';
$user = currentUser();
$campusMap = [1 => 'Camella Campus', 2 => 'BNT Campus'];
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
<title>Edit Cash In — SJFS</title>
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
          <a href="/sjfs/?page=sources" style="color:var(--muted);text-decoration:none">Cash in</a>
          <i class="ti ti-chevron-right"></i>
          <span class="breadcrumb-current">Edit entry</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Edit cash in entry</h1>
          <p>Updating record #<?= (int) $source->id ?></p>
        </div>
        <div class="page-header-right">
          <a href="/sjfs/?page=sources" class="btn"><i class="ti ti-arrow-left"></i> Back</a>
        </div>
      </div>

      <div class="card animate-in" style="max-width:640px">
        <div class="card-header">
          <div class="card-title">Source entry form</div>
          <div class="card-subtitle">Fields marked * are required</div>
        </div>

        <div class="alert alert-danger" id="form-alert" style="display:none;margin:0 24px">
          <i class="ti ti-alert-circle"></i>
          <span id="form-alert-msg"></span>
        </div>

        <div style="padding:20px 24px">
          <input type="hidden" id="record-id" value="<?= (int) $source->id ?>">

          <div class="form-group">
            <label>Campus <span style="color:var(--danger)">*</span></label>
            <select id="campus_id" class="form-control">
              <option value="">Select campus</option>
              <?php foreach ($campuses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $raw['campus_id'] == $c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Collection type <span style="color:var(--danger)">*</span></label>
            <select id="collection_type_id" class="form-control">
              <option value="">Select type</option>
              <?php foreach ($types as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $raw['collection_type_id'] == $t['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($t['code']) ?> — <?= htmlspecialchars($t['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group">
              <label>Bank account <span style="color:var(--danger)">*</span></label>
              <select id="bank_account_id" class="form-control">
                <option value="">Select bank account</option>
                <?php foreach ($banks as $b): ?>
                  <option value="<?= $b['id'] ?>" <?= $raw['bank_account_id'] == $b['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['account_name']) ?> (<?= htmlspecialchars($b['bank_name']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Amount (₱) <span style="color:var(--danger)">*</span></label>
              <input type="number" id="amount" class="form-control"
                value="<?= htmlspecialchars($source->amount) ?>" min="0.01" step="0.01">
            </div>
          </div>

          <div class="form-group">
            <label>Transaction date <span style="color:var(--danger)">*</span></label>
            <input type="date" id="transaction_date" class="form-control"
              value="<?= htmlspecialchars($source->transaction_date) ?>">
          </div>

          <div class="form-group">
            <label>Remarks <span style="color:var(--hint)">(optional)</span></label>
            <input type="text" id="remarks" class="form-control"
              value="<?= htmlspecialchars($source->remarks ?? '') ?>"
              placeholder="e.g. Payment from Juan dela Cruz">
          </div>

          <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">
            <a href="/sjfs/?page=sources" class="btn">Cancel</a>
            <button class="btn btn-primary" onclick="submitUpdate(this)">
              <i class="ti ti-check"></i> Save changes
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
function submitUpdate(btn) {
    const id                 = document.getElementById('record-id').value;
    const campus_id          = document.getElementById('campus_id').value;
    const collection_type_id = document.getElementById('collection_type_id').value;
    const bank_account_id    = document.getElementById('bank_account_id').value;
    const amount             = document.getElementById('amount').value.trim();
    const transaction_date   = document.getElementById('transaction_date').value;
    const remarks            = document.getElementById('remarks').value.trim();
    const alertBox           = document.getElementById('form-alert');
    const alertMsg           = document.getElementById('form-alert-msg');

    alertBox.style.display = 'none';

    if (!campus_id)          return showError('Campus is required.');
    if (!collection_type_id) return showError('Collection type is required.');
    if (!bank_account_id)    return showError('Bank account is required.');
    if (!amount || +amount <= 0) return showError('Amount must be greater than 0.');
    if (!transaction_date)   return showError('Transaction date is required.');

    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2"></i> Saving...';

    const body = new URLSearchParams({
        id, campus_id, collection_type_id, bank_account_id, amount, transaction_date, remarks
    });

    fetch('/sjfs/?page=sources&action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Source entry updated.', 'success');
            setTimeout(() => window.location.href = '/sjfs/?page=sources', 800);
        } else {
            showError(data.message || 'Failed to update entry.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check"></i> Save changes';
        }
    })
    .catch(() => {
        showError('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check"></i> Save changes';
    });

    function showError(msg) {
        alertMsg.textContent = msg;
        alertBox.style.display = 'flex';
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
</body>
</html>