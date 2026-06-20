// ─── THEME ────────────────────────────────────────────────
function toggleTheme() {
    var html  = document.documentElement;
    var icon  = document.getElementById('theme-icon');
    var isDark = html.dataset.theme === 'dark';

    html.dataset.theme = isDark ? 'light' : 'dark';
    localStorage.setItem('sjfs_theme', html.dataset.theme);

    if (icon) {
        icon.className = isDark ? 'ti ti-moon' : 'ti ti-sun';
    }
}

// apply saved theme on load
(function () {
    var saved = localStorage.getItem('sjfs_theme');
    if (saved) {
        document.documentElement.dataset.theme = saved;
        var icon = document.getElementById('theme-icon');
        if (icon) icon.className = saved === 'dark' ? 'ti ti-moon' : 'ti ti-sun';
    }
})();

// ─── SIDEBAR ──────────────────────────────────────────────
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var wrapper = document.querySelector('.main-wrapper');
    if (!sidebar) return;

    var isMobile = window.innerWidth <= 768;
    if (isMobile) {
        sidebar.classList.toggle('open');
    } else {
        sidebar.classList.toggle('collapsed');
        if (wrapper) wrapper.classList.toggle('expanded');
        localStorage.setItem('sjfs_sidebar',
            sidebar.classList.contains('collapsed') ? 'collapsed' : 'open');
    }
}

// restore sidebar state
(function () {
    var state   = localStorage.getItem('sjfs_sidebar');
    var sidebar = document.getElementById('sidebar');
    var wrapper = document.querySelector('.main-wrapper');
    if (state === 'collapsed' && sidebar && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        if (wrapper) wrapper.classList.add('expanded');
    }
})();

// close sidebar on mobile when clicking outside
document.addEventListener('click', function (e) {
    if (window.innerWidth > 768) return;
    var sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    var toggle = document.querySelector('.sidebar-toggle');
    if (sidebar.classList.contains('open')
        && !sidebar.contains(e.target)
        && (!toggle || !toggle.contains(e.target))) {
        sidebar.classList.remove('open');
    }
});

// ─── TOAST ────────────────────────────────────────────────
function showToast(message, type) {
    type = type || 'success';
    var icons = {
        success: 'ti-check',
        danger:  'ti-alert-circle',
        warning: 'ti-alert-triangle',
        info:    'ti-info-circle'
    };

    var container = document.getElementById('toast-container');
    if (!container) return;

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML =
        '<i class="ti ' + (icons[type] || icons.success) + '"></i>' +
        '<span class="toast-msg">' + message + '</span>';

    container.appendChild(toast);

    setTimeout(function () {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(12px)';
        toast.style.transition = 'all 0.25s ease';
        setTimeout(function () { toast.remove(); }, 260);
    }, 3000);
}

// ─── FETCH HELPER ─────────────────────────────────────────
function sjfsPost(url, data, onSuccess, onError) {
    var formData = data instanceof FormData ? data : new FormData();
    if (!(data instanceof FormData)) {
        Object.keys(data).forEach(function (k) { formData.append(k, data[k]); });
    }

    fetch(url, { method: 'POST', body: formData })
        .then(function (res) { return res.json(); })
        .then(function (json) {
            if (json.success) {
                if (onSuccess) onSuccess(json);
            } else {
                if (onError) onError(json.message || 'Something went wrong.');
                else showToast(json.message || 'Something went wrong.', 'danger');
            }
        })
        .catch(function () {
            var msg = 'Network error. Please try again.';
            if (onError) onError(msg);
            else showToast(msg, 'danger');
        });
}

// ─── CONFIRM DELETE ───────────────────────────────────────
function confirmDelete(url, id, label, onSuccess) {
    if (!confirm('Delete ' + (label || 'this record') + '? This cannot be undone.')) return;

    sjfsPost(url, { id: id }, function () {
        showToast((label || 'Record') + ' deleted successfully.', 'success');
        if (onSuccess) onSuccess();
    });
}

// ─── FORMAT CURRENCY ──────────────────────────────────────
function formatCurrency(amount) {
    return '\u20B1' + parseFloat(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// ─── DATE FILTER DEFAULT ──────────────────────────────────
(function () {
    var from = document.getElementById('date_from');
    var to   = document.getElementById('date_to');
    var today = new Date().toISOString().split('T')[0];
    var firstOfMonth = today.substring(0, 7) + '-01';

    if (from && !from.value) from.value = firstOfMonth;
    if (to   && !to.value)   to.value   = today;
})();