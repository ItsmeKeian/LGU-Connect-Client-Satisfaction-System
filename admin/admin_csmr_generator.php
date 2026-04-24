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
<link rel="stylesheet" href="../assets/css/admin_csmr_generator.css">
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
<script src="../assets/js/mobile_toggle.js"></script>
<script src="../js/admin/admin_csmr_generator.js"></script>
</body>
</html>