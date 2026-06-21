<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SJFS — Sign in</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;1,14..32,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/dist/tabler-icons.min.css">
<style>
/* ── TOKENS ── */
:root {
  --ink:        #0D0F0E;
  --ink-2:      #3A3D3B;
  --ink-3:      #6B6F6D;
  --ink-4:      #9EA19F;
  --paper:      #F5F4F0;
  --paper-2:    #EDECEA;
  --paper-3:    #E3E2DE;
  --line:       #D6D4CF;
  --green:      #1A5438;
  --green-mid:  #2B7A55;
  --green-light:#D6EDE3;
  --green-glow: rgba(26,84,56,0.10);
  --red:        #B53B2A;
  --red-light:  #FAEAE7;
  --gold:       #A07830;
  --mono:       'DM Mono', monospace;
  --sans:       'Inter', sans-serif;
  --ease:       cubic-bezier(0.16,1,0.3,1);
  --t:          0.2s;
}
[data-theme="dark"] {
  --ink:        #F0EFEB;
  --ink-2:      #C8C6C1;
  --ink-3:      #8A8883;
  --ink-4:      #5A5855;
  --paper:      #0F1110;
  --paper-2:    #181A19;
  --paper-3:    #202321;
  --line:       #2C2F2D;
  --green:      #3DBA7E;
  --green-mid:  #2B7A55;
  --green-light:#0A2018;
  --green-glow: rgba(61,186,126,0.08);
  --red:        #E5705E;
  --red-light:  #2A100C;
  --gold:       #C9A050;
}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{height:100%}
body{
  font-family:var(--sans);
  font-size:14px;
  background:var(--paper);
  color:var(--ink);
  min-height:100vh;
  display:grid;
  grid-template-columns:1fr 1fr;
  transition:background var(--t), color var(--t);
}

/* ── LEFT PANEL ── */
.left{
  position:relative;
  background:var(--green);
  overflow:hidden;
  display:flex;
  flex-direction:column;
  padding:48px;
}
.left::before{
  content:'';
  position:absolute;
  inset:0;
  background:
    repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(255,255,255,0.04) 39px, rgba(255,255,255,0.04) 40px),
    repeating-linear-gradient(90deg, transparent, transparent 39px, rgba(255,255,255,0.04) 39px, rgba(255,255,255,0.04) 40px);
  pointer-events:none;
}
.left::after{
  content:'';
  position:absolute;
  bottom:-120px;
  right:-80px;
  width:400px;
  height:400px;
  background:radial-gradient(circle, rgba(255,255,255,0.07) 0%, transparent 70%);
  pointer-events:none;
}

.left-brand{
  display:flex;
  align-items:center;
  gap:12px;
  position:relative;
  z-index:1;
}
.left-brand-mark{
  width:40px;height:40px;
  background:rgba(255,255,255,0.12);
  border:1px solid rgba(255,255,255,0.2);
  border-radius:8px;
  display:flex;align-items:center;justify-content:center;
}
.left-brand-mark i{font-size:20px;color:#fff}
.left-brand-name{
  font-size:13px;font-weight:600;color:#fff;letter-spacing:0.01em;
}
.left-brand-sub{
  font-size:11px;color:rgba(255,255,255,0.55);
  font-family:var(--mono);letter-spacing:0.04em;
}

.left-content{
  position:relative;
  z-index:1;
  margin-top:auto;
  margin-bottom:auto;
}
.left-eyebrow{
  font-family:var(--mono);
  font-size:10px;
  color:rgba(255,255,255,0.5);
  letter-spacing:0.12em;
  text-transform:uppercase;
  margin-bottom:16px;
}
.left-headline{
  font-size:clamp(28px,3.5vw,42px);
  font-weight:300;
  color:#fff;
  line-height:1.15;
  letter-spacing:-0.02em;
  margin-bottom:20px;
}
.left-headline strong{
  font-weight:600;
  display:block;
}
.left-desc{
  font-size:13px;
  color:rgba(255,255,255,0.6);
  line-height:1.7;
  max-width:320px;
  margin-bottom:40px;
}

.stat-row{
  display:grid;
  grid-template-columns:1fr 1fr 1fr;
  gap:1px;
  background:rgba(255,255,255,0.1);
  border-radius:10px;
  overflow:hidden;
}
.stat-item{
  background:rgba(0,0,0,0.15);
  padding:16px;
  backdrop-filter:blur(4px);
}
.stat-num{
  font-family:var(--mono);
  font-size:22px;
  font-weight:500;
  color:#fff;
  line-height:1;
  margin-bottom:4px;
}
.stat-label{
  font-size:10px;
  color:rgba(255,255,255,0.5);
  text-transform:uppercase;
  letter-spacing:0.06em;
}

.left-footer{
  position:relative;
  z-index:1;
  font-size:11px;
  color:rgba(255,255,255,0.35);
  font-family:var(--mono);
}

/* ── RIGHT PANEL ── */
.right{
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  padding:48px 56px;
  position:relative;
  background:var(--paper);
}

.theme-btn{
  position:absolute;
  top:24px;right:24px;
  width:34px;height:34px;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--line);
  border-radius:8px;
  background:var(--paper-2);
  color:var(--ink-3);
  cursor:pointer;
  transition:all var(--t);
}
.theme-btn:hover{border-color:var(--ink-3);color:var(--ink)}
.theme-btn i{font-size:16px}

.form-wrap{width:100%;max-width:360px}

.form-heading{
  font-size:22px;
  font-weight:600;
  letter-spacing:-0.02em;
  color:var(--ink);
  margin-bottom:4px;
}
.form-sub{
  font-size:13px;
  color:var(--ink-3);
  margin-bottom:32px;
  line-height:1.5;
}

/* role tabs */
.role-tabs{
  display:flex;
  gap:4px;
  background:var(--paper-2);
  border:1px solid var(--line);
  border-radius:8px;
  padding:4px;
  margin-bottom:24px;
}
.role-tab{
  flex:1;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:3px;
  padding:8px 4px;
  border-radius:6px;
  border:none;
  background:transparent;
  cursor:pointer;
  color:var(--ink-3);
  font-family:var(--sans);
  font-size:10px;
  font-weight:500;
  text-transform:uppercase;
  letter-spacing:0.05em;
  transition:all var(--t);
}
.role-tab i{font-size:16px}
.role-tab:hover{color:var(--ink);background:var(--paper-3)}
.role-tab.active{
  background:var(--paper);
  color:var(--green);
  box-shadow:0 1px 3px rgba(0,0,0,0.08);
}
[data-theme="dark"] .role-tab.active{background:var(--paper-3)}

/* form fields */
.field{margin-bottom:16px}
.field-label{
  display:block;
  font-size:11px;
  font-weight:600;
  color:var(--ink-3);
  text-transform:uppercase;
  letter-spacing:0.05em;
  margin-bottom:6px;
}
.field-wrap{position:relative}
.field-icon{
  position:absolute;
  left:11px;top:50%;
  transform:translateY(-50%);
  font-size:15px;
  color:var(--ink-4);
  pointer-events:none;
  transition:color var(--t);
}
.field-input{
  width:100%;
  padding:9px 12px 9px 34px;
  font-size:13px;
  font-family:var(--sans);
  color:var(--ink);
  background:var(--paper-2);
  border:1px solid var(--line);
  border-radius:8px;
  outline:none;
  transition:all var(--t);
}
.field-input:focus{
  background:var(--paper);
  border-color:var(--green);
  box-shadow:0 0 0 3px var(--green-glow);
}
.field-input:focus~.field-icon,
.field-wrap:focus-within .field-icon{color:var(--green)}
.field-input.is-error{border-color:var(--red);box-shadow:0 0 0 3px rgba(181,59,42,0.08)}

.pw-toggle{
  position:absolute;
  right:10px;top:50%;
  transform:translateY(-50%);
  background:none;border:none;
  color:var(--ink-4);cursor:pointer;
  display:flex;align-items:center;
  padding:3px;
  transition:color var(--t);
}
.pw-toggle:hover{color:var(--ink-2)}
.field-error{
  font-size:11px;color:var(--red);
  margin-top:4px;display:none;
}
.field-error.show{display:block}

/* alert */
.alert{
  display:none;
  align-items:center;
  gap:8px;
  padding:10px 12px;
  border-radius:8px;
  font-size:12px;
  margin-bottom:16px;
  border:1px solid;
}
.alert.show{display:flex}
.alert-error{background:var(--red-light);border-color:var(--red);color:var(--red)}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-5px)}75%{transform:translateX(5px)}}
.shake{animation:shake 0.28s ease}

/* submit */
.btn-submit{
  width:100%;
  padding:10px 16px;
  background:var(--green);
  color:#fff;
  border:none;
  border-radius:8px;
  font-size:13px;
  font-weight:500;
  font-family:var(--sans);
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  transition:all var(--t);
  margin-top:4px;
  letter-spacing:0.01em;
}
.btn-submit:hover{background:var(--green-mid);transform:translateY(-1px)}
.btn-submit:active{transform:translateY(0)}
.btn-submit:disabled{opacity:0.55;cursor:not-allowed;transform:none}
.spin{
  width:15px;height:15px;
  border:2px solid rgba(255,255,255,0.25);
  border-top-color:#fff;
  border-radius:50%;
  animation:rotate 0.65s linear infinite;
  display:none;
}
.loading .spin{display:inline-block}
.loading .btn-label{display:none}
@keyframes rotate{to{transform:rotate(360deg)}}

.form-footer{
  margin-top:28px;
  padding-top:20px;
  border-top:1px solid var(--line);
  display:flex;
  align-items:center;
  justify-content:space-between;
}
.left-brand-mark {
  width: 40px;
  height: 40px;
  overflow: hidden;
  border-radius: 8px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.2);
}

.left-brand-mark img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.form-footer-text{font-size:11px;color:var(--ink-4);font-family:var(--mono)}
.version-badge{
  font-size:10px;
  font-family:var(--mono);
  color:var(--ink-4);
  background:var(--paper-2);
  border:1px solid var(--line);
  padding:3px 8px;
  border-radius:20px;
  letter-spacing:0.04em;
}

/* ── RESPONSIVE ── */
@media(max-width:768px){
  body{grid-template-columns:1fr}
  .left{display:none}
  .right{padding:40px 24px}
}
</style>
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

<script>
var role = 'admin';

function setRole(el) {
    document.querySelectorAll('.role-tab').forEach(function(b){ b.classList.remove('active'); });
    el.classList.add('active');
    role = el.dataset.role;
}

function togglePw() {
    var i = document.getElementById('password');
    var e = document.getElementById('pw-eye');
    if (i.type === 'password') { i.type = 'text'; e.className = 'ti ti-eye-off'; }
    else { i.type = 'password'; e.className = 'ti ti-eye'; }
}

function toggleTheme() {
    var h = document.documentElement;
    var i = document.getElementById('theme-icon');
    var dark = h.dataset.theme === 'dark';
    h.dataset.theme = dark ? 'light' : 'dark';
    i.className = dark ? 'ti ti-moon' : 'ti ti-sun';
    localStorage.setItem('sjfs_theme', h.dataset.theme);
}

// restore theme
(function(){
    var t = localStorage.getItem('sjfs_theme');
    if (t) {
        document.documentElement.dataset.theme = t;
        var i = document.getElementById('theme-icon');
        if (i) i.className = t === 'dark' ? 'ti ti-moon' : 'ti ti-sun';
    }
})();

function showAlert(msg) {
    var a = document.getElementById('alert');
    document.getElementById('alert-msg').textContent = msg;
    a.classList.remove('show','shake');
    void a.offsetWidth;
    a.classList.add('show','shake');
}

function fieldErr(id, show) {
    document.getElementById(id).classList.toggle('is-error', show);
    document.getElementById('err-' + id).classList.toggle('show', show);
}

['email','password'].forEach(function(id){
    document.getElementById(id).addEventListener('input', function(){
        fieldErr(id, false);
        document.getElementById('alert').classList.remove('show');
    });
});

document.getElementById('form').addEventListener('submit', function(e) {
    e.preventDefault();
    var email = document.getElementById('email').value.trim();
    var pw    = document.getElementById('password').value;
    var ok    = true;

    fieldErr('email', false); fieldErr('password', false);
    document.getElementById('alert').classList.remove('show');

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { fieldErr('email', true); ok = false; }
    if (!pw || pw.length < 6) { fieldErr('password', true); ok = false; }
    if (!ok) return;

    var btn = document.getElementById('submit-btn');
    btn.disabled = true; btn.classList.add('loading');

    var fd = new FormData();
    fd.append('email', email);
    fd.append('password', pw);
    fd.append('role', role);

    fetch('/sjfs/?page=login&action=login', { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) {
                window.location.href = d.redirect || '/sjfs/?page=dashboard';
            } else {
                btn.disabled = false; btn.classList.remove('loading');
                showAlert(d.message || 'Invalid credentials.');
            }
        })
        .catch(function(){
            btn.disabled = false; btn.classList.remove('loading');
            showAlert('Network error. Try again.');
        });
});
</script>
</body>
</html>