
// ── Date ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Panel switching ──
function showPanel(name) {
  document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('panel-' + name).classList.add('active');
  event.currentTarget.classList.add('active');

  // Load DB stats when maintenance panel is opened
  if (name === 'maintenance') loadDBStats();
}

// ── Load settings from DB ──
$.ajax({
  url: '../php/get/get_settings.php',
  method: 'GET',
  dataType: 'json',
  success(res) {
    if (!res.success) return;
    const s = res.settings;

    // LGU fields
    ['lgu_name','lgu_address','lgu_province','lgu_region',
     'lgu_mayor','lgu_email','lgu_phone','lgu_website'].forEach(key => {
      const el = document.getElementById(key);
      if (el) el.value = s[key] || '';
    });

    // System fields
    const fbOpen = document.getElementById('feedback_open');
    if (fbOpen) fbOpen.checked = s['feedback_open'] === '1';

    ['refresh_interval','max_export_rows','csmr_footer_note'].forEach(key => {
      const el = document.getElementById(key);
      if (el) el.value = s[key] || '';
    });
  }
});

// ── Save LGU Info ──
function saveLGUSettings() {
  const settings = {
    lgu_name:     document.getElementById('lgu_name').value,
    lgu_address:  document.getElementById('lgu_address').value,
    lgu_province: document.getElementById('lgu_province').value,
    lgu_region:   document.getElementById('lgu_region').value,
    lgu_mayor:    document.getElementById('lgu_mayor').value,
  };
  saveSettings('lgu', settings);
}

function saveContactSettings() {
  const settings = {
    lgu_email:   document.getElementById('lgu_email').value,
    lgu_phone:   document.getElementById('lgu_phone').value,
    lgu_website: document.getElementById('lgu_website').value,
  };
  saveSettings('lgu', settings);
}

// ── Save System Preferences ──
function saveSystemSettings() {
  const settings = {
    feedback_open:    document.getElementById('feedback_open').checked ? '1' : '0',
    refresh_interval: document.getElementById('refresh_interval').value,
    max_export_rows:  document.getElementById('max_export_rows').value,
    csmr_footer_note: document.getElementById('csmr_footer_note').value,
  };
  saveSettings('system', settings);
}

// ── Generic save ──
function saveSettings(group, settings) {
  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'save_settings', group, settings },
    success(res) {
      showToast(res.success, res.message || (res.success ? 'Settings saved!' : 'Save failed.'));
    },
    error(xhr) {
      console.error(xhr.responseText);
      showToast(false, 'Server error. Check console.');
    }
  });
}

// ── DB Stats ──
function loadDBStats() {
  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'get_db_stats' },
    success(res) {
      if (!res.success) return;
      const s = res.stats;

      // Row count boxes
      Object.entries(s.rows).forEach(([tbl, cnt]) => {
        const el = document.getElementById('ds-' + tbl);
        if (el) el.textContent = Number(cnt).toLocaleString();
      });
      document.getElementById('ds-dbsize').textContent = Number(s.db_size_kb).toLocaleString();

      // Sysinfo table
      document.getElementById('si-dbname').textContent  = s.db_name       || '—';
      document.getElementById('si-mysql').textContent   = s.mysql_version  || '—';
      document.getElementById('si-php').textContent     = s.php_version    || '—';
      document.getElementById('si-oldest').textContent  = s.feedback_range?.oldest || 'No data';
      document.getElementById('si-newest').textContent  = s.feedback_range?.newest || 'No data';
      document.getElementById('si-total').textContent   = Number(s.feedback_range?.total || 0).toLocaleString();
    }
  });
}

// ── Maintenance: clear old feedback ──
function clearOldFeedback() {
  const months = document.getElementById('clearMonths').value;
  const label  = document.getElementById('clearMonths').selectedOptions[0].text;
  if (!confirm(`Delete all feedback ${label}?\n\nThis action CANNOT be undone.`)) return;

  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'clear_old_feedback', months },
    success(res) {
      showToast(res.success, res.message);
      if (res.success) loadDBStats();
    }
  });
}

// ── Maintenance: clear export logs ──
function clearExportLogs() {
  if (!confirm('Clear all export history logs? This cannot be undone.')) return;
  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'clear_export_logs' },
    success(res) {
      showToast(res.success, res.message);
      if (res.success) loadDBStats();
    }
  });
}

// ── Toast notification ──
let toastTimer = null;
function showToast(success, msg) {
  const bar  = document.getElementById('toastBar');
  const icon = document.getElementById('toastIcon');
  const text = document.getElementById('toastMsg');

  bar.className  = 'toast-bar ' + (success ? 'success' : 'error');
  icon.className = 'bi ' + (success ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill');
  text.textContent = msg;

  bar.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => bar.classList.remove('show'), 3500);
}

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});