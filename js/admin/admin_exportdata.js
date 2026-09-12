// ── Local date formatting helper ──
// IMPORTANT: do NOT use `.toISOString().split('T')[0]` for local dates.
// toISOString() always converts to UTC, and in timezones ahead of UTC
// (e.g. Philippines, UTC+8), local midnight shifts back into the
// previous day once converted — silently sending the wrong date to
// the server. Same bug found and fixed earlier in admin_csmr_generator.js.
function toLocalISODate(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

// ── Date display ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Default date range ──
const now = new Date();
document.getElementById('filterDateFrom').value = toLocalISODate(new Date(now.getFullYear(), now.getMonth(), 1));
document.getElementById('filterDateTo').value   = toLocalISODate(new Date(now.getFullYear(), now.getMonth()+1, 0));

// ── Load departments ──
$.ajax({
  url: '../php/get/get_departments.php',
  method: 'GET',
  success(res) {
    const depts = Array.isArray(res) ? res : (res.data || res.departments || []);
    const sel   = document.getElementById('filterDept');
    depts.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.code;
      opt.textContent = d.name;
      sel.appendChild(opt);
    });
    updateStats();
    loadExportHistory(); // ← load DB history on page open
  }
});

// ── Quick range ──
function applyQuickRange() {
  const val = document.getElementById('quickRange').value;
  const n   = new Date();
  let from, to;
  switch(val) {
    case 'this_month':
      from = new Date(n.getFullYear(), n.getMonth(), 1);
      to   = new Date(n.getFullYear(), n.getMonth()+1, 0); break;
    case 'last_month':
      from = new Date(n.getFullYear(), n.getMonth()-1, 1);
      to   = new Date(n.getFullYear(), n.getMonth(), 0); break;
    case 'this_quarter':
      const q = Math.floor(n.getMonth()/3);
      from = new Date(n.getFullYear(), q*3, 1);
      to   = new Date(n.getFullYear(), q*3+3, 0); break;
    case 'this_year':
      from = new Date(n.getFullYear(), 0, 1);
      to   = new Date(n.getFullYear(), 11, 31); break;
    case 'all_time':
      from = new Date('2000-01-01');
      to   = new Date(); break;
    default: return;
  }
  document.getElementById('filterDateFrom').value = toLocalISODate(from);
  document.getElementById('filterDateTo').value   = toLocalISODate(to);
  updateStats();
}

// ── Update stats preview ──
function updateStats() {
  const from = document.getElementById('filterDateFrom').value;
  const to   = document.getElementById('filterDateTo').value;
  const dept = document.getElementById('filterDept').value;

  // Show range in stat box
  const fmtDate = d => new Date(d).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
  document.getElementById('statDateRange').textContent =
    from && to ? fmtDate(from) + ' – ' + fmtDate(to) : '—';

  // Fetch quick counts via AJAX
  // NOTE: period:'custom' now handled server-side in get_analytics_data.php —
  // previously it silently fell through to 'this_month' and ignored date_from/date_to.
  $.ajax({
    url: '../php/get/get_analytics_data.php',
    method: 'POST',
    dataType: 'json',
    data: { period: 'custom', dept_id: dept, date_from: from, date_to: to },
    success(res) {
      if (res.success) {
        document.getElementById('statTotalFeedback').textContent = Number(res.kpi.total_responses).toLocaleString();
        document.getElementById('statTotalDepts').textContent    = res.kpi.dept_count;
      }
    }
  });
}

// ── Export — trigger download then refresh DB history ──
const exportTypeLabels = {
  feedback:    'Raw Feedback Records',
  summary:     'Department Summary',
  sqd:         'SQD Scores Report',
  departments: 'Departments Directory'
};
const exportTypeIcons = {
  feedback:    'bi-clipboard-data-fill',
  summary:     'bi-building-check',
  sqd:         'bi-bar-chart-steps',
  departments: 'bi-buildings-fill'
};

function doExport(type, format) {
  const from     = document.getElementById('filterDateFrom').value;
  const to       = document.getElementById('filterDateTo').value;
  const dept     = document.getElementById('filterDept').value;

  if (!from || !to) { alert('Please select a date range first.'); return; }

  const params = new URLSearchParams({
    type, format,
    dept_id:   dept,
    date_from: from,
    date_to:   to
  });

  // Trigger file download
  window.location.href = '../php/get/get_export_data.php?' + params.toString();

  // Refresh history after short delay (give server time to save the log)
  setTimeout(loadExportHistory, 1500);
}

// ── Load export history from DB ──
function loadExportHistory() {
  $.ajax({
    url: '../php/get/get_export_logs.php',
    method: 'GET',
    dataType: 'json',
    success(res) {
      if (!res.success) {
        console.error('[ExportData] get_export_logs returned success:false —', res.message || res);
        return;
      }
      renderLogFromDB(res.logs);
    },
    error(xhr) {
      // Was previously a silent failure point — now logs the raw response
      // so a broken get_export_logs.php shows up in the console immediately
      // instead of just leaving the history list stuck on "Loading…".
      console.error('[ExportData] get_export_logs request failed:', xhr.status, xhr.responseText);
    }
  });
}

function renderLogFromDB(logs) {
  const el = document.getElementById('exportLog');

  if (!logs || logs.length === 0) {
    el.innerHTML = `
      <div class="log-empty">
        <i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:8px;color:#ddd"></i>
        No export history yet. Click any Export button above to get started.
      </div>`;
    document.getElementById('logCount').textContent = '';
    return;
  }

  document.getElementById('logCount').textContent = logs.length + ' record' + (logs.length !== 1 ? 's' : '');

  const fmtDate = d => {
    if (!d) return '—';
    return new Date(d + 'T00:00:00').toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'});
  };

  el.innerHTML = logs.map(log => {
    const fmt      = log.export_format;
    const typeIcon = exportTypeIcons[log.export_type] || 'bi-download';
    const label    = exportTypeLabels[log.export_type] || log.export_type;
    const dept     = log.dept_name || 'All Departments';
    const records  = parseInt(log.record_count).toLocaleString();
    const badgeBg  = fmt === 'csv' ? '#f0f9f0' : '#e8f5e9';
    const badgeCl  = fmt === 'csv' ? '#1e7c3b' : '#155724';
    const ext      = fmt === 'excel' ? 'xls' : 'csv';

    return `
      <div class="log-item">
        <div class="log-icon ${fmt}">
          <i class="bi ${typeIcon}"></i>
        </div>
        <div class="log-name">
          <span style="font-weight:600;color:#222">${label}</span>
          <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:8px">
            ${escHtml(dept)} &nbsp;·&nbsp; ${fmtDate(log.date_from)} – ${fmtDate(log.date_to)}
            &nbsp;·&nbsp; <strong style="color:#555">${records}</strong> records
          </span>
          <span style="font-size:11px;color:#bbb;display:block;margin-top:2px">
            <i class="bi bi-person" style="font-size:10px"></i> ${escHtml(log.exported_by)}
          </span>
        </div>
        <span style="font-size:10px;background:${badgeBg};color:${badgeCl};padding:3px 9px;border-radius:10px;font-weight:600;margin-right:6px;flex-shrink:0">
          .${ext}
        </span>
        <div class="log-time" style="flex-shrink:0;text-align:right">
          <div>${log.export_date}</div>
          <div style="font-size:10px;color:#bbb">${log.export_time}</div>
        </div>
      </div>`;
  }).join('');
}

// ── Clear all history ──
function clearHistory() {
  if (!confirm('Clear all export history? This cannot be undone.')) return;
  $.ajax({
    url: '../php/get/get_export_logs.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'clear' },
    success(res) {
      if (res.success) loadExportHistory();
    }
  });
}

function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});

// ── Custom range detection ──
document.getElementById('filterDateFrom').addEventListener('change', () => {
  document.getElementById('quickRange').value = '';
});
document.getElementById('filterDateTo').addEventListener('change', () => {
  document.getElementById('quickRange').value = '';
});