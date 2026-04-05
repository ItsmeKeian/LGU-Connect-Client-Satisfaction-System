
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard | LGU-Connect</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css"/>
</head>
<body>
<div class="app-shell">

  <!-- ══════════════ SIDEBAR ══════════════ -->
  <aside class="sidebar" id="sidebar">

    <div class="sb-brand">
      <img src="../assets/img/san_julian_logo.png" class="sb-logo-img" alt="Logo"
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
        <div class="role-name" id="sidebarUserName"></div>
        <div class="role-sub">Super Administrator</div>
      </div>
    </div>

    <div class="sb-section">Main</div>
    <ul class="sb-nav">
      <li><a href="admin_dashboard.php" class="active">
        <span class="nav-icon">&#9962;</span> Dashboard
      </a></li>
      <li><a href="departments.php">
        <span class="nav-icon">&#127970;</span> Departments
      </a></li>
      <li><a href="feedback.php">
        <span class="nav-icon">&#128203;</span> All Feedback
        <span class="nav-badge" id="sbFeedbackCount">0</span>
      </a></li>
    </ul>

    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="csmr_generator.php">
        <span class="nav-icon">&#128196;</span> CSMR Generator
      </a></li>
      <li><a href="analytics.php">
        <span class="nav-icon">&#128200;</span> Analytics
      </a></li>
      <li><a href="export.php">
        <span class="nav-icon">&#128228;</span> Export Data
      </a></li>
    </ul>

    <div class="sb-section">System</div>
    <ul class="sb-nav">
      <li><a href="users.php">
        <span class="nav-icon">&#128101;</span> Manage Users
      </a></li>
      <li><a href="qrcodes.php">
        <span class="nav-icon">&#9636;</span> QR Codes
      </a></li>
      <li><a href="settings.php">
        <span class="nav-icon">&#9881;</span> Settings
      </a></li>
    </ul>

    <div class="sb-footer">
      <a href="../logout.php">
        <span class="nav-icon">&#10548;</span> Sign Out
      </a>
    </div>

  </aside>
  <!-- ══════════════ /SIDEBAR ══════════════ -->

  <!-- ══════════════ MAIN AREA ══════════════ -->
  <div class="main-area">

    <!-- Topbar -->
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        System Overview
        <span class="tb-subtitle">All Departments</span>
      </div>
      <div class="topbar-actions">
        <div class="search-wrap">
          <span class="search-icon">&#128269;</span>
          <input type="text" class="tb-search" id="globalSearch" placeholder="Search departments..."/>
        </div>
        <button class="tb-btn" id="refreshBtn">&#8635; Refresh</button>
        <button class="tb-btn primary" onclick="location.href='csmr_generator.php'">
          &#128196; Generate CSMR
        </button>
        <div class="tb-avatar" id="topbarAvatar"></div>
      </div>
    </div>

    <!-- Page content -->
    <div class="page-content">

      <!-- Live bar -->
      <div class="live-bar">
        <div class="live-dot" id="liveDot"></div>
        <span class="live-text">
          Live &nbsp;&middot;&nbsp; Last updated: <span id="lastUpdated">just now</span>
        </span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- ── STAT CARDS ── -->
      <div class="stats-grid">
        <div class="stat-card sc-red">
          <div class="sc-top-bar"></div>
          <div class="sc-icon">&#128203;</div>
          <div class="sc-value" id="statTotal">—</div>
          <div class="sc-label">Total Feedback Received</div>
          <div class="sc-change sc-up" id="statTotalChange">&#9650; Loading...</div>
        </div>
        <div class="stat-card sc-gold">
          <div class="sc-top-bar"></div>
          <div class="sc-icon">&#11088;</div>
          <div class="sc-value" id="statAvg">—</div>
          <div class="sc-label">System-Wide Avg. Rating</div>
          <div class="sc-change sc-up" id="statAvgChange">&#9650; Loading...</div>
        </div>
        <div class="stat-card sc-grn">
          <div class="sc-top-bar"></div>
          <div class="sc-icon">&#127970;</div>
          <div class="sc-value" id="statDepts">—</div>
          <div class="sc-label">Active Departments</div>
          <div class="sc-change sc-neu" id="statDeptsChange">Loading...</div>
        </div>
        <div class="stat-card sc-blu">
          <div class="sc-top-bar"></div>
          <div class="sc-icon">&#128196;</div>
          <div class="sc-value" id="statReports">—</div>
          <div class="sc-label">Pending CSMR Reports</div>
          <div class="sc-change sc-down" id="statReportsChange">&#9660; Loading...</div>
        </div>
      </div>

      <!-- ── CHARTS ROW ── -->
      <div class="charts-grid">

        <div class="chart-card">
          <div class="cc-header">
            <div>
              <div class="cc-title">Satisfaction Trend</div>
              <div class="cc-sub">Monthly average — all departments</div>
            </div>
            <span class="badge badge-green">&#9650; Improving</span>
          </div>
          <canvas id="chartTrend" height="200"></canvas>
        </div>

        <div class="chart-card">
          <div class="cc-header">
            <div>
              <div class="cc-title">Department Comparison</div>
              <div class="cc-sub">Average rating per department</div>
            </div>
            <span class="badge badge-red">Q1 2026</span>
          </div>
          <canvas id="chartDeptBar" height="200"></canvas>
        </div>

        <div class="chart-card">
          <div class="cc-header">
            <div>
              <div class="cc-title">Service Quality Dimensions</div>
              <div class="cc-sub">System-wide SQD average scores</div>
            </div>
            <span class="badge badge-gold">ARTA SQD</span>
          </div>
          <canvas id="chartSQD" height="200"></canvas>
        </div>

        <div class="chart-card">
          <div class="cc-header">
            <div>
              <div class="cc-title">Feedback Volume</div>
              <div class="cc-sub">Daily submissions — last 14 days</div>
            </div>
            <span class="badge badge-blue">2 weeks</span>
          </div>
          <canvas id="chartVolume" height="200"></canvas>
        </div>

      </div>

      <!-- ── DEPARTMENT TABLE ── -->
      <div class="table-card">
        <div class="tc-header">
          <div class="tc-title">Department Performance Summary</div>
          <button class="tb-btn" onclick="location.href='csmr_generator.php'">
            Full Report &rarr;
          </button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Department</th>
              <th>Responses</th>
              <th>Avg Rating</th>
              <th>Satisfaction</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="deptTableBody">
            <!-- Populated by admin_dashboard.js -->
            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px">
              Loading department data...
            </td></tr>
          </tbody>
        </table>
      </div>

      <!-- ── BOTTOM ROW ── -->
      <div class="bottom-grid g-7-3">

        <!-- Recent Feedback -->
        <div class="table-card" style="margin-bottom:0">
          <div class="tc-header">
            <div class="tc-title">Recent Feedback</div>
            <button class="tb-btn" onclick="location.href='feedback.php'">View All</button>
          </div>
          <ul class="feedback-list" id="recentFeedbackList">
            <li style="padding:20px;text-align:center;color:var(--text-muted)">Loading...</li>
          </ul>
        </div>

        <!-- Right column -->
        <div class="col-stack">

          <!-- This month -->
          <div class="chart-card" style="padding:18px">
            <div class="cc-title" style="margin-bottom:14px">This Month</div>
            <div class="mini-grid" id="monthlyMiniStats">
              <div class="mini-stat"><div class="ms-val" style="color:var(--red)">—</div><div class="ms-label">Responses</div></div>
              <div class="mini-stat"><div class="ms-val" style="color:var(--gold-dark)">—</div><div class="ms-label">Avg Score</div></div>
              <div class="mini-stat"><div class="ms-val" style="color:var(--green)">—</div><div class="ms-label">Top Dept</div></div>
              <div class="mini-stat"><div class="ms-val" style="color:var(--blue)">—</div><div class="ms-label">Due Reports</div></div>
            </div>
          </div>

          <!-- Quick actions -->
          <div class="chart-card" style="padding:18px">
            <div class="cc-title" style="margin-bottom:14px">Quick Actions</div>
            <div class="action-list">
              <button class="action-btn primary" onclick="location.href='csmr_generator.php'">
                &#128196;&nbsp; Generate CSMR Report
              </button>
              <button class="action-btn" onclick="location.href='qrcodes.php'">
                &#9636;&nbsp; Manage QR Codes
              </button>
              <button class="action-btn" onclick="location.href='export.php'">
                &#128228;&nbsp; Export CSV / Excel
              </button>
              <button class="action-btn" onclick="location.href='users.php'">
                &#128101;&nbsp; Manage Users
              </button>
            </div>
          </div>

        </div>

      </div>
      <!-- /bottom grid -->

    </div>
    <!-- /page-content -->

  </div>
  <!-- /main-area -->

</div>
<!-- /app-shell -->

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../js/admin_dashboard.js"></script>

</body>
</html>