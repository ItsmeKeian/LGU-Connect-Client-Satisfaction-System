
<?php
// department/dept_dashboard.php
require "../php/auth_check.php";

// Block superadmin from dept dashboard
if (IS_SUPERADMIN) {
    header("Location: ../admin/admin_dashboard.php");
    exit();
}

// Now safe to use — all queries filtered by department
$dept = CURRENT_DEPT; // e.g. "MSWD"
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>LGU-Connect | Municipality of San Julian</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css"/>
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
        <div class="role-name" id="sidebarUserName">Department Admin</div>
        <div class="role-sub">Department Administrator</div>
      </div>
    </div>

    <div class="sb-section">My Department</div>
    <ul class="sb-nav">
      <li><a href="dept_dashboard.php" class="active">
        <span class="nav-icon">&#9962;</span> My Dashboard
      </a></li>
      <li><a href="feedback.php">
        <span class="nav-icon">&#128203;</span> Feedback Inbox
        <span class="nav-badge" id="sbFeedbackCount">0</span>
      </a></li>
      <li><a href="qrcode.php">
        <span class="nav-icon">&#9636;</span> My QR Code
      </a></li>
    </ul>

    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="my_csmr.php">
        <span class="nav-icon">&#128196;</span> Generate CSMR
      </a></li>
      <li><a href="analytics.php">
        <span class="nav-icon">&#128200;</span> My Analytics
      </a></li>
      <li><a href="export.php">
        <span class="nav-icon">&#128228;</span> Export Data
      </a></li>
    </ul>

    <div class="sb-section">Account</div>
    <ul class="sb-nav">
      <li><a href="profile.php">
        <span class="nav-icon">&#128100;</span> My Profile
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
  <!-- /SIDEBAR -->

  <!-- ══════════════ MAIN AREA ══════════════ -->
  <div class="main-area">

    <!-- Topbar -->
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title" id="topbarTitle">
       
        <span class="tb-subtitle">Department Dashboard</span>
      </div>
      <div class="topbar-actions">
        <div class="search-wrap">
          <span class="search-icon">&#128269;</span>
          <input type="text" class="tb-search" id="fbSearch" placeholder="Search feedback..."/>
        </div>
        <button class="tb-btn" id="refreshBtn">&#8635; Refresh</button>
        <button class="tb-btn primary" onclick="location.href='my_csmr.php'">
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
          <div class="sc-label">Total Responses</div>
          <div class="sc-change sc-up" id="statTotalChange">&#9650; Loading...</div>
        </div>
        <div class="stat-card sc-gold">
          <div class="sc-top-bar"></div>
          <div class="sc-icon">&#11088;</div>
          <div class="sc-value" id="statAvg">—</div>
          <div class="sc-label">Dept. Avg. Rating</div>
          <div class="sc-change sc-up" id="statAvgChange">&#9650; Loading...</div>
        </div>
        <div class="stat-card sc-grn">
          <div class="sc-top-bar"></div>
          <div class="sc-icon">&#128200;</div>
          <div class="sc-value" id="statSatisfaction">—</div>
          <div class="sc-label">Satisfaction Rate</div>
          <div class="sc-change sc-up" id="statSatChange">&#9650; Loading...</div>
        </div>
        <div class="stat-card sc-blu">
          <div class="sc-top-bar"></div>
          <div class="sc-icon">&#128197;</div>
          <div class="sc-value" id="statMonth">—</div>
          <div class="sc-label">Responses This Month</div>
          <div class="sc-change sc-neu" id="statMonthChange">Loading...</div>
        </div>
      </div>

      <!-- ── CHARTS ROW ── -->
      <div class="charts-grid">

        <div class="chart-card">
          <div class="cc-header">
            <div>
              <div class="cc-title">SQD Breakdown</div>
              <div class="cc-sub">Score per ARTA quality dimension</div>
            </div>
            <span class="badge badge-red">ARTA Indicators</span>
          </div>
          <canvas id="chartSQD" height="200"></canvas>
        </div>

        <div class="chart-card">
          <div class="cc-header">
            <div>
              <div class="cc-title">Rating Distribution</div>
              <div class="cc-sub">How citizens rated this department</div>
            </div>
            <span class="badge badge-gold">All Time</span>
          </div>
          <canvas id="chartDist" height="200"></canvas>
        </div>

        <div class="chart-card full">
          <div class="cc-header">
            <div>
              <div class="cc-title">Monthly Feedback Trend</div>
              <div class="cc-sub">Volume and average rating — last 8 months</div>
            </div>
            <span class="badge badge-green">&#9650; On track</span>
          </div>
          <canvas id="chartTrend" height="110"></canvas>
        </div>

      </div>

      <!-- ── BOTTOM ROW ── -->
      <div class="bottom-grid g-7-3">

        <!-- Left: SQD table + Feedback list -->
        <div class="col-stack" style="min-width:0">

          <!-- SQD Score Card -->
          <div class="table-card" style="margin-bottom:0">
            <div class="tc-header">
              <div class="tc-title">SQD Score Card</div>
              <span class="badge badge-green" id="overallBadge">Overall: — / 5.0</span>
            </div>
            <table>
              <thead>
                <tr>
                  <th>Dimension</th>
                  <th>Score</th>
                  <th>Rating</th>
                  <th>Grade</th>
                </tr>
              </thead>
              <tbody id="sqdTableBody">
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px">
                  Loading SQD data...
                </td></tr>
              </tbody>
            </table>
          </div>

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

        </div>

        <!-- Right: QR + Rating breakdown -->
        <div class="col-stack" style="min-width:0">

          <!-- QR Card -->
          <div class="qr-card">
            <div>
              <div class="qr-title">Department QR Code</div>
              <div class="qr-sub" style="margin-top:4px">Post at your service counter</div>
            </div>
            <div class="qr-img-wrap">
              <!-- Replace with: <img src="../qrcodes/dept_<?= $deptId ?>.png" style="width:100%;height:100%;border-radius:10px"> -->
              <div class="qr-icon">&#9636;</div>
              <div style="font-size:0.68rem;color:var(--text-muted)">QR Code here</div>
            </div>
            <div class="qr-sub">Scan to submit feedback<br>for this department</div>
            <button class="action-btn primary" style="width:100%" onclick="location.href='qrcode.php'">
              &#128228;&nbsp; Download QR Code
            </button>
            <button class="action-btn" style="width:100%" onclick="location.href='qrcode.php?print=1'">
              &#128438;&nbsp; Print QR Code
            </button>
          </div>

          <!-- Rating breakdown -->
          <div class="chart-card" style="padding:18px">
            <div class="cc-title" style="margin-bottom:14px">Rating Breakdown</div>
            <div id="ratingBreakdown">
              <!-- Populated by dept_dashboard.js -->
              <div style="color:var(--text-muted);font-size:0.78rem">Loading...</div>
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

<!-- Hidden: pass PHP session data to JS -->
<script>
  const DEPT_ID   = <?= (int)$deptId ?>;
  const DEPT_NAME = <?= json_encode($userName) ?>;
</script>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../js/dept_dashboard.js"></script>

</body>
</html>