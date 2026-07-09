<?php
$pageTitle  = 'Add Cash In — SJFS';
$currentPage = 'sources';
$currentAction = 'create';
$user = currentUser();
$campusMap = [1 => 'Camella Campus', 2 => 'BNT Campus'];

$db = Database::getInstance()->getConnection();
$campuses = $db->query("SELECT * FROM campuses WHERE is_active = 1")->fetchAll();
$types = $db->query("SELECT * FROM collection_types WHERE is_active = 1")->fetchAll();
$banks = $db->query("SELECT * FROM bank_accounts WHERE is_active = 1 ORDER BY bank_name, account_name")->fetchAll();

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
          <a href="/sjfs/?page=sources" style="color:var(--muted)">Cash in</a>
          <i class="ti ti-chevron-right"></i>
          <span class="breadcrumb-current">Add entry</span>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-date"><i class="ti ti-calendar"></i><span><?= date('F d, Y') ?></span></div>
      </div>
    </header>

    <main class="main-content">

      <div class="page-header animate-in">
        <div class="page-header-left">
          <h1>Add cash in</h1>
          <p>Log a new source entry</p>
        </div>
        <div class="page-header-right">
          <a href="/sjfs/?page=sources" class="btn"><i class="ti ti-arrow-left"></i> Back</a>
        </div>
      </div>

      <div class="card animate-in" style="max-width:640px">
        <div class="card-header">
          <div class="card-title">Source entry form</div>
        </div>

        <div class="alert alert-danger" id="form-alert" style="display:none">
          <i class="ti ti-alert-circle"></i>
          <span id="form-alert-msg"></span>
        </div>

        <form id="source-form" novalidate>

          <div class="form-grid">

            <div class="form-group">
              <label for="campus_id">Campus</label>
              <select id="campus_id" name="campus_id" class="form-control">
                <option value="">Select campus</option>
                <?php foreach ($campuses as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <span class="form-error" id="err-campus_id">Campus is required.</span>
            </div>

            <div class="form-group">
              <label for="collection_type_id">Collection type</label>
              <select id="collection_type_id" name="collection_type_id" class="form-control">
                <option value="">Select type</option>
                <?php foreach ($types as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['code']) ?> — <?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <span class="form-error" id="err-collection_type_id">Collection type is required.</span>
            </div>

            <div class="form-group">
              <label for="bank_account_id">Bank account</label>
              <select id="bank_account_id" name="bank_account_id" class="form-control">
                <option value="">Select bank account</option>
                <?php foreach ($banks as $b): ?>
                  <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['account_name']) ?> (<?= htmlspecialchars($b['bank_name']) ?>)</option>
                <?php endforeach; ?>
              </select>
              <span class="form-error" id="err-bank_account_id">Bank account is required.</span>
            </div>

            <?php if ($user['role'] === 'cashier'): ?>
            <div class="form-group">
              <label for="source_type">Payment source</label>
              <select id="source_type" name="source_type" class="form-control">
                <option value="">Select source</option>
                <option value="cash">Cash</option>
                <option value="gcash">GCash</option>
                <option value="maya">Maya</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="check">Check</option>
              </select>
              <span class="form-error" id="err-source_type">Payment source is required.</span>
            </div>
            <?php endif; ?>

            <div class="form-group">
              <label for="amount">Amount (₱)</label>
              <div class="input-with-icon">
                <i class="ti ti-currency-peso"></i>
                <input type="number" id="amount" name="amount" class="form-control"
                  placeholder="0.00" step="0.01" min="0.01">
              </div>
              <span class="form-error" id="err-amount">Enter a valid amount greater than 0.</span>
            </div>

            <div class="form-group">
              <label for="transaction_date">Transaction date</label>
              <input type="date" id="transaction_date" name="transaction_date"
                class="form-control" value="<?= date('Y-m-d') ?>">
              <span class="form-error" id="err-transaction_date">Date is required.</span>
            </div>

            <div class="form-group form-full">
              <label for="remarks">Remarks <span style="color:var(--hint)">(optional)</span></label>
              <input type="text" id="remarks" name="remarks" class="form-control"
                placeholder="e.g. Payment from Juan dela Cruz">
            </div>

          </div>

          <div class="form-actions">
            <a href="/sjfs/?page=sources" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary" id="submit-btn">
              <div class="spinner" style="display:none"></div>
              <span class="btn-text"><i class="ti ti-check"></i> Save entry</span>
            </button>
          </div>

        </form>
      </div>

    </main>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script src="/sjfs/public/js/app.js"></script>
<script>
var required = ['campus_id','collection_type_id','bank_account_id','amount','transaction_date'];
if (document.getElementById('source_type')) required.push('source_type');

required.forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', function() { clearErr(id); });
    if (el) el.addEventListener('input',  function() { clearErr(id); });
});

function clearErr(id) {
    var el  = document.getElementById(id);
    var err = document.getElementById('err-' + id);
    if (el)  el.classList.remove('error');
    if (err) err.classList.remove('show');
}

function validate() {
    var ok = true;
    ['campus_id','collection_type_id','bank_account_id'].forEach(function(id) {
        if (!document.getElementById(id).value) {
            document.getElementById(id).classList.add('error');
            document.getElementById('err-' + id).classList.add('show');
            ok = false;
        }
    });

    var sourceTypeEl = document.getElementById('source_type');
    if (sourceTypeEl && !sourceTypeEl.value) {
        sourceTypeEl.classList.add('error');
        document.getElementById('err-source_type').classList.add('show');
        ok = false;
    }

    var amt = parseFloat(document.getElementById('amount').value);
    if (!amt || amt <= 0) {
        document.getElementById('amount').classList.add('error');
        document.getElementById('err-amount').classList.add('show');
        ok = false;
    }

    if (!document.getElementById('transaction_date').value) {
        document.getElementById('transaction_date').classList.add('error');
        document.getElementById('err-transaction_date').classList.add('show');
        ok = false;
    }

    return ok;
}

document.getElementById('source-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('form-alert').style.display = 'none';
    if (!validate()) return;

    var btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.querySelector('.spinner').style.display = 'inline-block';
    btn.querySelector('.btn-text').style.display = 'none';

    sjfsPost('/sjfs/?page=sources&action=store', new FormData(this),
        function(data) {
            showToast(data.message || 'Entry saved.', 'success');
            setTimeout(function() { window.location.href = '/sjfs/?page=sources'; }, 800);
        },
        function(msg) {
            btn.disabled = false;
            btn.querySelector('.spinner').style.display = 'none';
            btn.querySelector('.btn-text').style.display = 'inline-flex';
            document.getElementById('form-alert-msg').textContent = msg;
            document.getElementById('form-alert').style.display = 'flex';
        }
    );
});
</script>
</body>
</html>