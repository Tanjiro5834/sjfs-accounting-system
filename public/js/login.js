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