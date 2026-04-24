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
<title>LGU-Connect | Municipality of San Julian</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_analytics.css">

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
      <li><a href="admin_predictive.php">
        <span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span> Predictive Analytics
      </a></li>
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
                <a href="admin_settings.php" class="av-item">
                  <i class="bi bi-person-circle"></i> My Profile
                </a>
                <a href="admin_settings.php" class="av-item">
                  <i class="bi bi-gear"></i> Settings
                </a>
                <div class="av-divider"></div>
                <a href="../php/logout.php" class="av-item danger"
                  onclick="return confirm('Are you sure you want to sign out?')">
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



<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script src="../js/admin/admin_analytics.js"></script>
</body>
</html>