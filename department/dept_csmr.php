<?php
require "../php/auth_check.php";
requireDeptUser();
require "../php/dbconnect.php";

$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));
$dept_code    = CURRENT_DEPT;

$deptStmt = $conn->prepare("SELECT * FROM departments WHERE code = ? LIMIT 1");
$deptStmt->execute([$dept_code]);
$deptInfo = $deptStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Generate CSMR | <?= htmlspecialchars($deptInfo['name'] ?? 'Department') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css"/>
<link rel="stylesheet" href="../assets/css/dept_csmr.css"/>

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

    <!-- Department badge -->
    <div class="sb-dept-badge">
      <div class="sb-dept-badge-icon"><i class="bi bi-building"></i></div>
      <div>
        <div class="sb-dept-badge-name"><?= htmlspecialchars($deptInfo['name'] ?? 'Department') ?></div>
        <div class="sb-dept-badge-code"><?= htmlspecialchars($dept_code) ?></div>
      </div>
    </div>

    <div class="sb-role">
      <div class="role-dot"></div>
      <div>
        <div class="role-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
        <div class="role-sub">Department User</div>
      </div>
    </div>

    <div class="sb-section">My Department</div>
    <ul class="sb-nav">
      <li><a href="dept_dashboard.php" >
        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span> My Dashboard
      </a></li>
      <li><a href="dept_feedback.php">
        <span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> Feedback Inbox
        <span class="nav-badge" id="sbFeedbackCount">0</span>
      </a></li>
      <li><a href="dept_qrcode.php">
        <span class="nav-icon"><i class="bi bi-qr-code"></i></span> My QR Code
      </a></li>
    </ul>

    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="dept_csmr.php" class="active">
        <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> Generate CSMR
      </a></li>
    
      <li><a href="dept_export.php">
        <span class="nav-icon"><i class="bi bi-download"></i></span> Export Data
      </a></li>
    </ul>

    <div class="sb-footer">
      <a href="../php/logout.php" onclick="return confirm('Are you sure you want to sign out?')">
        <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span> Sign Out
      </a>
    </div>
  </aside>

  <!-- ══ MAIN AREA ══ -->
  <div class="main-area">
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        Generate CSMR
        <span class="tb-subtitle"><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        <button class="tb-btn primary" id="topbarPrintBtn" onclick="openPrint()" style="display:none">
          <i class="bi bi-printer"></i> Print / PDF
        </button>
        <div class="tb-avatar" id="topbarAvatar" onclick="toggleAvatarDropdown(event)">
          <?= $avatarLetter ?>
          <div class="avatar-dropdown" id="avatarDropdown">
            <div class="av-header">
              <div class="av-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
              <div class="av-role">Department User</div>
              <div style="font-size:.68rem;color:#888;margin-top:1px"><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></div>
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
        <span class="live-text">Live &nbsp;&middot;&nbsp; Client Satisfaction Measurement Report — <?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="csmr-content">

        <!-- ── LEFT: Filter Panel ── -->
        <div class="filter-panel">
          <div class="filter-panel-header">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <div>
              <h2>CSMR Generator</h2>
              <small><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></small>
            </div>
          </div>
          <div class="filter-body">

            <div class="dept-notice">
              <i class="bi bi-lock-fill"></i>
              <div>
                <strong><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></strong><br>
                <span style="font-size:11px;opacity:.8">This report is for your department only</span>
              </div>
            </div>

            <div class="filter-group">
              <label><i class="bi bi-calendar3" style="margin-right:4px"></i> Quick Period</label>
              <div class="period-chips">
                <span class="period-chip" data-period="today">Today</span>
                <span class="period-chip" data-period="this_week">This Week</span>
                <span class="period-chip active" data-period="this_month">This Month</span>
                <span class="period-chip" data-period="last_month">Last Month</span>
                <span class="period-chip" data-period="this_quarter">This Quarter</span>
                <span class="period-chip" data-period="this_year">This Year</span>
                <span class="period-chip" data-period="custom">Custom</span>
              </div>
            </div>

            <div id="customDateGroup" style="display:none">
              <div class="filter-group">
                <label>Date From</label>
                <input type="date" id="dateFrom"/>
              </div>
              <div class="filter-group">
                <label>Date To</label>
                <input type="date" id="dateTo"/>
              </div>
            </div>

            <div id="selectedDatesDisplay" class="filter-group">
              <label>Selected Period</label>
              <div id="datesDisplay" style="font-size:12px;color:#555;background:#f5f5f5;padding:8px 12px;border-radius:7px;border:1px solid #efefef"></div>
              <input type="hidden" id="dateFrom" />
              <input type="hidden" id="dateTo" />
            </div>

            <hr class="filter-divider"/>

            <div class="filter-group">
              <label><i class="bi bi-pencil" style="margin-right:4px"></i> Report Title <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#bbb">(optional)</span></label>
              <input type="text" id="reportTitle" placeholder="e.g. Q2 2026 Satisfaction Report"/>
            </div>

            <div class="filter-group">
              <label><i class="bi bi-list-check" style="margin-right:4px"></i> Include in Report</label>
              <div style="display:flex;flex-direction:column;gap:9px;margin-top:4px">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;text-transform:none;letter-spacing:0;color:#444;font-weight:400;cursor:pointer">
                  <input type="checkbox" id="inclCharts" checked style="accent-color:var(--red)"/> Charts &amp; Graphs
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;text-transform:none;letter-spacing:0;color:#444;font-weight:400;cursor:pointer">
                  <input type="checkbox" id="inclSQD" checked style="accent-color:var(--red)"/> SQD Score Breakdown
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;text-transform:none;letter-spacing:0;color:#444;font-weight:400;cursor:pointer">
                  <input type="checkbox" id="inclComments" checked style="accent-color:var(--red)"/> Recent Comments
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;text-transform:none;letter-spacing:0;color:#444;font-weight:400;cursor:pointer">
                  <input type="checkbox" id="inclDemo" style="accent-color:var(--red)"/> Demographics Breakdown
                </label>
              </div>
            </div>

            <hr class="filter-divider"/>

            <button class="btn-generate" id="generateBtn" onclick="generateReport()">
              <i class="bi bi-eye"></i> Preview Report
            </button>
            <button class="btn-print" id="printBtn" onclick="openPrint()">
              <i class="bi bi-printer"></i> Print / Export PDF
            </button>
          </div>
        </div>

        <!-- ── RIGHT: Preview Panel ── -->
        <div class="preview-panel">

          <!-- Stat cards -->
          <div id="statCards" style="display:none">
            <div class="csmr-stats">
              <div class="csmr-stat">
                <div class="csmr-stat-icon red"><i class="bi bi-people-fill"></i></div>
                <div><div class="csmr-stat-val" id="statTotal">0</div><div class="csmr-stat-label">Total Respondents</div></div>
              </div>
              <div class="csmr-stat">
                <div class="csmr-stat-icon green"><i class="bi bi-emoji-smile-fill"></i></div>
                <div><div class="csmr-stat-val" id="statSat">0%</div><div class="csmr-stat-label">Satisfaction Rate</div></div>
              </div>
              <div class="csmr-stat">
                <div class="csmr-stat-icon blue"><i class="bi bi-star-fill"></i></div>
                <div><div class="csmr-stat-val" id="statAvg">0</div><div class="csmr-stat-label">Avg. Rating</div></div>
              </div>
              <div class="csmr-stat">
                <div class="csmr-stat-icon gold"><i class="bi bi-bar-chart-fill"></i></div>
                <div><div class="csmr-stat-val" id="statSqd">0</div><div class="csmr-stat-label">SQD Avg Score</div></div>
              </div>
            </div>
          </div>

          <!-- Preview card -->
          <div class="preview-card">
            <div class="preview-card-header">
              <h3><i class="bi bi-file-earmark-bar-graph" style="color:var(--red);margin-right:7px"></i> Report Preview</h3>
              <div id="periodBadgeWrap" style="display:none">
                <div class="period-badge"><i class="bi bi-calendar-range"></i><span id="periodBadgeText">—</span></div>
              </div>
            </div>

            <div class="preview-empty" id="previewEmpty">
              <i class="bi bi-file-earmark-text"></i>
              <h4>No Report Generated Yet</h4>
              <p>Select a period and click <strong>Preview Report</strong><br>to generate your CSMR.</p>
            </div>

            <div class="preview-spinner" id="previewSpinner">
              <div class="spin-circle"></div>
              <p>Fetching feedback data…</p>
            </div>

            <div id="chartsSection" style="display:none">
              <div class="csmr-charts">
                <div class="chart-section">
                  <h5>Rating Distribution</h5>
                  <div id="ratingBars"></div>
                </div>
              </div>
            </div>

            <!-- SQD section — independent of charts -->
            <div id="sqdSection" style="display:none">
              <div style="padding:0 20px 16px;border-top:1px solid #f0f0f0">
                <h5 style="font-size:12px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin:14px 0 10px">SQD Scores</h5>
                <div id="sqdBars"></div>
              </div>
            </div>

            <div id="demoSection" style="display:none">
              <hr style="margin:0;border-color:#f0f0f0"/>
              <div class="demo-grid">
                <div>
                  <div style="font-size:11px;font-weight:700;color:#888;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">By Respondent Type</div>
                  <div id="demoType"></div>
                </div>
                <div>
                  <div style="font-size:11px;font-weight:700;color:#888;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">By Age Group</div>
                  <div id="demoAge"></div>
                </div>
              </div>
            </div>

            <div id="commentsSection" style="display:none">
              <div class="comments-section">
                <h5>Recent Comments</h5>
                <div id="commentsList"></div>
              </div>
            </div>

          </div><!-- /preview-card -->
        </div><!-- /preview-panel -->

      </div><!-- /csmr-content -->
    </div><!-- /page-content -->
  </div><!-- /main-area -->
</div><!-- /app-shell -->

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script>
const DEPT_CODE = <?= json_encode($dept_code) ?>;
const DEPT_NAME = <?= json_encode($deptInfo['name'] ?? $dept_code) ?>;

let lastReportData = null;
let selectedFrom   = '';
let selectedTo     = '';


</script>
<script src="../js/department/dept_csmr.js"></script>
</body>
</html>