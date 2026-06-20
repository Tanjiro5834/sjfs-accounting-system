<?php
session_start();
$pageTitle = 'Sign In — SJFS Cash Flow System';
$theme = $_SESSION['theme'] ?? 'light';
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
  <link rel="stylesheet" href="/sjfs/public/css/login.css">
</head>
<body>
<script>
  document.documentElement.setAttribute('data-theme', localStorage.getItem('sjfs_theme') || '<?= htmlspecialchars($theme) ?>');
</script>

<button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle theme">
  <i class="ti ti-sun" id="theme-icon"></i>
  <span id="theme-label">Light</span>
</button>

<div class="card">
  <div class="brand">
    <div class="brand-icon"><i class="ti ti-building-bank"></i></div>
    <div class="brand-text">
      <strong>St. John Fisher School</strong>
      <span>CASH FLOW SYSTEM v1.0</span>
    </div>
  </div>

  <div class="divider"></div>

  <h1 class="heading">Sign in</h1>
  <p class="subheading">Enter your credentials to access the system.</p>

  <div class="error-msg" id="error-msg">
    <i class="ti ti-alert-circle"></i>
    <span id="error-text">Invalid email or password.</span>
  </div>

  <form id="login-form" onsubmit="handleLogin(event)" novalidate>
    <div class="form-group">
      <label for="email">Email address</label>
      <div class="input-wrap">
        <i class="ti ti-mail"></i>
        <input type="email" id="email" name="email" placeholder="you@sjfs.edu.ph" autocomplete="email" />
      </div>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <div class="input-wrap">
        <i class="ti ti-lock"></i>
        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" />
        <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Toggle password visibility">
          <i class="ti ti-eye" id="pw-icon"></i>
        </button>
      </div>
    </div>

    <div class="role-label">Sign in as</div>
    <div class="role-select" id="role-select">
      <button type="button" class="role-btn active" data-role="admin" onclick="selectRole(this)">
        <i class="ti ti-shield"></i> Admin
      </button>
      <button type="button" class="role-btn" data-role="accountant" onclick="selectRole(this)">
        <i class="ti ti-calculator"></i> Accountant
      </button>
      <button type="button" class="role-btn" data-role="cashier" onclick="selectRole(this)">
        <i class="ti ti-cash"></i> Cashier
      </button>
      <button type="button" class="role-btn" data-role="auditor" onclick="selectRole(this)">
        <i class="ti ti-clipboard-check"></i> Auditor
      </button>
    </div>

    <button type="submit" class="btn-submit" id="submit-btn">
      <div class="spinner"></div>
      <span class="btn-text">
        <i class="ti ti-login"></i> Sign in
      </span>
    </button>
  </form>

  <p class="footer-note">SJFS &copy; 2026 &mdash; Authorized access only</p>
</div>

<script src="/sjfs/public/js/login.js"></script>
</body>
</html>