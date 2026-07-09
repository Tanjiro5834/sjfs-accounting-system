<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SJFS — Sign in</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;1,14..32,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/dist/tabler-icons.min.css">
<link rel="stylesheet" href="/sjfs/public/css/login.css">
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('sjfs_theme') || 'light');</script>
</head>
<body>

<!-- LEFT -->
<div class="left">
  <div class="left-brand">
    <div class="left-brand-mark">
      <img src="/sjfs/public/images/sjfs.png" alt="Brand Logo" class="brand-image">
    </div>
    <div>
      <div class="left-brand-name">St. John Fisher School</div>
      <div class="left-brand-sub">MULTI-CAMPUS FINANCE</div>
    </div>
  </div>

  <div class="left-content">
    <div class="left-eyebrow">Cash Flow Monitoring System</div>
    <h1 class="left-headline">
      Every peso,<br>
      <strong>accounted for.</strong>
    </h1>
    <p class="left-desc">
      Real-time visibility into collections and disbursements
      across Camella and BNT campuses — with full audit trail
      and bank reconciliation.
    </p>

    <div class="stat-row">
      <div class="stat-item">
        <div class="stat-num">2</div>
        <div class="stat-label">Campuses</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">11</div>
        <div class="stat-label">Bank accounts</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">4</div>
        <div class="stat-label">Role levels</div>
      </div>
    </div>
  </div>

  <div class="left-footer">© 2026 SJFS — Internal use only</div>
</div>

<!-- RIGHT -->
<div class="right">
  <button class="theme-btn" onclick="toggleTheme()" title="Toggle theme">
    <i class="ti ti-sun" id="theme-icon"></i>
  </button>

  <div class="form-wrap">
    <h2 class="form-heading">Sign in</h2>
    <p class="form-sub">Access is restricted to authorized school staff only.</p>

    <!-- Role tabs -->
    <div class="role-tabs" id="role-tabs">
      <button type="button" class="role-tab active" data-role="admin" onclick="setRole(this)">
        <i class="ti ti-shield-half"></i>Admin
      </button>
      <button type="button" class="role-tab" data-role="accountant" onclick="setRole(this)">
        <i class="ti ti-calculator"></i>Accountant
      </button>
      <button type="button" class="role-tab" data-role="cashier" onclick="setRole(this)">
        <i class="ti ti-cash-register"></i>Cashier
      </button>
      <button type="button" class="role-tab" data-role="auditor" onclick="setRole(this)">
        <i class="ti ti-file-certificate"></i>Auditor
      </button>
    </div>

    <!-- Error alert -->
    <div class="alert alert-error" id="alert">
      <i class="ti ti-alert-circle"></i>
      <span id="alert-msg"></span>
    </div>

    <form id="form" novalidate>
      <!-- Email -->
      <div class="field">
        <label class="field-label" for="email">Email address</label>
        <div class="field-wrap">
          <input class="field-input" type="email" id="email" name="email"
            placeholder="you@sjfs.edu.ph" autocomplete="email">
          <i class="ti ti-at field-icon"></i>
        </div>
        <div class="field-error" id="err-email">Enter a valid email address.</div>
      </div>

      <!-- Password -->
      <div class="field">
        <label class="field-label" for="password">Password</label>
        <div class="field-wrap">
          <input class="field-input" type="password" id="password" name="password"
            placeholder="••••••••" autocomplete="current-password" style="padding-right:38px">
          <i class="ti ti-lock field-icon"></i>
          <button type="button" class="pw-toggle" onclick="togglePw()" tabindex="-1">
            <i class="ti ti-eye" id="pw-eye" style="font-size:15px"></i>
          </button>
        </div>
        <div class="field-error" id="err-password">Password is required.</div>
      </div>

      <button type="submit" class="btn-submit" id="submit-btn">
        <div class="spin"></div>
        <span class="btn-label"><i class="ti ti-login" style="font-size:15px"></i> Sign in</span>
      </button>
    </form>

    <div class="form-footer">
      <span class="form-footer-text">SJFS Cash Flow System</span>
      <span class="version-badge">v1.0.0</span>
    </div>
  </div>
</div>
<script src="/sjfs/public/js/login.js"></script>
</body>
</html>