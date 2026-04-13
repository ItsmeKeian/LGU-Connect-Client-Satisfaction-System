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
<title>Analytics | LGU-Connect</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<style>
:root {
  --red-main: #8B1A1A;
  --red-light: #f8f0f0;
  --red-border: #e8c4c4;
  --card-radius: 12px;
}

/* ── Page layout ── */
.analytics-content { padding: 20px 24px 40px; }

/* ── Filter bar ── */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 22px;
  flex-wrap: wrap;
}
.filter-bar select,
.filter-bar input {
  padding: 8px 12px;
  font-size: 13px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
  color: #333;
  cursor: pointer;
}
.filter-bar select:focus,
.filter-bar input:focus {
  outline: none;
  border-color: var(--red-main);
  box-shadow: 0 0 0 3px rgba(139,26,26,.08);
}
.filter-bar .period-chips {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.chip {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  border: 1px solid #ddd;
  background: #f5f5f5;
  color: #555;
  cursor: pointer;
  transition: all .18s;
}
.chip:hover  { border-color: var(--red-main); color: var(--red-main); background: var(--red-light); }
.chip.active { background: var(--red-main); color: #fff; border-color: var(--red-main); }

.btn-load {
  background: var(--red-main);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background .2s;
  margin-left: auto;
}
.btn-load:hover { background: #6e1414; }
.btn-load.loading { opacity: .7; pointer-events: none; }

/* ── KPI Cards ── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 22px;
}
@media(max-width:900px){ .kpi-grid{ grid-template-columns: repeat(2,1fr); } }

.kpi-card {
  background: #fff;
  border-radius: var(--card-radius);
  border: 1px solid #e8e8e8;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: box-shadow .2s;
}
.kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
.kpi-icon {
  width: 46px; height: 46px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.kpi-card.red   .kpi-icon { background: #fff0f0; color: var(--red-main); }
.kpi-card.green .kpi-icon { background: #eef8f0; color: #1e7c3b; }
.kpi-card.blue  .kpi-icon { background: #eef5ff; color: #1a6fbf; }
.kpi-card.amber .kpi-icon { background: #fff8ee; color: #b06c10; }
.kpi-info { flex: 1; min-width: 0; }
.kpi-label { font-size: 11px; color: #999; font-weight: 500; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
.kpi-value { font-size: 28px; font-weight: 700; color: #1a1a1a; line-height: 1; }
.kpi-sub   { font-size: 11.5px; color: #aaa; margin-top: 3px; }

/* ── Chart cards ── */
.chart-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  margin-bottom: 18px;
}
.chart-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 18px;
  margin-bottom: 18px;
}
.chart-grid-full {
  margin-bottom: 18px;
}
@media(max-width:1024px){
  .chart-grid-2 { grid-template-columns: 1fr; }
  .chart-grid-3 { grid-template-columns: 1fr 1fr; }
}
@media(max-width:700px){
  .chart-grid-3 { grid-template-columns: 1fr; }
}

.chart-card {
  background: #fff;
  border-radius: var(--card-radius);
  border: 1px solid #e8e8e8;
  overflow: hidden;
}
.chart-card-header {
  padding: 14px 18px 12px;
  border-bottom: 1px solid #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.chart-card-header h4 {
  font-size: 13.5px;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 7px;
}
.chart-card-header h4 i { color: var(--red-main); }
.chart-card-header .chart-sub { font-size: 11px; color: #aaa; }
.chart-body { padding: 16px 18px; }
.chart-body-sm { padding: 12px 16px; }

/* ── SQD Score bars ── */
.sqd-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.sqd-label { font-size: 11px; color: #555; width: 130px; flex-shrink: 0; line-height: 1.3; }
.sqd-bar-wrap { flex: 1; background: #f0f0f0; border-radius: 4px; height: 10px; overflow: hidden; }
.sqd-bar-fill { height: 100%; border-radius: 4px; transition: width .8s ease; }
.sqd-score { font-size: 12px; font-weight: 600; color: #333; width: 36px; text-align: right; flex-shrink: 0; }

/* ── Department table ── */
.dept-analytics-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.dept-analytics-table thead th {
  background: #fafafa;
  padding: 10px 14px;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: .05em;
  border-bottom: 1px solid #ececec;
}
.dept-analytics-table thead th:not(:first-child) { text-align: center; }
.dept-analytics-table tbody td {
  padding: 11px 14px;
  border-bottom: 1px solid #f5f5f5;
  vertical-align: middle;
}
.dept-analytics-table tbody td:not(:first-child) { text-align: center; }
.dept-analytics-table tbody tr:last-child td { border-bottom: none; }
.dept-analytics-table tbody tr:hover td { background: #fafafa; }

.sat-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 9px;
  border-radius: 12px;
  font-size: 11.5px;
  font-weight: 600;
}
.sat-badge.high { background: #eef8f0; color: #1e7c3b; }
.sat-badge.mid  { background: #eef5ff; color: #1a6fbf; }
.sat-badge.low  { background: #fff8ee; color: #b06c10; }
.sat-badge.vlow { background: #fff0f0; color: #c0392b; }

.mini-bar-wrap { height: 6px; background: #f0f0f0; border-radius: 3px; overflow: hidden; min-width: 60px; }
.mini-bar-fill { height: 100%; border-radius: 3px; }

/* ── Recent comments ── */
.comment-feed { display: flex; flex-direction: column; gap: 12px; }
.comment-item {
  background: #fafafa;
  border: 1px solid #f0f0f0;
  border-left: 3px solid var(--red-main);
  border-radius: 0 8px 8px 0;
  padding: 11px 14px;
}
.comment-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
  flex-wrap: wrap;
}
.comment-dept  { font-size: 11.5px; font-weight: 600; color: #333; }
.comment-date  { font-size: 11px; color: #aaa; margin-left: auto; }
.comment-rating {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 11px;
  color: #f59e0b;
}
.comment-text { font-size: 12.5px; color: #555; line-height: 1.5; }

/* ── Spinner ── */
.spinner-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(255,255,255,.6);
  z-index: 999;
  align-items: center;
  justify-content: center;
}
.spinner-overlay.show { display: flex; }
.spinner {
  width: 40px; height: 40px;
  border: 3px solid #f0f0f0;
  border-top-color: var(--red-main);
  border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Empty state ── */
.empty-analytics {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 24px;
  text-align: center;
}
.empty-analytics i { font-size: 40px; color: #ddd; margin-bottom: 14px; }
.empty-analytics p { font-size: 13px; color: #aaa; }

/* ── Trend chart wrapper ── */
.trend-chart-wrap { position: relative; height: 200px; }
.donut-chart-wrap { position: relative; height: 200px; display: flex; align-items: center; justify-content: center; }
.bar-chart-wrap   { position: relative; height: 220px; }
</style>
</head>
<body>
<div class="app-shell">

  <!-- ══════════ SIDEBAR ══════════ -->
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
      <li><a href="admin_csmr_generator.php"><span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> CSMR Generator</a></li>
      <li><a href="admin_analytics.php" class="active"><span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics</a></li>
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

  <!-- ══════════ MAIN AREA ══════════ -->
  <div class="main-area">

    <!-- Topbar -->
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        Analytics
        <span class="tb-subtitle">Feedback Insights & Trends</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="loadAnalytics()">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="tb-btn primary" onclick="location.href='admin_csmr_generator.php'">
          <i class="bi bi-file-earmark-text"></i> Generate CSMR
        </button>
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
              <a href="../php/logout.php" class="av-item danger" onclick="return confirm('Sign out?')">
                <i class="bi bi-box-arrow-right"></i> Sign Out
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Content -->
    <div class="page-content">
      <div class="live-bar">
        <div class="live-dot"></div>
        <span class="live-text">Live &nbsp;&middot;&nbsp; Real-time feedback analytics across all departments</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="analytics-content">

        <!-- ── FILTER BAR ── -->
        <div class="filter-bar">
          <div class="period-chips">
            <span class="chip" data-period="today">Today</span>
            <span class="chip" data-period="this_week">This Week</span>
            <span class="chip active" data-period="this_month">This Month</span>
            <span class="chip" data-period="last_month">Last Month</span>
            <span class="chip" data-period="this_quarter">This Quarter</span>
            <span class="chip" data-period="this_year">This Year</span>
          </div>
          <select id="filterDept" style="min-width:200px">
            <option value="">All Departments</option>
          </select>
          <button class="btn-load" id="loadBtn" onclick="loadAnalytics()">
            <i class="bi bi-bar-chart-line"></i> Load Analytics
          </button>
        </div>

        <!-- ── KPI CARDS ── -->
        <div class="kpi-grid">
          <div class="kpi-card red">
            <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
              <div class="kpi-label">Total Responses</div>
              <div class="kpi-value" id="kpiTotal">—</div>
              <div class="kpi-sub" id="kpiPeriod">Select a period</div>
            </div>
          </div>
          <div class="kpi-card green">
            <div class="kpi-icon"><i class="bi bi-emoji-smile-fill"></i></div>
            <div class="kpi-info">
              <div class="kpi-label">Satisfaction Rate</div>
              <div class="kpi-value" id="kpiSat">—</div>
              <div class="kpi-sub">Rating ≥ 4 (Good/Excellent)</div>
            </div>
          </div>
          <div class="kpi-card blue">
            <div class="kpi-icon"><i class="bi bi-star-fill"></i></div>
            <div class="kpi-info">
              <div class="kpi-label">Average Rating</div>
              <div class="kpi-value" id="kpiAvg">—</div>
              <div class="kpi-sub" id="kpiRatingLabel">Out of 5.0</div>
            </div>
          </div>
          <div class="kpi-card amber">
            <div class="kpi-icon"><i class="bi bi-building"></i></div>
            <div class="kpi-info">
              <div class="kpi-label">Active Departments</div>
              <div class="kpi-value" id="kpiDepts">—</div>
              <div class="kpi-sub">With feedback received</div>
            </div>
          </div>
        </div>

        <!-- ── ROW 1: Trend + Rating Distribution ── -->
        <div class="chart-grid-2">

          <!-- Feedback Trend -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-graph-up-arrow"></i> Feedback Trend</h4>
              <span class="chart-sub" id="trendSubLabel">Daily submissions</span>
            </div>
            <div class="chart-body">
              <div class="trend-chart-wrap">
                <canvas id="chartTrend"></canvas>
              </div>
            </div>
          </div>

          <!-- Rating Distribution -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-bar-chart-fill"></i> Rating Distribution</h4>
              <span class="chart-sub">Count per rating level</span>
            </div>
            <div class="chart-body">
              <div class="bar-chart-wrap">
                <canvas id="chartRating"></canvas>
              </div>
            </div>
          </div>

        </div>

        <!-- ── ROW 2: SQD Scores (full width) ── -->
        <div class="chart-grid-full">
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-clipboard-data"></i> Service Quality Dimensions (SQD) Scores</h4>
              <span class="chart-sub">Average score per dimension (0–5)</span>
            </div>
            <div class="chart-body" id="sqdScoresBody">
              <div class="empty-analytics">
                <i class="bi bi-bar-chart"></i>
                <p>Click "Load Analytics" to view SQD scores</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ── ROW 3: Demographics (3 cols) ── -->
        <div class="chart-grid-3">

          <!-- By Respondent Type -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-person-badge"></i> Respondent Type</h4>
            </div>
            <div class="chart-body">
              <div class="donut-chart-wrap">
                <canvas id="chartType"></canvas>
              </div>
            </div>
          </div>

          <!-- By Sex -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-gender-ambiguous"></i> Sex Distribution</h4>
            </div>
            <div class="chart-body">
              <div class="donut-chart-wrap">
                <canvas id="chartSex"></canvas>
              </div>
            </div>
          </div>

          <!-- By Age Group -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-people"></i> Age Group</h4>
            </div>
            <div class="chart-body">
              <div class="donut-chart-wrap">
                <canvas id="chartAge"></canvas>
              </div>
            </div>
          </div>

        </div>

        <!-- ── ROW 4: Department table + Recent Comments ── -->
        <div class="chart-grid-2">

          <!-- Department Performance Table -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-building"></i> Department Performance</h4>
              <span class="chart-sub">Ranked by responses</span>
            </div>
            <div style="overflow-x:auto">
              <table class="dept-analytics-table">
                <thead>
                  <tr>
                    <th>Department</th>
                    <th>Responses</th>
                    <th>Avg Rating</th>
                    <th>Satisfied</th>
                    <th style="min-width:80px">Performance</th>
                  </tr>
                </thead>
                <tbody id="deptTableBody">
                  <tr><td colspan="5" style="text-align:center;padding:30px;color:#aaa;font-size:13px">Load analytics to see data</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Recent Comments -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4><i class="bi bi-chat-left-quote"></i> Recent Comments</h4>
              <span class="chart-sub">Latest citizen feedback</span>
            </div>
            <div class="chart-body" id="commentsBody">
              <div class="empty-analytics">
                <i class="bi bi-chat-left-text"></i>
                <p>Load analytics to see recent comments</p>
              </div>
            </div>
          </div>

        </div>

      </div><!-- /analytics-content -->
    </div><!-- /page-content -->
  </div><!-- /main-area -->
</div><!-- /app-shell -->

<!-- Spinner overlay -->
<div class="spinner-overlay" id="spinnerOverlay">
  <div class="spinner"></div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script>

// ── Chart instances (kept for destroy/recreate) ──
let chartTrend  = null;
let chartRating = null;
let chartType   = null;
let chartSex    = null;
let chartAge    = null;

// ── Date display ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Load departments ──
$.ajax({
  url: '../php/get/get_departments.php',
  method: 'GET',
  success(res) {
    const depts = Array.isArray(res) ? res : (res.data || res.departments || []);
    const sel   = document.getElementById('filterDept');
    depts.forEach(d => {
      const opt       = document.createElement('option');
      opt.value       = d.code;
      opt.textContent = d.name;
      sel.appendChild(opt);
    });
  }
});

// ── Period chips ──
document.querySelectorAll('.chip[data-period]').forEach(chip => {
  chip.addEventListener('click', () => {
    document.querySelectorAll('.chip[data-period]').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
  });
});

// ── Load analytics ──
function loadAnalytics() {
  const period = document.querySelector('.chip.active')?.dataset.period || 'this_month';
  const dept   = document.getElementById('filterDept').value;

  document.getElementById('spinnerOverlay').classList.add('show');
  document.getElementById('loadBtn').classList.add('loading');

  $.ajax({
    url: '../php/get/get_analytics_data.php',
    method: 'POST',
    dataType: 'json',
    data: { period, dept_id: dept },
    success(res) {
      if (!res.success) { alert('Error: ' + res.message); return; }
      renderKPIs(res.kpi, res.period);
      renderTrendChart(res.trend);
      renderRatingChart(res.kpi);
      renderSQDScores(res.kpi);
      renderDemoCharts(res.by_type, res.by_sex, res.by_age);
      renderDeptTable(res.by_dept);
      renderComments(res.recent_comments);
    },
    error(xhr) {
      console.error('Analytics error:', xhr.responseText);
      alert('Server error. Check console (F12).');
    },
    complete() {
      document.getElementById('spinnerOverlay').classList.remove('show');
      document.getElementById('loadBtn').classList.remove('loading');
    }
  });
}

// ── KPI Cards ──
function renderKPIs(kpi, period) {
  document.getElementById('kpiTotal').textContent      = Number(kpi.total_responses).toLocaleString();
  document.getElementById('kpiSat').textContent        = (kpi.satisfaction_rate || '0.0') + '%';
  document.getElementById('kpiAvg').textContent        = parseFloat(kpi.avg_rating || 0).toFixed(1);
  document.getElementById('kpiDepts').textContent      = kpi.dept_count;
  document.getElementById('kpiPeriod').textContent     = formatDate(period.from) + ' – ' + formatDate(period.to);
  document.getElementById('kpiRatingLabel').textContent = ratingLabel(kpi.avg_rating) + ' · Out of 5.0';
}

// ── Trend Chart (Line) ──
function renderTrendChart(trend) {
  const labels = trend.map(t => {
    const d = new Date(t.day);
    return d.toLocaleDateString('en-PH', { month:'short', day:'numeric' });
  });
  const data   = trend.map(t => parseInt(t.total));
  const avg    = trend.map(t => parseFloat(t.avg_rating));

  if (chartTrend) chartTrend.destroy();
  const ctx = document.getElementById('chartTrend').getContext('2d');
  chartTrend = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Responses',
          data,
          borderColor: '#8B1A1A',
          backgroundColor: 'rgba(139,26,26,.08)',
          borderWidth: 2,
          fill: true,
          tension: .35,
          pointRadius: 3,
          pointBackgroundColor: '#8B1A1A',
          yAxisID: 'y',
        },
        {
          label: 'Avg Rating',
          data: avg,
          borderColor: '#1a6fbf',
          backgroundColor: 'transparent',
          borderWidth: 1.5,
          borderDash: [4,3],
          fill: false,
          tension: .35,
          pointRadius: 2,
          pointBackgroundColor: '#1a6fbf',
          yAxisID: 'y2',
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { labels: { font: { size: 11 }, boxWidth: 12 } } },
      scales: {
        x:  { grid: { color: '#f5f5f5' }, ticks: { font: { size: 10 }, maxTicksLimit: 10 } },
        y:  { grid: { color: '#f5f5f5' }, ticks: { font: { size: 10 }, stepSize: 1 }, title: { display: true, text: 'Responses', font: { size: 10 } } },
        y2: { position: 'right', min: 0, max: 5, grid: { drawOnChartArea: false }, ticks: { font: { size: 10 } }, title: { display: true, text: 'Avg Rating', font: { size: 10 } } }
      }
    }
  });

  // Update subLabel
  document.getElementById('trendSubLabel').textContent =
    trend.length > 0 ? trend.length + ' day(s) of data' : 'No data for period';
}

// ── Rating Distribution Chart (Bar) ──
function renderRatingChart(kpi) {
  const labels = ['Excellent (5)','Good (4)','Average (3)','Poor (2)','Very Poor (1)'];
  const data   = [kpi.cnt_5, kpi.cnt_4, kpi.cnt_3, kpi.cnt_2, kpi.cnt_1].map(Number);
  const colors = ['#1e7c3b','#1a6fbf','#b06c10','#c0392b','#922b21'];

  if (chartRating) chartRating.destroy();
  const ctx = document.getElementById('chartRating').getContext('2d');
  chartRating = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Responses',
        data,
        backgroundColor: colors,
        borderRadius: 5,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { grid: { color: '#f5f5f5' }, ticks: { font: { size: 10 }, stepSize: 1 } }
      }
    }
  });
}

// ── SQD Scores (custom HTML bars) ──
function renderSQDScores(kpi) {
  const sqds = [
    { key:'sqd0', label:'SQD0 — Anti-Red Tape Awareness' },
    { key:'sqd1', label:'SQD1 — Service Speed & Timeliness' },
    { key:'sqd2', label:'SQD2 — Updated Service Info' },
    { key:'sqd3', label:'SQD3 — Staff Courtesy & Helpfulness' },
    { key:'sqd4', label:'SQD4 — No Unnecessary Requirements' },
    { key:'sqd5', label:'SQD5 — No Extra Payment Asked' },
    { key:'sqd6', label:'SQD6 — Simple & Fast Process' },
    { key:'sqd7', label:'SQD7 — Service Delivered as Promised' },
    { key:'sqd8', label:'SQD8 — Overall Satisfaction' },
  ];

  let html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 24px">';
  sqds.forEach(s => {
    const val  = parseFloat(kpi['avg_' + s.key] || 0);
    const pct  = Math.round(val / 5 * 100);
    const color= val >= 4 ? '#1e7c3b' : val >= 3 ? '#1a6fbf' : val >= 2 ? '#b06c10' : '#c0392b';
    html += `
      <div class="sqd-row">
        <div class="sqd-label">${s.label}</div>
        <div class="sqd-bar-wrap">
          <div class="sqd-bar-fill" style="width:${pct}%;background:${color}"></div>
        </div>
        <div class="sqd-score" style="color:${color}">${val.toFixed(2)}</div>
      </div>`;
  });
  html += '</div>';
  document.getElementById('sqdScoresBody').innerHTML = html;
}

// ── Donut charts (Type, Sex, Age) ──
function renderDemoCharts(byType, bySex, byAge) {
  const donutOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } }
    },
    cutout: '62%'
  };

  // Respondent type
  const typeLabels = byType.map(t => capitalize(t.respondent_type.replace(/_/g,' ')));
  const typeData   = byType.map(t => parseInt(t.total));
  const typeColors = ['#8B1A1A','#1a6fbf','#1e7c3b','#b06c10','#888'];
  if (chartType) chartType.destroy();
  chartType = new Chart(document.getElementById('chartType').getContext('2d'), {
    type: 'doughnut',
    data: { labels: typeLabels, datasets: [{ data: typeData, backgroundColor: typeColors, borderWidth: 0 }] },
    options: donutOpts
  });

  // Sex
  const sexMap    = { male:'#1a6fbf', female:'#e06090', prefer_not_to_say:'#aaa' };
  const sexLabels = bySex.map(s => capitalize(s.sex || 'Unknown'));
  const sexData   = bySex.map(s => parseInt(s.total));
  const sexColors = bySex.map(s => sexMap[s.sex] || '#aaa');
  if (chartSex) chartSex.destroy();
  chartSex = new Chart(document.getElementById('chartSex').getContext('2d'), {
    type: 'doughnut',
    data: { labels: sexLabels, datasets: [{ data: sexData, backgroundColor: sexColors, borderWidth: 0 }] },
    options: donutOpts
  });

  // Age group
  const ageLabels = byAge.map(a => (a.age_group || 'Unknown').replace(/_/g,' '));
  const ageData   = byAge.map(a => parseInt(a.total));
  const ageColors = ['#8B1A1A','#c0392b','#e67e22','#1a6fbf','#1e7c3b'];
  if (chartAge) chartAge.destroy();
  chartAge = new Chart(document.getElementById('chartAge').getContext('2d'), {
    type: 'doughnut',
    data: { labels: ageLabels, datasets: [{ data: ageData, backgroundColor: ageColors, borderWidth: 0 }] },
    options: donutOpts
  });
}

// ── Department table ──
function renderDeptTable(byDept) {
  const tbody = document.getElementById('deptTableBody');
  if (!byDept || byDept.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:30px;color:#aaa;font-size:13px">No department data</td></tr>';
    return;
  }
  tbody.innerHTML = '';
  byDept.forEach(d => {
    const sat     = parseFloat(d.satisfaction_rate);
    const cls     = sat >= 80 ? 'high' : sat >= 60 ? 'mid' : sat >= 40 ? 'low' : 'vlow';
    const barClr  = sat >= 80 ? '#1e7c3b' : sat >= 60 ? '#1a6fbf' : sat >= 40 ? '#b06c10' : '#c0392b';
    tbody.innerHTML += `
      <tr>
        <td><strong>${escHtml(d.dept_name)}</strong></td>
        <td>${d.total}</td>
        <td>${parseFloat(d.avg_rating).toFixed(1)} ★</td>
        <td><span class="sat-badge ${cls}">${sat}%</span></td>
        <td>
          <div class="mini-bar-wrap">
            <div class="mini-bar-fill" style="width:${sat}%;background:${barClr}"></div>
          </div>
        </td>
      </tr>`;
  });
}

// ── Recent Comments ──
function renderComments(comments) {
  const el = document.getElementById('commentsBody');
  if (!comments || comments.length === 0) {
    el.innerHTML = '<div class="empty-analytics"><i class="bi bi-chat-left-text"></i><p>No comments for this period</p></div>';
    return;
  }
  const stars = r => '★'.repeat(Math.round(r)) + '☆'.repeat(5 - Math.round(r));
  let html = '<div class="comment-feed">';
  comments.forEach(c => {
    html += `
      <div class="comment-item">
        <div class="comment-meta">
          <span class="comment-dept">${escHtml(c.dept_name)}</span>
          <span class="comment-rating">${stars(c.rating)} ${c.rating}/5</span>
          <span class="comment-date">${escHtml(c.submitted_at)}</span>
        </div>
        <div class="comment-text">"${escHtml(c.comment)}"</div>
      </div>`;
  });
  html += '</div>';
  el.innerHTML = html;
}

// ── Helpers ──
function ratingLabel(r) {
  r = parseFloat(r);
  if (r >= 4.21) return 'Excellent';
  if (r >= 3.41) return 'Good';
  if (r >= 2.61) return 'Average';
  if (r >= 1.81) return 'Poor';
  return 'Very Poor';
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
}
function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
}
function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});

// ── Auto-load on page open ──
loadAnalytics();
</script>
</body>
</html>