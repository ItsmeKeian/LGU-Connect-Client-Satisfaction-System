<?php
require "../php/auth_check.php";
requireSuperAdmin();
require "../php/dbconnect.php";

$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));

// Get all departments for filter
$depts = $conn->query("SELECT code, name FROM departments WHERE status='active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Predictive Analytics | LGU-Connect</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<style>
:root { --red:#8B1A1A; --red-dark:#6e1414; --red-light:#fdf0f0; --red-border:#e8c4c4; }

.pa-content { padding: 24px; }

/* Health Score */
.health-card {
  background: linear-gradient(135deg, var(--red), var(--red-dark));
  border-radius: 16px;
  padding: 28px 32px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
  box-shadow: 0 6px 24px rgba(139,26,26,.3);
}
.health-left h2  { font-size: 15px; font-weight: 600; opacity: .85; margin: 0 0 6px; }
.health-left h1  { font-size: 42px; font-weight: 800; margin: 0 0 6px; line-height: 1; }
.health-left p   { font-size: 13px; opacity: .75; margin: 0; max-width: 420px; line-height: 1.5; }
.health-badge {
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.25);
  border-radius: 12px;
  padding: 14px 24px;
  text-align: center;
  flex-shrink: 0;
}
.health-badge .hb-val   { font-size: 40px; font-weight: 800; line-height: 1; }
.health-badge .hb-label { font-size: 12px; opacity: .75; margin-top: 4px; }

/* Filter bar */
.filter-bar {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e8e8e8;
  padding: 14px 18px;
  margin-bottom: 22px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.filter-bar label { font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .05em; }
.filter-bar select {
  padding: 7px 12px; font-size: 13px;
  border: 1px solid #ddd; border-radius: 7px;
  background: #fafafa; color: #333;
}
.filter-bar select:focus { outline: none; border-color: var(--red); }
.btn-load-pa {
  padding: 8px 20px;
  background: linear-gradient(135deg, var(--red), var(--red-dark));
  color: #fff; border: none; border-radius: 7px;
  font-size: 12.5px; font-weight: 700;
  cursor: pointer; display: flex; align-items: center; gap: 6px;
  transition: all .2s;
}
.btn-load-pa:hover { box-shadow: 0 4px 12px rgba(139,26,26,.3); }
.btn-load-pa:disabled { opacity: .7; pointer-events: none; }
@keyframes spin { to { transform: rotate(360deg); } }
.spin-anim { animation: spin .7s linear infinite; display: inline-block; }

/* Grid layouts */
.grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 20px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.grid-full { margin-bottom: 20px; }
@media(max-width:900px) { .grid-3,.grid-2 { grid-template-columns: 1fr; } }

/* Cards */
.pa-card { background: #fff; border-radius: 12px; border: 1px solid #e8e8e8; overflow: hidden; }
.pa-card-header {
  padding: 14px 18px 12px;
  border-bottom: 1px solid #f5f5f5;
  display: flex; align-items: center; justify-content: space-between;
}
.pa-card-header h4 {
  font-size: 13.5px; font-weight: 700; color: #1a1a1a; margin: 0;
  display: flex; align-items: center; gap: 7px;
}
.pa-card-header h4 i { color: var(--red); }
.pa-card-header span { font-size: 11px; color: #aaa; }
.pa-card-body { padding: 18px; }

/* Prediction cards (3 KPIs) */
.pred-card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e8e8e8;
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.pred-card-top { display: flex; align-items: flex-start; justify-content: space-between; }
.pred-icon {
  width: 42px; height: 42px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; flex-shrink: 0;
}
.pred-icon.blue   { background: #eef5ff; color: #1a6fbf; }
.pred-icon.orange { background: #fff4e6; color: #d04a02; }
.pred-icon.purple { background: #f3f0ff; color: #6741d9; }
.pred-direction {
  font-size: 11px; font-weight: 700; padding: 3px 10px;
  border-radius: 12px; display: flex; align-items: center; gap: 4px;
}
.pred-direction.up     { background: #eef8f0; color: #1e7c3b; }
.pred-direction.down   { background: #fff0f0; color: var(--red); }
.pred-direction.stable { background: #f5f5f5; color: #888; }
.pred-label { font-size: 11px; color: #999; font-weight: 500; text-transform: uppercase; letter-spacing: .05em; }
.pred-value { font-size: 28px; font-weight: 800; color: #1a1a1a; line-height: 1; }
.pred-sub   { font-size: 12px; color: #888; line-height: 1.5; }
.pred-forecast {
  background: #f8f8f8; border-radius: 8px; padding: 10px 12px;
  font-size: 12.5px; color: #555;
  display: flex; align-items: center; gap: 7px;
}
.pred-forecast strong { color: #1a1a1a; }

/* Risk alerts */
.risk-item {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 0; border-bottom: 1px solid #f5f5f5;
}
.risk-item:last-child { border-bottom: none; }
.risk-dot {
  width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.risk-dot.high     { background: #c0392b; box-shadow: 0 0 6px rgba(192,57,43,.4); }
.risk-dot.moderate { background: #e67e22; box-shadow: 0 0 6px rgba(230,126,34,.4); }
.risk-dot.none     { background: #1e7c3b; }
.risk-name  { flex: 1; font-size: 13.5px; font-weight: 600; color: #1a1a1a; }
.risk-meta  { font-size: 12px; color: #888; margin-top: 2px; }
.risk-badge {
  font-size: 11px; font-weight: 700; padding: 3px 10px;
  border-radius: 12px;
}
.risk-badge.high     { background: #fff0f0; color: #c0392b; }
.risk-badge.moderate { background: #fff4e6; color: #d04a02; }
.risk-badge.none     { background: #eef8f0; color: #1e7c3b; }
.risk-avg {
  font-size: 14px; font-weight: 700; width: 40px;
  text-align: right; flex-shrink: 0;
}

/* SQD analysis */
.sqd-analysis-row {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 0; border-bottom: 1px solid #f5f5f5;
}
.sqd-analysis-row:last-child { border-bottom: none; }
.sqd-analysis-label { font-size: 12.5px; color: #333; font-weight: 500; flex: 1; line-height: 1.3; }
.sqd-analysis-bar-wrap { width: 120px; height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden; flex-shrink: 0; }
.sqd-analysis-bar-fill { height: 100%; border-radius: 4px; transition: width .8s ease; }
.sqd-analysis-val { font-size: 12px; font-weight: 700; width: 36px; text-align: right; flex-shrink: 0; }
.sqd-status-badge {
  font-size: 10px; font-weight: 700; padding: 2px 8px;
  border-radius: 10px; white-space: nowrap; flex-shrink: 0;
}
.sqd-status-badge.good             { background: #eef8f0; color: #1e7c3b; }
.sqd-status-badge.needs_improvement{ background: #fff4e6; color: #d04a02; }
.sqd-status-badge.weak             { background: #fff0f0; color: #c0392b; }
.sqd-status-badge.critical         { background: #c0392b; color: #fff; }

/* Chart wrap */
.chart-wrap { position: relative; height: 220px; }

/* Recommendation */
.recommendation-box {
  background: linear-gradient(135deg, #f8f9ff, #eef2ff);
  border: 1px solid #d0d8f0;
  border-left: 4px solid #1a6fbf;
  border-radius: 0 10px 10px 0;
  padding: 14px 16px;
  display: flex; align-items: flex-start; gap: 12px;
}
.recommendation-box i { color: #1a6fbf; font-size: 20px; flex-shrink: 0; margin-top: 2px; }
.recommendation-box p { font-size: 13.5px; color: #333; margin: 0; line-height: 1.6; }

/* Spinner overlay */
.spinner-overlay { display:none; position:fixed; inset:0; background:rgba(255,255,255,.7); z-index:999; align-items:center; justify-content:center; }
.spinner-overlay.show { display:flex; }
.spinner-box { background:#fff; border-radius:12px; padding:30px 40px; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,.12); }
.spin-circle { width:40px; height:40px; border:3px solid #f0f0f0; border-top-color:var(--red); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto 12px; }
.spinner-box p { font-size:13px; color:#888; margin:0; }

/* Empty state */
.pa-empty { padding: 50px 20px; text-align: center; color: #bbb; }
.pa-empty i  { font-size: 48px; display: block; margin-bottom: 14px; opacity: .35; }
.pa-empty h4 { font-size: 15px; font-weight: 600; color: #999; margin-bottom: 6px; }
.pa-empty p  { font-size: 13px; }

/* Method badge */
.method-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: #eef5ff; color: #1a6fbf;
  font-size: 10px; font-weight: 700;
  padding: 3px 9px; border-radius: 10px;
  letter-spacing: .03em;
}
</style>
</head>
<body>
<div class="app-shell">

  <!-- ══ SIDEBAR ══ -->
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
      <li><a href="admin_analytics.php"><span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics</a></li>
      <li><a href="admin_predictive.php" class="active"><span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span> Predictive Analytics</a></li>
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

  <!-- ══ MAIN AREA ══ -->
  <div class="main-area">
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        Predictive Analytics
        <span class="tb-subtitle">Statistical Forecasting & Risk Detection</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="loadPredictions()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
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
              <div class="av-divider"></div>
              <a href="../php/logout.php" class="av-item danger" onclick="return confirm('Sign out?')">
                <i class="bi bi-box-arrow-right"></i> Sign Out
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="live-bar">
        <div class="live-dot"></div>
        <span class="live-text">Live &nbsp;·&nbsp; Predictive Analytics — Statistical Trend Analysis &amp; Forecasting</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="pa-content">

        <!-- Health Score (hidden until loaded) -->
        <div id="healthCard" style="display:none">
          <div class="health-card">
            <div class="health-left">
              <h2><i class="bi bi-activity me-2"></i> Overall Service Health</h2>
              <h1 id="healthLabel">—</h1>
              <p id="healthRec">Loading recommendation…</p>
            </div>
            <div class="health-badge">
              <div class="hb-val" id="healthScore">—</div>
              <div class="hb-label">Health Score</div>
            </div>
          </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar">
          <label><i class="bi bi-funnel me-1"></i> Filter:</label>
          <select id="filterDept">
            <option value="">All Departments (System-Wide)</option>
            <?php foreach ($depts as $d): ?>
            <option value="<?= htmlspecialchars($d['code']) ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn-load-pa" id="loadBtn" onclick="loadPredictions()">
            <i class="bi bi-graph-up-arrow"></i> Load Predictions
          </button>
          <span style="font-size:11px;color:#aaa;margin-left:auto">
            <i class="bi bi-info-circle me-1"></i>
            Uses last 6 months of data · Weighted Moving Average method
          </span>
        </div>

        <!-- Empty state -->
        <div class="pa-card" id="emptyState">
          <div class="pa-empty">
            <i class="bi bi-graph-up-arrow"></i>
            <h4>No Predictions Loaded</h4>
            <p>Click <strong>Load Predictions</strong> to generate analytics</p>
          </div>
        </div>

        <!-- Main content (hidden until loaded) -->
        <div id="paContent" style="display:none">

          <!-- 3 Prediction Cards -->
          <div class="grid-3" id="predCards"></div>

          <!-- Trend Chart + Risk Alerts -->
          <div class="grid-2">
            <div class="pa-card">
              <div class="pa-card-header">
                <h4><i class="bi bi-graph-up-arrow"></i> Satisfaction Trend Forecast</h4>
                <span class="method-badge"><i class="bi bi-calculator"></i> WMA</span>
              </div>
              <div class="pa-card-body">
                <div class="chart-wrap"><canvas id="chartTrend"></canvas></div>
              </div>
            </div>
            <div class="pa-card">
              <div class="pa-card-header">
                <h4><i class="bi bi-exclamation-triangle-fill"></i> Department Risk Alerts</h4>
                <span class="method-badge"><i class="bi bi-calculator"></i> Decline Detection</span>
              </div>
              <div class="pa-card-body" id="riskAlertsList">
                <div class="pa-empty" style="padding:20px">
                  <i class="bi bi-shield-check" style="font-size:32px;opacity:.4;display:block;margin-bottom:8px"></i>
                  <p style="font-size:13px;color:#bbb">No risk alerts detected</p>
                </div>
              </div>
            </div>
          </div>

          <!-- SQD Analysis -->
          <div class="grid-full">
            <div class="pa-card">
              <div class="pa-card-header">
                <h4><i class="bi bi-clipboard-data-fill"></i> SQD Weak Point Detector</h4>
                <span class="method-badge"><i class="bi bi-calculator"></i> Threshold Analysis</span>
              </div>
              <div class="pa-card-body" id="sqdAnalysisList"></div>
            </div>
          </div>

          <!-- Recommendation -->
          <div class="grid-full">
            <div class="recommendation-box" id="recommendationBox">
              <i class="bi bi-lightbulb-fill"></i>
              <p id="recommendationText">—</p>
            </div>
          </div>

        </div><!-- /paContent -->
      </div>
    </div>
  </div>
</div>

<div class="spinner-overlay" id="spinnerOverlay">
  <div class="spinner-box">
    <div class="spin-circle"></div>
    <p>Running predictions…</p>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/admin_sidebarcount.js"></script>
<script>
let chartTrend = null;

document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
document.getElementById('menuToggle')?.addEventListener('click',()=>
  document.getElementById('sidebar').classList.toggle('sb-open'));
function toggleAvatarDropdown(e){e.stopPropagation();document.getElementById('avatarDropdown').classList.toggle('show');}
document.addEventListener('click',()=>document.getElementById('avatarDropdown')?.classList.remove('show'));

function loadPredictions(){
  const dept = document.getElementById('filterDept').value;
  const btn  = document.getElementById('loadBtn');

  document.getElementById('spinnerOverlay').classList.add('show');
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split spin-anim"></i> Processing…';

  $.ajax({
    url: '../php/get/get_predictive_data.php',
    method: 'GET',
    data: { dept },
    dataType: 'json',
    success(res){
      if (!res.success){ alert('Error: '+(res.message||'Failed.')); return; }
      const d = res.data;

      renderHealthCard(d.summary);
      renderPredCards(d);
      renderTrendChart(d.trend);
      renderRiskAlerts(d.risk);
      renderSQDAnalysis(d.sqd);
      renderRecommendation(d.summary);

      document.getElementById('emptyState').style.display = 'none';
      document.getElementById('paContent').style.display  = 'block';
      document.getElementById('healthCard').style.display = 'block';
    },
    error(xhr){ console.error(xhr.responseText); alert('Server error.'); },
    complete(){
      document.getElementById('spinnerOverlay').classList.remove('show');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-graph-up-arrow"></i> Load Predictions';
    }
  });
}

function renderHealthCard(summary){
  const score = summary.health_score;
  const color = score >= 80 ? '#1e7c3b' : score >= 65 ? '#b06c10' : '#c0392b';
  $('#healthScore').text(score + '%');
  $('#healthLabel').text(summary.health_label);
  $('#healthRec').text(summary.recommendation);
}

function renderPredCards(d){
  const trend = d.trend;
  const dirIcon  = trend.direction==='improving'?'↑':trend.direction==='declining'?'↓':'→';
  const dirClass = trend.direction==='improving'?'up':trend.direction==='declining'?'down':'stable';
  const dirLabel = trend.direction==='improving'?'Improving':trend.direction==='declining'?'Declining':'Stable';

  const highRisk = d.risk.high_risk_count;
  const modRisk  = d.risk.mod_risk_count;
  const riskLabel = highRisk > 0 ? `${highRisk} High Risk` : modRisk > 0 ? `${modRisk} Moderate Risk` : 'All Clear';
  const riskClass = highRisk > 0 ? 'down' : modRisk > 0 ? 'stable' : 'up';

  const weakCount = d.sqd.weak_count;
  const sqdClass  = weakCount > 0 ? 'down' : d.sqd.improve_count > 0 ? 'stable' : 'up';
  const sqdLabel  = weakCount > 0 ? `${weakCount} Weak Dimension${weakCount>1?'s':''}` : d.sqd.improve_count > 0 ? `${d.sqd.improve_count} Need Improvement` : 'All Dimensions Good';

  document.getElementById('predCards').innerHTML = `
    <!-- Forecast Card -->
    <div class="pred-card">
      <div class="pred-card-top">
        <div class="pred-icon blue"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="pred-direction ${dirClass}">${dirIcon} ${dirLabel}</div>
      </div>
      <div class="pred-label">Satisfaction Trend</div>
      <div class="pred-value">${trend.wma_sat !== null ? trend.wma_sat+'%' : '—'}</div>
      <div class="pred-sub">Weighted moving average<br>Last 6 months of data</div>
      <div class="pred-forecast">
        <i class="bi bi-calendar-event" style="color:#1a6fbf"></i>
        Next month forecast: <strong>${trend.forecast_sat !== null ? trend.forecast_sat+'%' : 'Insufficient data'}</strong>
      </div>
    </div>

    <!-- Risk Card -->
    <div class="pred-card">
      <div class="pred-card-top">
        <div class="pred-icon orange"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="pred-direction ${riskClass}">${riskLabel}</div>
      </div>
      <div class="pred-label">Department Risk Status</div>
      <div class="pred-value">${highRisk + modRisk}</div>
      <div class="pred-sub">Departments flagged<br>${highRisk} high risk · ${modRisk} moderate</div>
      <div class="pred-forecast">
        <i class="bi bi-info-circle" style="color:#d04a02"></i>
        Based on <strong>consecutive decline detection</strong>
      </div>
    </div>

    <!-- SQD Card -->
    <div class="pred-card">
      <div class="pred-card-top">
        <div class="pred-icon purple"><i class="bi bi-clipboard-data-fill"></i></div>
        <div class="pred-direction ${sqdClass}">${sqdLabel}</div>
      </div>
      <div class="pred-label">SQD Health</div>
      <div class="pred-value">${d.sqd.overall_avg > 0 ? d.sqd.overall_avg+'/5' : '—'}</div>
      <div class="pred-sub">Overall SQD average<br>${d.sqd.good_count} good · ${d.sqd.improve_count} needs improvement · ${d.sqd.weak_count} weak</div>
      <div class="pred-forecast">
        <i class="bi bi-info-circle" style="color:#6741d9"></i>
        Based on <strong>threshold analysis</strong> (last 3 months)
      </div>
    </div>`;
}

function renderTrendChart(trend){
  if (!trend.monthly_data.length) return;

  const labels   = trend.monthly_data.map(m => m.month_label);
  const satData  = trend.monthly_data.map(m => parseFloat(m.satisfaction_rate)||0);
  const ratingData = trend.monthly_data.map(m => parseFloat(m.avg_rating)||0);

  // Forecast point
  const forecastLabel = 'Next Month (Forecast)';
  if (trend.forecast_sat !== null){
    labels.push(forecastLabel);
    satData.push(trend.forecast_sat);
    ratingData.push(trend.wma_rating);
  }

  // Trend line (linear regression on sat data)
  const n = satData.length;
  const indices = satData.map((_,i)=>i);
  const sumX = indices.reduce((a,b)=>a+b,0);
  const sumY = satData.reduce((a,b)=>a+b,0);
  const sumXY = indices.reduce((s,x,i)=>s+x*satData[i],0);
  const sumX2 = indices.reduce((s,x)=>s+x*x,0);
  const slope = (n*sumXY - sumX*sumY) / (n*sumX2 - sumX*sumX);
  const intercept = (sumY - slope*sumX) / n;
  const trendLine = indices.map(x => Math.round((intercept + slope*x)*10)/10);

  if (chartTrend) chartTrend.destroy();
  chartTrend = new Chart(document.getElementById('chartTrend'),{
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Satisfaction Rate %',
          data: satData,
          borderColor: '#8B1A1A',
          backgroundColor: 'rgba(139,26,26,.08)',
          borderWidth: 2.5, tension: .35, fill: true,
          pointBackgroundColor: satData.map((_,i)=> i===satData.length-1&&trend.forecast_sat!==null ? '#1a6fbf' : '#8B1A1A'),
          pointRadius: satData.map((_,i)=> i===satData.length-1&&trend.forecast_sat!==null ? 6 : 4),
          pointStyle: satData.map((_,i)=> i===satData.length-1&&trend.forecast_sat!==null ? 'star' : 'circle'),
        },
        {
          label: 'Trend Line',
          data: trendLine,
          borderColor: '#1a6fbf',
          backgroundColor: 'transparent',
          borderWidth: 1.5, borderDash: [5,4], tension: 0, fill: false,
          pointRadius: 0,
        }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { labels: { font: { size: 11 }, boxWidth: 12 } },
        tooltip: {
          callbacks: {
            label: (item) => `${item.dataset.label}: ${item.parsed.y}${item.dataset.label.includes('%')?'%':''}`
          }
        }
      },
      scales: {
        y: { min: 0, max: 100, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { callback: v => v+'%' } },
        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
      }
    }
  });
}

function renderRiskAlerts(risk){
  const el = document.getElementById('riskAlertsList');
  if (!risk.alerts.length) {
    el.innerHTML = `<div style="padding:20px;text-align:center">
      <i class="bi bi-shield-check" style="font-size:32px;color:#1e7c3b;display:block;margin-bottom:8px;opacity:.6"></i>
      <p style="font-size:13px;color:#aaa">No departments at risk. All performing well! ✅</p>
    </div>`;
    return;
  }

  el.innerHTML = risk.alerts.map(a => {
    const changeStr = a.change >= 0 ? `+${a.change}` : `${a.change}`;
    const changeColor = a.change >= 0 ? '#1e7c3b' : '#c0392b';
    return `
      <div class="risk-item">
        <div class="risk-dot ${a.risk_level}"></div>
        <div style="flex:1">
          <div class="risk-name">${escHtml(a.dept_name)}</div>
          <div class="risk-meta">
            ${a.declines} consecutive decline${a.declines!==1?'s':''} ·
            <span style="color:${changeColor};font-weight:600">${changeStr} change</span>
          </div>
        </div>
        <div class="risk-avg" style="color:${a.current_avg>=4?'#1e7c3b':a.current_avg>=3?'#b06c10':'#c0392b'}">
          ${a.current_avg.toFixed(1)}
        </div>
        <div class="risk-badge ${a.risk_level}">
          ${a.risk_level.charAt(0).toUpperCase()+a.risk_level.slice(1)} Risk
        </div>
      </div>`;
  }).join('');
}

function renderSQDAnalysis(sqd){
  const el = document.getElementById('sqdAnalysisList');
  if (!sqd.analysis.length) {
    el.innerHTML = '<p style="color:#bbb;text-align:center;padding:20px">No SQD data available for the selected period.</p>';
    return;
  }

  const statusLabels = {
    good: 'Good',
    needs_improvement: 'Needs Improvement',
    weak: 'Weak',
    critical: 'Critical'
  };
  const barColors = {
    good: '#1e7c3b',
    needs_improvement: '#b06c10',
    weak: '#c0392b',
    critical: '#7b1010'
  };

  el.innerHTML = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 24px">' +
    sqd.analysis.map(s => `
      <div class="sqd-analysis-row">
        <div style="width:46px;font-size:11px;font-weight:700;color:#666;flex-shrink:0">${s.key.toUpperCase()}</div>
        <div class="sqd-analysis-label">${escHtml(s.label)}</div>
        <div class="sqd-analysis-bar-wrap">
          <div class="sqd-analysis-bar-fill" style="width:${s.pct}%;background:${barColors[s.status]}"></div>
        </div>
        <div class="sqd-analysis-val" style="color:${barColors[s.status]}">${s.avg.toFixed(2)}</div>
        <div class="sqd-status-badge ${s.status}">${statusLabels[s.status]}</div>
      </div>`).join('') + '</div>' +
    `<div style="margin-top:14px;padding:12px 14px;background:#f8f8f8;border-radius:8px;font-size:12px;color:#555">
      <strong>Legend:</strong>
      <span style="color:#1e7c3b;margin-left:12px">● Good (≥4.0)</span>
      <span style="color:#b06c10;margin-left:12px">● Needs Improvement (3.5–3.99)</span>
      <span style="color:#c0392b;margin-left:12px">● Weak (3.0–3.49)</span>
      <span style="color:#7b1010;margin-left:12px">● Critical (&lt;3.0)</span>
    </div>`;
}

function renderRecommendation(summary){
  document.getElementById('recommendationText').textContent = summary.recommendation;
}

function escHtml(s){
  if(!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Auto-load on page open
loadPredictions();
</script>
</body>
</html>