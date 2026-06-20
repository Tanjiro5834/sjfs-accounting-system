var currentRole = 'admin';

function selectRole(el) {
  document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  currentRole = el.dataset.role;
}

function togglePassword() {
  var input = document.getElementById('password');
  var icon  = document.getElementById('pw-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'ti ti-eye-off';
  } else {
    input.type = 'password';
    icon.className = 'ti ti-eye';
  }
}

function toggleTheme() {
  var html  = document.documentElement;
  var icon  = document.getElementById('theme-icon');
  var label = document.getElementById('theme-label');
  if (html.dataset.theme === 'light') {
    html.dataset.theme = 'dark';
    icon.className  = 'ti ti-moon';
    label.textContent = 'Dark';
  } else {
    html.dataset.theme = 'light';
    icon.className  = 'ti ti-sun';
    label.textContent = 'Light';
  }
}

function showError(msg) {
  var el = document.getElementById('error-msg');
  document.getElementById('error-text').textContent = msg;
  el.classList.remove('show');
  void el.offsetWidth;
  el.classList.add('show');
}

function handleLogin(e) {
  e.preventDefault();
  var email    = document.getElementById('email');
  var password = document.getElementById('password');
  var btn      = document.getElementById('submit-btn');
  var valid    = true;

  email.classList.remove('error');
  password.classList.remove('error');
  document.getElementById('error-msg').classList.remove('show');

  if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    email.classList.add('error');
    valid = false;
  }
  if (!password.value || password.value.length < 6) {
    password.classList.add('error');
    valid = false;
  }
  if (!valid) {
    showError('Please fill in all fields correctly.');
    return;
  }

  btn.classList.add('loading');
  btn.disabled = true;

  // Simulate — replace with actual fetch to AuthController
  setTimeout(function() {
    btn.classList.remove('loading');
    btn.disabled = false;
    // showError('Invalid email or password.');
    // On success: window.location.href = '/sjfs/?page=dashboard';
    showError('Demo mode — connect to AuthController to authenticate.');
  }, 1400);
}

// Auto dark mode
if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
  document.documentElement.dataset.theme = 'dark';
  document.getElementById('theme-icon').className  = 'ti ti-moon';
  document.getElementById('theme-label').textContent = 'Dark';
}