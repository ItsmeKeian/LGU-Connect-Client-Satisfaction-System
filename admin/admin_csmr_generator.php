<?php
require "../php/auth_check.php";
requireSuperAdmin();
$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CSMR Generator | LGU-Connect</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<style>
:root { --red-main:#8B1A1A; --red-light:#f8f0f0; --red-border:#e8c4c4; --card-radius:12px; }

.csmr-wrapper { display:grid; grid-template-columns:340px 1fr; gap:24px; padding:24px; align-items:start; }
@media(max-width:1024px){ .csmr-wrapper{ grid-template-columns:1fr; } }

/* Filter panel */
.filter-panel { background:#fff; border-radius:var(--card-radius); border:1px solid #e8e8e8; overflow:hidden; position:sticky; top:24px; }
.filter-panel-header { background:var(--red-main); color:#fff; padding:18px 20px; display:flex; align-items:center; gap:10px; }
.filter-panel-header i { font-size:18px; opacity:.85; }
.filter-panel-header h2 { font-size:15px; font-weight:600; margin:0; }
.filter-panel-header small { font-size:11px; opacity:.7; display:block; margin-top:2px; }
.filter-body { padding:20px; }
.filter-group { margin-bottom:18px; }
.filter-group>label { font-size:12px; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:.06em; display:block; margin-bottom:7px; }
.filter-group select,
.filter-group input[type=date],
.filter-group input[type=text] { width:100%; padding:9px 12px; font-size:13.5px; border:1px solid #ddd; border-radius:7px; background:#fafafa; color:#222; transition:border-color .2s,box-shadow .2s; }
.filter-group select:focus,
.filter-group input:focus { outline:none; border-color:var(--red-main); box-shadow:0 0 0 3px rgba(139,26,26,.08); background:#fff; }
.date-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.date-row>label { grid-column:1/-1; }
.period-chips { display:flex; flex-wrap:wrap; gap:7px; margin-top:6px; }
.chip { padding:5px 13px; border-radius:20px; font-size:12px; font-weight:500; border:1px solid #ddd; background:#f5f5f5; color:#555; cursor:pointer; transition:all .18s; }
.chip:hover { border-color:var(--red-main); color:var(--red-main); background:var(--red-light); }
.chip.active { background:var(--red-main); color:#fff; border-color:var(--red-main); }
.filter-divider { border:none; border-top:1px solid #f0f0f0; margin:18px 0; }
.btn-generate { width:100%; background:var(--red-main); color:#fff; border:none; border-radius:8px; padding:13px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:9px; transition:background .2s,transform .1s; }
.btn-generate:hover { background:#6e1414; }
.btn-generate:active { transform:scale(.98); }
.btn-generate.loading { opacity:.7; pointer-events:none; }
.btn-preview-print { width:100%; margin-top:10px; background:transparent; color:var(--red-main); border:1px solid var(--red-border); border-radius:8px; padding:11px; font-size:13px; font-weight:500; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all .18s; }
.btn-preview-print:hover { background:var(--red-light); }

/* Preview panel */
.preview-panel { min-height:500px; }
.stat-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
@media(max-width:900px){ .stat-cards{ grid-template-columns:repeat(2,1fr); } }
.stat-card { background:#fff; border-radius:10px; border:1px solid #e8e8e8; padding:16px 18px; display:flex; flex-direction:column; gap:4px; }
.stat-card .stat-icon { width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:16px; margin-bottom:8px; }
.stat-card .stat-label { font-size:11.5px; color:#888; font-weight:500; text-transform:uppercase; letter-spacing:.05em; }
.stat-card .stat-value { font-size:26px; font-weight:700; color:#1a1a1a; line-height:1; }
.stat-card .stat-sub { font-size:11.5px; color:#aaa; margin-top:2px; }
.stat-card.red   .stat-icon { background:#fff0f0; color:var(--red-main); }
.stat-card.blue  .stat-icon { background:#eef5ff; color:#1a6fbf; }
.stat-card.green .stat-icon { background:#eef8f0; color:#1e7c3b; }
.stat-card.amber .stat-icon { background:#fff8ee; color:#b06c10; }
.preview-card { background:#fff; border-radius:var(--card-radius); border:1px solid #e8e8e8; overflow:hidden; }
.preview-card-header { padding:18px 22px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; }
.preview-card-header h3 { font-size:15px; font-weight:600; margin:0; color:#1a1a1a; }
.empty-state { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:80px 24px; text-align:center; }
.empty-icon { width:72px; height:72px; background:var(--red-light); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:30px; color:var(--red-main); margin-bottom:20px; }
.empty-state h4 { font-size:17px; font-weight:600; color:#1a1a1a; margin-bottom:8px; }
.empty-state p  { font-size:13.5px; color:#888; max-width:320px; margin:0; }
.spinner-wrap { display:none; padding:60px; text-align:center; }
.spinner { width:36px; height:36px; border:3px solid #f0f0f0; border-top-color:var(--red-main); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto 16px; }
@keyframes spin { to{ transform:rotate(360deg); } }
.spinner-wrap p { font-size:13px; color:#888; }
.dept-summary-grid { display:none; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; padding:20px 22px; }
.dept-summary-item { background:#fafafa; border:1px solid #efefef; border-radius:8px; padding:14px 16px; }
.dept-summary-item .dept-name { font-size:13px; font-weight:600; color:#222; margin-bottom:10px; display:flex; align-items:center; gap:7px; }
.dept-summary-item .dept-name i { color:var(--red-main); }
.dept-mini-stats { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; font-size:12px; }
.dept-mini-stat { text-align:center; }
.dept-mini-stat .val { font-size:18px; font-weight:700; color:#1a1a1a; }
.dept-mini-stat .lbl { color:#999; margin-top:2px; }
.results-table-wrapper { overflow-x:auto; display:none; }
.results-table { width:100%; border-collapse:collapse; font-size:13px; }
.results-table thead th { background:#fafafa; padding:11px 16px; text-align:left; font-size:11px; font-weight:600; color:#777; text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid #ececec; white-space:nowrap; }
.results-table tbody tr:hover { background:#fafafa; }
.results-table tbody td { padding:12px 16px; border-bottom:1px solid #f3f3f3; color:#333; vertical-align:middle; }
.results-table tbody tr:last-child td { border-bottom:none; }
.rating-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.rating-badge.excellent { background:#eef8f0; color:#1e7c3b; }
.rating-badge.good      { background:#eef5ff; color:#1a6fbf; }
.rating-badge.average   { background:#fff8ee; color:#b06c10; }
.rating-badge.poor      { background:#fff0f0; color:#c0392b; }
.sat-bar-wrap { display:flex; align-items:center; gap:8px; }
.sat-bar { flex:1; height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden; min-width:60px; }
.sat-bar-fill { height:100%; border-radius:3px; transition:width .6s ease; }
.period-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; background:var(--red-light); color:var(--red-main); border-radius:20px; font-size:12px; font-weight:500; }
</style>
</head>
<body>
<div class="app-shell">

  <aside class="sidebar" id="sidebar">
    <div class="sb-brand">
      <img src="../assets/img/logo.png" class="sb-logo-img" alt="Logo"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"/>
      <div class="sb-logo-fallback" style="display:none">SJ</div>
      <div class="sb-brand-text">
        <div class="sb-name">LGU<span>-Connect</span></div>
        <div class="sb-sub">San Julian, E. Samar</div>
      </div>
    </div>
    <div class="sb-role">
      <div class="role-dot"></div>
      <div>
        <div class="role-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
        <div class="role-sub">Super Administrator</div>
      </div>
    </div>
    <div class="sb-section">Main</div>
    <ul class="sb-nav">
      <li><a href="admin_dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span> Dashboard</a></li>
      <li><a href="admin_departments.php"><span class="nav-icon"><i class="bi bi-building"></i></span> Departments</a></li>
      <li><a href="admin_allfeedback.php"><span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> All Feedback <span class="nav-badge" id="sbFeedbackCount">0</span></a></li>
    </ul>
    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="admin_csmr_generator.php" class="active"><span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> CSMR Generator</a></li>
      <li><a href="admin_analytics.php"><span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics</a></li>
      <li><a href="admin_exportdata.php"><span class="nav-icon"><i class="bi bi-download"></i></span> Export Data</a></li>
    </ul>
    <div class="sb-section">System</div>
    <ul class="sb-nav">
      <li><a href="admin_manage_users.php"><span class="nav-icon"><i class="bi bi-people"></i></span> Manage Users</a></li>
      <li><a href="admin_qrcodes.php"><span class="nav-icon"><i class="bi bi-qr-code"></i></span> QR Codes</a></li>
      <li><a href="admin_settings.php"><span class="nav-icon"><i class="bi bi-gear"></i></span> Settings</a></li>
    </ul>
    <div class="sb-footer">
      <a href="../php/logout.php" onclick="return confirm('Sign out?')">
        <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span> Sign Out
      </a>
    </div>
  </aside>

  <div class="main-area">
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        CSMR Generator
        <span class="tb-subtitle">Client Satisfaction Measurement Report</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        <button class="tb-btn primary" onclick="generateReport()"><i class="bi bi-file-earmark-text"></i> Generate CSMR</button>
        <div class="tb-avatar" id="topbarAvatar" onclick="toggleAvatarDropdown(event)">
          <?= $avatarLetter ?>
          <div class="avatar-dropdown" id="avatarDropdown">
            <div class="av-header">
              <div class="av-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
              <div class="av-role">Super Administrator</div>
            </div>
            <div class="av-menu">
              <a href="admin_settings.php" class="av-item"><i class="bi bi-person-circle"></i> My Profile</a>
              <a href="admin_settings.php" class="av-item"><i class="bi bi-gear"></i> Settings</a>
              <div class="av-divider"></div>
              <a href="../php/logout.php" class="av-item danger" onclick="return confirm('Sign out?')"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="live-bar">
        <div class="live-dot"></div>
        <span class="live-text">Live &nbsp;&middot;&nbsp; Generate official CSMR reports per department or system-wide</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="csmr-wrapper">

        <!-- LEFT: Filter Panel -->
        <div class="filter-panel">
          <div class="filter-panel-header">
            <i class="bi bi-funnel-fill"></i>
            <div><h2>Report Filters</h2><small>Configure your CSMR parameters</small></div>
          </div>
          <div class="filter-body">

            <div class="filter-group">
              <label><i class="bi bi-building" style="margin-right:4px"></i> Department</label>
              <select id="filterDept">
                <option value="">All Departments (System-Wide)</option>
              </select>
            </div>

            <div class="filter-group">
              <label><i class="bi bi-calendar3" style="margin-right:4px"></i> Quick Period</label>
              <div class="period-chips">
                <span class="chip" data-period="today">Today</span>
                <span class="chip" data-period="this_week">This Week</span>
                <span class="chip active" data-period="this_month">This Month</span>
                <span class="chip" data-period="last_month">Last Month</span>
                <span class="chip" data-period="this_quarter">This Quarter</span>
                <span class="chip" data-period="this_year">This Year</span>
                <span class="chip" data-period="custom">Custom Range</span>
              </div>
            </div>

            <div class="filter-group date-row" id="customDateGroup" style="display:none">
              <label>Custom Date Range</label>
              <div><input type="date" id="filterDateFrom"/></div>
              <div><input type="date" id="filterDateTo"/></div>
            </div>

            <hr class="filter-divider">

            <div class="filter-group">
              <label><i class="bi bi-pencil" style="margin-right:4px"></i> Report Title <span style="color:#aaa;font-weight:400;text-transform:none">(optional)</span></label>
              <input type="text" id="filterTitle" placeholder="e.g. Q1 2026 Citizen Satisfaction Report"/>
            </div>

            <div class="filter-group">
              <label><i class="bi bi-list-check" style="margin-right:4px"></i> Include in Report</label>
              <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px">

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;text-transform:none;letter-spacing:0;color:#444;font-weight:400;">
                  <input type="checkbox" id="inclDeptBreakdown" checked style="width:14px;height:14px;accent-color:var(--red-main)"/>
                  <span><i class="bi bi-building" style="color:var(--red-main);font-size:12px;margin-right:3px"></i> Department Breakdown</span>
                </label>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;text-transform:none;letter-spacing:0;color:#444;font-weight:400;">
                  <input type="checkbox" id="inclCharts" checked style="width:14px;height:14px;accent-color:var(--red-main)"/>
                  <span><i class="bi bi-bar-chart-fill" style="color:var(--red-main);font-size:12px;margin-right:3px"></i> Charts &amp; Graphs <span style="font-size:11px;color:#aaa">(preview + print)</span></span>
                </label>

                <div>
                  <label style="display:flex;align-items:center;gap:8px;font-size:13px;text-transform:none;letter-spacing:0;color:#444;font-weight:400;">
                    <input type="checkbox" id="inclRawFeedback" style="width:14px;height:14px;accent-color:var(--red-main)" onchange="toggleRawSubOptions()"/>
                    <span><i class="bi bi-table" style="color:var(--red-main);font-size:12px;margin-right:3px"></i> Raw Feedback Table</span>
                  </label>
                  <!-- Sub-options: only active when Raw Feedback is checked -->
                  <div id="rawSubOptions" style="margin-left:22px;margin-top:7px;display:flex;flex-direction:column;gap:6px;opacity:0.35;pointer-events:none;transition:opacity 0.2s">
                    <label style="display:flex;align-items:center;gap:7px;font-size:12px;text-transform:none;letter-spacing:0;color:#555;font-weight:400;cursor:not-allowed">
                      <input type="checkbox" id="inclComments" checked style="width:13px;height:13px;accent-color:var(--red-main)"/>
                      <i class="bi bi-chat-left-text" style="color:#999;font-size:11px"></i> Show Comments &amp; Suggestions column
                    </label>
                  </div>
                </div>

              </div>
            </div>

            <hr class="filter-divider">

            <button class="btn-generate" id="generateBtn" onclick="generateReport()">
              <i class="bi bi-eye"></i> Preview Report
            </button>
            <button class="btn-preview-print" id="printBtn" onclick="openPrintView()" style="display:none">
              <i class="bi bi-printer"></i> Print / Export PDF
            </button>

          </div>
        </div>

        <!-- RIGHT: Preview Panel -->
        <div class="preview-panel">

          <div class="stat-cards" id="statCards" style="display:none">
            <div class="stat-card red">
              <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
              <div class="stat-label">Total Respondents</div>
              <div class="stat-value" id="statTotal">0</div>
              <div class="stat-sub" id="statPeriodLabel">—</div>
            </div>
            <div class="stat-card green">
              <div class="stat-icon"><i class="bi bi-emoji-smile-fill"></i></div>
              <div class="stat-label">Satisfaction Rate</div>
              <div class="stat-value" id="statSat">0%</div>
              <div class="stat-sub">Satisfied + Very Satisfied</div>
            </div>
            <div class="stat-card blue">
              <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
              <div class="stat-label">Avg. Rating</div>
              <div class="stat-value" id="statAvgRating">0</div>
              <div class="stat-sub">Out of 5.0</div>
            </div>
            <div class="stat-card amber">
              <div class="stat-icon"><i class="bi bi-building"></i></div>
              <div class="stat-label">Departments</div>
              <div class="stat-value" id="statDepts">0</div>
              <div class="stat-sub">With responses</div>
            </div>
          </div>

          <div class="preview-card">
            <div class="preview-card-header">
              <h3><i class="bi bi-file-earmark-bar-graph" style="color:var(--red-main);margin-right:7px"></i> Report Preview</h3>
              <div id="headerActions" style="display:none">
                <span class="period-badge"><i class="bi bi-calendar-range"></i> <span id="periodBadgeText">—</span></span>
              </div>
            </div>

            <div class="empty-state" id="emptyState">
              <div class="empty-icon"><i class="bi bi-file-earmark-text"></i></div>
              <h4>No Report Generated Yet</h4>
              <p>Configure the filters on the left, then click <strong>Preview Report</strong> to generate a summary before printing.</p>
            </div>

            <div class="spinner-wrap" id="spinnerWrap">
              <div class="spinner"></div>
              <p>Fetching feedback data…</p>
            </div>

            <div class="dept-summary-grid" id="deptSummaryGrid"></div>

            <!-- Charts Section -->
            <div id="chartsSection" style="display:none;padding:20px 22px;border-top:1px solid #f0f0f0">
              <div style="font-size:13px;font-weight:600;color:#333;margin-bottom:16px;display:flex;align-items:center;gap:8px">
                <i class="bi bi-bar-chart-fill" style="color:var(--red-main)"></i> Charts &amp; Graphs
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <!-- Rating Distribution Bar Chart -->
                <div style="background:#fafafa;border:1px solid #efefef;border-radius:8px;padding:16px">
                  <div style="font-size:12px;font-weight:600;color:#555;margin-bottom:12px;text-transform:uppercase;letter-spacing:.05em">
                    Rating Distribution
                  </div>
                  <div id="chartRatingBars"></div>
                </div>
                <!-- Department Satisfaction Chart -->
                <div style="background:#fafafa;border:1px solid #efefef;border-radius:8px;padding:16px">
                  <div style="font-size:12px;font-weight:600;color:#555;margin-bottom:12px;text-transform:uppercase;letter-spacing:.05em">
                    Satisfaction by Department
                  </div>
                  <div id="chartDeptSat"></div>
                </div>
              </div>
            </div>

            <!-- Comments Section -->
            <div id="commentsSection" style="display:none;border-top:1px solid #f0f0f0"></div>

            <div class="results-table-wrapper" id="resultsTableWrapper">
              <table class="results-table">
                <thead>
                  <tr>
                    <th>#</th><th>Department</th><th>Date</th>
                    <th>Respondent Type</th><th>Rating</th>
                    <th>Satisfaction</th><th>Comment</th>
                  </tr>
                </thead>
                <tbody id="resultsTableBody"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script>

// ── Date display ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Default date range: this month ──
const now      = new Date();
const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
const lastDay  = new Date(now.getFullYear(), now.getMonth() + 1, 0);
document.getElementById('filterDateFrom').value = firstDay.toISOString().split('T')[0];
document.getElementById('filterDateTo').value   = lastDay.toISOString().split('T')[0];

// ══════════════════════════════════════════════════════════
// FIX 1 — Load departments
// URL:  ../php/get/get_departments.php  ✅
// The success handler normalizes ALL possible response shapes:
//   • Plain array       → [ {id, name}, … ]
//   • {data:[…]}        → common wrapper
//   • {departments:[…]} → alternate wrapper
//   • {success, data}   → success-flag wrapper
// ══════════════════════════════════════════════════════════
$.ajax({
  url: '../php/get/get_departments.php',
  method: 'GET',
  success(res) {
    // get_departments.php returns: { success: true, data: [...] }
    // Each dept has: id, name, code, description, head, status
    // We must use d.code as option value (NOT d.id)
    // because feedback.department_code stores the code string (e.g. "BPLO")
    let depts = [];
    if (Array.isArray(res)) {
      depts = res;                             // plain array fallback
    } else if (res && Array.isArray(res.data)) {
      depts = res.data;                        // ✅ {success:true, data:[…]}
    } else if (res && Array.isArray(res.departments)) {
      depts = res.departments;                 // alternate wrapper fallback
    } else {
      console.warn('[CSMR] Unexpected get_departments response:', res);
    }

    const sel = document.getElementById('filterDept');
    depts.forEach(d => {
      const opt       = document.createElement('option');
      opt.value       = d.code;   // ✅ must be code, NOT id
      opt.textContent = d.name;
      sel.appendChild(opt);
    });
  },
  error(xhr) {
    // Log the raw response so you can see the PHP error immediately
    console.error('[CSMR] get_departments failed:', xhr.responseText);
  }
});

// ── Period chips ──
document.querySelectorAll('.chip[data-period]').forEach(chip => {
  chip.addEventListener('click', () => {
    document.querySelectorAll('.chip[data-period]').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    const period = chip.dataset.period;
    document.getElementById('customDateGroup').style.display = period === 'custom' ? 'grid' : 'none';
    if (period !== 'custom') setDateRange(period);
  });
});

function setDateRange(period) {
  const n = new Date();
  let from, to;
  switch (period) {
    case 'today':
      from = to = new Date(n.getFullYear(), n.getMonth(), n.getDate()); break;
    case 'this_week': {
      const dow = n.getDay() === 0 ? 6 : n.getDay() - 1;
      from = new Date(n); from.setDate(n.getDate() - dow);
      to   = new Date(n); to.setDate(n.getDate() + (6 - dow)); break;
    }
    case 'this_month':
      from = new Date(n.getFullYear(), n.getMonth(), 1);
      to   = new Date(n.getFullYear(), n.getMonth() + 1, 0); break;
    case 'last_month':
      from = new Date(n.getFullYear(), n.getMonth() - 1, 1);
      to   = new Date(n.getFullYear(), n.getMonth(), 0); break;
    case 'this_quarter': {
      const q = Math.floor(n.getMonth() / 3);
      from = new Date(n.getFullYear(), q * 3, 1);
      to   = new Date(n.getFullYear(), q * 3 + 3, 0); break;
    }
    case 'this_year':
      from = new Date(n.getFullYear(), 0, 1);
      to   = new Date(n.getFullYear(), 11, 31); break;
    default: return;
  }
  document.getElementById('filterDateFrom').value = from.toISOString().split('T')[0];
  document.getElementById('filterDateTo').value   = to.toISOString().split('T')[0];
}

// ══════════════════════════════════════════════════════════
// FIX 2 — Generate Report
// URL: ../php/get/get_csmr_data.php  ✅
// (was wrongly pointing to admin_csmr_generator.php which
//  is the HTML page, NOT a JSON endpoint)
// ══════════════════════════════════════════════════════════
let lastReportData = null;

function generateReport() {
  const dept     = document.getElementById('filterDept').value;
  const dateFrom = document.getElementById('filterDateFrom').value;
  const dateTo   = document.getElementById('filterDateTo').value;
  const inclDept = document.getElementById('inclDeptBreakdown').checked ? 1 : 0;
  const inclComm = document.getElementById('inclComments').checked ? 1 : 0;
  const inclCharts = document.getElementById('inclCharts').checked ? 1 : 0;
  const inclRaw  = document.getElementById('inclRawFeedback').checked ? 1 : 0;

  // Loading state
  document.getElementById('emptyState').style.display          = 'none';
  document.getElementById('spinnerWrap').style.display         = 'block';
  document.getElementById('statCards').style.display           = 'none';
  document.getElementById('deptSummaryGrid').style.display     = 'none';
  document.getElementById('resultsTableWrapper').style.display = 'none';
  document.getElementById('headerActions').style.display       = 'none';
  document.getElementById('generateBtn').classList.add('loading');

  $.ajax({
    url: '../php/get/get_csmr_data.php',   // ✅ dedicated JSON handler
    method: 'POST',
    dataType: 'json',
    data: { dept_id:dept, date_from:dateFrom, date_to:dateTo, incl_dept:inclDept, incl_raw:inclRaw },
    success(res) {
      if (!res.success) {
        alert('Error: ' + (res.message || 'Unknown error'));
        resetPreview(); return;
      }
      lastReportData = res;
      renderPreview(res, { inclDept, inclComm, inclRaw, inclCharts });
      document.getElementById('printBtn').style.display = 'flex';
    },
    error(xhr) {
      // Print the raw PHP error to console — very helpful for debugging
      console.error('[CSMR] get_csmr_data failed:', xhr.responseText);
      alert('Server error — open browser console (F12) for the full error message.');
      resetPreview();
    },
    complete() {
      document.getElementById('spinnerWrap').style.display = 'none';
      document.getElementById('generateBtn').classList.remove('loading');
    }
  });
}

// ── Toggle Raw Feedback sub-options ──
function toggleRawSubOptions() {
  const checked = document.getElementById('inclRawFeedback').checked;
  const sub     = document.getElementById('rawSubOptions');
  sub.style.opacity       = checked ? '1'    : '0.35';
  sub.style.pointerEvents = checked ? 'auto' : 'none';
}

function resetPreview() {
  document.getElementById('spinnerWrap').style.display    = 'none';
  document.getElementById('emptyState').style.display     = 'flex';
  document.getElementById('chartsSection').style.display  = 'none';
  document.getElementById('commentsSection').style.display= 'none';
}

function renderPreview(res, opts) {
  const { summary, departments, feedbacks } = res;

  // ── Stat cards ──
  document.getElementById('statTotal').textContent       = summary.total_responses;
  document.getElementById('statSat').textContent         = summary.satisfaction_rate + '%';
  document.getElementById('statAvgRating').textContent   = parseFloat(summary.avg_rating).toFixed(1);
  document.getElementById('statDepts').textContent       = summary.dept_count;
  document.getElementById('statPeriodLabel').textContent = summary.period_label;
  document.getElementById('statCards').style.display     = 'grid';
  document.getElementById('periodBadgeText').textContent = summary.period_label;
  document.getElementById('headerActions').style.display = 'flex';

  // ── Department breakdown ──
  if (opts.inclDept && departments && departments.length > 0) {
    const grid = document.getElementById('deptSummaryGrid');
    grid.innerHTML = '';
    departments.forEach(d => {
      const sat   = parseFloat(d.satisfaction_rate);
      const color = sat >= 80 ? '#1e7c3b' : sat >= 60 ? '#1a6fbf' : sat >= 40 ? '#b06c10' : '#c0392b';
      grid.innerHTML += `
        <div class="dept-summary-item">
          <div class="dept-name"><i class="bi bi-building"></i> ${escHtml(d.dept_name)}</div>
          <div class="dept-mini-stats">
            <div class="dept-mini-stat"><div class="val">${d.total_responses}</div><div class="lbl">Responses</div></div>
            <div class="dept-mini-stat"><div class="val" style="color:${color}">${sat}%</div><div class="lbl">Satisfied</div></div>
            <div class="dept-mini-stat"><div class="val">${parseFloat(d.avg_rating).toFixed(1)}</div><div class="lbl">Avg Rating</div></div>
          </div>
          <div style="margin-top:10px">
            <div class="sat-bar-wrap">
              <div class="sat-bar"><div class="sat-bar-fill" style="width:${sat}%;background:${color}"></div></div>
              <span style="font-size:11px;color:#888;white-space:nowrap">${sat}% sat.</span>
            </div>
          </div>
        </div>`;
    });
    grid.style.display = 'grid';
  }

  // ── Charts & Graphs ──
  if (opts.inclCharts) {
    renderCharts(summary, departments);
    document.getElementById('chartsSection').style.display = 'block';
  } else {
    document.getElementById('chartsSection').style.display = 'none';
  }

  // ── Raw Feedback Table ──
  if (opts.inclRaw && feedbacks && feedbacks.length > 0) {
    const inclComm = document.getElementById('inclComments').checked;
    const tbody    = document.getElementById('resultsTableBody');
    tbody.innerHTML = '';
    feedbacks.forEach((f, idx) => {
      const ri  = getRatingInfo(f.rating);
      const pct = (f.rating / 5 * 100).toFixed(0);
      const cmt = inclComm
        ? escHtml(f.comment || '—')
        : '<span style="color:#ccc;font-size:11px">Hidden</span>';
      tbody.innerHTML += `
        <tr>
          <td style="color:#aaa;font-size:12px">${idx+1}</td>
          <td><strong>${escHtml(f.dept_name)}</strong></td>
          <td style="white-space:nowrap;color:#888;font-size:12px">${escHtml(f.submitted_at)}</td>
          <td style="text-transform:capitalize">${escHtml((f.respondent_type||'—').replace(/_/g,' '))}</td>
          <td><span class="rating-badge ${ri.cls}">${ri.stars} ${ri.label}</span></td>
          <td style="min-width:120px">
            <div class="sat-bar-wrap">
              <div class="sat-bar"><div class="sat-bar-fill" style="width:${pct}%;background:${ri.color}"></div></div>
              <span style="font-size:11px;color:#888">${f.rating}/5</span>
            </div>
          </td>
          <td style="max-width:220px;font-size:12px;color:#555">${cmt}</td>
        </tr>`;
    });
    document.getElementById('resultsTableWrapper').style.display = 'block';
  }
}

// ── Chart rendering (pure CSS/HTML — no external library needed) ──
function renderCharts(summary, departments) {
  const total = parseInt(summary.total_responses) || 1;

  // Chart 1: Rating Distribution horizontal bars
  const ratings = [
    { label:'Excellent (5)', count: parseInt(summary.cnt_5||0), color:'#1e7c3b' },
    { label:'Good (4)',      count: parseInt(summary.cnt_4||0), color:'#1a6fbf' },
    { label:'Average (3)',   count: parseInt(summary.cnt_3||0), color:'#b06c10' },
    { label:'Poor (2)',      count: parseInt(summary.cnt_2||0), color:'#c0392b' },
    { label:'Very Poor (1)',count: parseInt(summary.cnt_1||0), color:'#922b21' },
  ];
  let ratingHTML = '';
  ratings.forEach(r => {
    const pct = Math.round(r.count / total * 100);
    ratingHTML += `
      <div style="margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#555;margin-bottom:3px">
          <span>${r.label}</span>
          <span style="font-weight:600;color:${r.color}">${r.count} <span style="color:#aaa;font-weight:400">(${pct}%)</span></span>
        </div>
        <div style="height:10px;background:#f0f0f0;border-radius:5px;overflow:hidden">
          <div style="height:100%;width:${pct}%;background:${r.color};border-radius:5px;transition:width .6s ease"></div>
        </div>
      </div>`;
  });
  document.getElementById('chartRatingBars').innerHTML = ratingHTML;

  // Chart 2: Department satisfaction horizontal bars
  if (departments && departments.length > 0) {
    let deptHTML = '';
    departments.forEach(d => {
      const sat   = parseFloat(d.satisfaction_rate) || 0;
      const color = sat >= 80 ? '#1e7c3b' : sat >= 60 ? '#1a6fbf' : sat >= 40 ? '#b06c10' : '#c0392b';
      // Truncate long dept names
      const name  = d.dept_name.length > 28 ? d.dept_name.substring(0,26)+'…' : d.dept_name;
      deptHTML += `
        <div style="margin-bottom:10px">
          <div style="display:flex;justify-content:space-between;font-size:11px;color:#555;margin-bottom:3px">
            <span title="${escHtml(d.dept_name)}">${escHtml(name)}</span>
            <span style="font-weight:600;color:${color}">${sat}%</span>
          </div>
          <div style="height:10px;background:#f0f0f0;border-radius:5px;overflow:hidden">
            <div style="height:100%;width:${sat}%;background:${color};border-radius:5px;transition:width .6s ease"></div>
          </div>
        </div>`;
    });
    document.getElementById('chartDeptSat').innerHTML = deptHTML;
  } else {
    document.getElementById('chartDeptSat').innerHTML =
      '<p style="font-size:12px;color:#aaa;text-align:center;padding:20px 0">Check "Department Breakdown" to see this chart</p>';
  }
}

function getRatingInfo(r) {
  r = parseFloat(r);
  if (r >= 4.5) return {cls:'excellent',label:'Excellent',stars:'★★★★★',color:'#1e7c3b'};
  if (r >= 3.5) return {cls:'good',     label:'Good',     stars:'★★★★☆',color:'#1a6fbf'};
  if (r >= 2.5) return {cls:'average',  label:'Average',  stars:'★★★☆☆',color:'#b06c10'};
  return              {cls:'poor',      label:'Poor',     stars:'★★☆☆☆',color:'#c0392b'};
}

function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function openPrintView() {
  if (!lastReportData) return;
  const params = new URLSearchParams({
    dept_id:       document.getElementById('filterDept').value,
    dept_name:     document.getElementById('filterDept').selectedOptions[0].text,
    date_from:     document.getElementById('filterDateFrom').value,
    date_to:       document.getElementById('filterDateTo').value,
    title:         document.getElementById('filterTitle').value,
    incl_comments: document.getElementById('inclComments').checked ? 1 : 0,
    incl_raw:      document.getElementById('inclRawFeedback').checked ? 1 : 0,
    incl_charts:   document.getElementById('inclCharts').checked ? 1 : 0
  });
  // ✅ Both files are in admin/ folder — no path prefix needed
  window.open('admin_csmr_generator_print.php?' + params.toString(), '_blank');
}

function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});
</script>
</body>
</html>