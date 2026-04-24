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
<link rel="stylesheet" href="../assets/css/admin_exportdata.css">
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
      <li><a href="admin_analytics.php"><span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics</a></li>
      <li><a href="admin_predictive.php">
        <span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span> Predictive Analytics
      </a></li>
      <li><a href="admin_exportdata.php" class="active"><span class="nav-icon"><i class="bi bi-download"></i></span> Export Data</a></li>
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
        Export Data
        <span class="tb-subtitle">Download feedback data as CSV or Excel</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="location.reload()">
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
        <span class="live-text">Live &nbsp;&middot;&nbsp; Export feedback records, summaries, and SQD scores</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="export-content">

        <!-- ── Quick Stats ── -->
        <div class="stats-bar" id="statsBar">
          <div class="stats-box">
            <i class="bi bi-clipboard-data"></i>
            <div>
              <div class="sv" id="statTotalFeedback">—</div>
              <div class="sl">Total Feedback Records</div>
            </div>
          </div>
          <div class="stats-box">
            <i class="bi bi-building"></i>
            <div>
              <div class="sv" id="statTotalDepts">—</div>
              <div class="sl">Active Departments</div>
            </div>
          </div>
          <div class="stats-box">
            <i class="bi bi-calendar-range"></i>
            <div>
              <div class="sv" id="statDateRange">—</div>
              <div class="sl">Selected Date Range</div>
            </div>
          </div>
        </div>

        <!-- ── Filters ── -->
        <div class="section-label"><i class="bi bi-funnel"></i> Export Filters</div>
        <div class="filters-panel">
          <div class="filter-field">
            <label><i class="bi bi-building" style="margin-right:3px"></i> Department</label>
            <select id="filterDept">
              <option value="">All Departments</option>
            </select>
          </div>
          <div class="filter-field">
            <label><i class="bi bi-calendar" style="margin-right:3px"></i> Date From</label>
            <input type="date" id="filterDateFrom"/>
          </div>
          <div class="filter-field">
            <label><i class="bi bi-calendar" style="margin-right:3px"></i> Date To</label>
            <input type="date" id="filterDateTo"/>
          </div>
          <div class="filter-field">
            <label>Quick Range</label>
            <select id="quickRange" onchange="applyQuickRange()">
              <option value="">Custom</option>
              <option value="this_month" selected>This Month</option>
              <option value="last_month">Last Month</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
              <option value="all_time">All Time</option>
            </select>
          </div>
          <div class="filter-field" style="margin-left:auto">
            <label>&nbsp;</label>
            <button onclick="updateStats()" style="padding:8px 18px;background:var(--red-main);color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px">
              <i class="bi bi-arrow-clockwise"></i> Update Preview
            </button>
          </div>
        </div>

        <!-- ── Export Cards ── -->
        <div class="section-label"><i class="bi bi-download"></i> Choose Export Type</div>
        <div class="export-grid">

          <!-- Raw Feedback -->
          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon red"><i class="bi bi-clipboard-data-fill"></i></div>
              <div class="export-card-info">
                <h3>Raw Feedback Records</h3>
                <p>All individual feedback submissions with full details including SQD scores, comments, and respondent demographics.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag highlight">ID</span>
              <span class="col-tag highlight">Department</span>
              <span class="col-tag highlight">Rating</span>
              <span class="col-tag">Respondent Type</span>
              <span class="col-tag">Sex</span>
              <span class="col-tag">Age Group</span>
              <span class="col-tag">SQD0–SQD8</span>
              <span class="col-tag">Comment</span>
              <span class="col-tag">Suggestions</span>
              <span class="col-tag">Date</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-export csv" onclick="doExport('feedback','csv')">
                <i class="bi bi-filetype-csv"></i> Export CSV
              </button>
              <button class="btn-export excel" onclick="doExport('feedback','excel')">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
              </button>
            </div>
          </div>

          <!-- Department Summary -->
          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon green"><i class="bi bi-building-check"></i></div>
              <div class="export-card-info">
                <h3>Department Summary</h3>
                <p>Aggregated statistics per department — total responses, average rating, satisfaction rate, and rating breakdown.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag highlight">Department</span>
              <span class="col-tag highlight">Total Responses</span>
              <span class="col-tag highlight">Avg Rating</span>
              <span class="col-tag highlight">Satisfaction %</span>
              <span class="col-tag">Excellent</span>
              <span class="col-tag">Good</span>
              <span class="col-tag">Average</span>
              <span class="col-tag">Poor</span>
              <span class="col-tag">Avg SQD0–8</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-export csv" onclick="doExport('summary','csv')">
                <i class="bi bi-filetype-csv"></i> Export CSV
              </button>
              <button class="btn-export excel" onclick="doExport('summary','excel')">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
              </button>
            </div>
          </div>

          <!-- SQD Scores -->
          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon blue"><i class="bi bi-bar-chart-steps"></i></div>
              <div class="export-card-info">
                <h3>SQD Scores Report</h3>
                <p>Service Quality Dimension scores per department — all 9 SQD dimensions with overall averages. Ideal for ARTA compliance.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag highlight">Department</span>
              <span class="col-tag highlight">SQD0–SQD8 Scores</span>
              <span class="col-tag highlight">Overall SQD Average</span>
              <span class="col-tag">Responses</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-export csv" onclick="doExport('sqd','csv')">
                <i class="bi bi-filetype-csv"></i> Export CSV
              </button>
              <button class="btn-export excel" onclick="doExport('sqd','excel')">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
              </button>
            </div>
          </div>

          <!-- Departments List -->
          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon amber"><i class="bi bi-buildings-fill"></i></div>
              <div class="export-card-info">
                <h3>Departments Directory</h3>
                <p>Complete list of all registered departments with their head officers, status, and total feedback received.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag highlight">Department Name</span>
              <span class="col-tag highlight">Code</span>
              <span class="col-tag highlight">Head Officer</span>
              <span class="col-tag">Status</span>
              <span class="col-tag">Total Feedback</span>
              <span class="col-tag">Avg Rating</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-export csv" onclick="doExport('departments','csv')">
                <i class="bi bi-filetype-csv"></i> Export CSV
              </button>
              <button class="btn-export excel" onclick="doExport('departments','excel')">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
              </button>
            </div>
          </div>

        </div>

        <!-- ── Export Log ── -->
        <div class="section-label">
          <i class="bi bi-clock-history"></i> Export History
          <span style="font-weight:400;color:#bbb;font-size:10px;text-transform:none;letter-spacing:0">(saved to database)</span>
        </div>
        <div class="export-log">
          <div class="export-log-header">
            <i class="bi bi-clock-history"></i> Recent Exports
            <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
              <span id="logCount" style="font-size:11px;color:#aaa;font-weight:400"></span>
              <button onclick="loadExportHistory()" style="background:none;border:1px solid #ddd;border-radius:6px;padding:4px 10px;font-size:11px;color:#666;cursor:pointer;display:flex;align-items:center;gap:4px">
                <i class="bi bi-arrow-clockwise"></i> Refresh
              </button>
              <button onclick="clearHistory()" style="background:none;border:1px solid #fcc;border-radius:6px;padding:4px 10px;font-size:11px;color:#c0392b;cursor:pointer;display:flex;align-items:center;gap:4px">
                <i class="bi bi-trash3"></i> Clear All
              </button>
            </div>
          </div>
          <div class="log-list" id="exportLog">
            <div class="log-empty">
              <i class="bi bi-hourglass" style="font-size:20px;display:block;margin-bottom:8px;color:#ddd"></i>
              Loading export history…
            </div>
          </div>
        </div>

      </div><!-- /export-content -->
    </div><!-- /page-content -->
  </div><!-- /main-area -->
</div><!-- /app-shell -->

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script src="../js/admin/admin_exportdata.js"></script>
</body>
</html>