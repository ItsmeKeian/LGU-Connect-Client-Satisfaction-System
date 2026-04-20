<?php
require "../php/auth_check.php";
requireDeptUser();
require "../php/dbconnect.php";

$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));
$dept_code    = CURRENT_DEPT;

$deptStmt = $conn->prepare("SELECT * FROM departments WHERE code = ? LIMIT 1");
$deptStmt->execute([$dept_code]);
$deptInfo = $deptStmt->fetch(PDO::FETCH_ASSOC);

$statsStmt = $conn->prepare("
    SELECT COUNT(*) AS total,
           ROUND(AVG(rating),2) AS avg_rating,
           ROUND(SUM(CASE WHEN rating>=4 THEN 1 ELSE 0 END)*100.0/NULLIF(COUNT(*),0),1) AS satisfaction_rate,
           SUM(CASE WHEN MONTH(submitted_at)=MONTH(NOW()) AND YEAR(submitted_at)=YEAR(NOW()) THEN 1 ELSE 0 END) AS this_month
    FROM feedback WHERE department_code = ?
");
$statsStmt->execute([$dept_code]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// ── Export logs from DB ──
$logsStmt = $conn->prepare("
    SELECT export_type, export_format, date_from, date_to, record_count,
           DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS exported_at
    FROM export_logs
    WHERE dept_code = ? AND exported_by = ?
    ORDER BY created_at DESC LIMIT 20
");
$logsStmt->execute([$dept_code, CURRENT_USER]);
$exportLogs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Export Data | <?= htmlspecialchars($deptInfo['name'] ?? 'Department') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css"/>
<link rel="stylesheet" href="../assets/css/dept_export.css"/>
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
      <li><a href="dept_dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span> My Dashboard</a></li>
      <li><a href="dept_feedback.php"><span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> Feedback Inbox <span class="nav-badge" id="sbFeedbackCount">0</span></a></li>
      <li><a href="dept_qrcode.php"><span class="nav-icon"><i class="bi bi-qr-code"></i></span> My QR Code</a></li>
    </ul>
    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="dept_csmr.php"><span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> Generate CSMR</a></li>
      <li><a href="dept_export.php" class="active"><span class="nav-icon"><i class="bi bi-download"></i></span> Export Data</a></li>
    </ul>
    <div class="sb-footer">
      <a href="../php/logout.php" onclick="return confirm('Are you sure you want to sign out?')">
        <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span> Sign Out
      </a>
    </div>
  </aside>

  <div class="main-area">
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        Export Data
        <span class="tb-subtitle"><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?> Only</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        <button class="tb-btn primary" onclick="location.href='dept_csmr.php'">
          <i class="bi bi-file-earmark-text"></i> Generate CSMR
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
        <span class="live-text">Live &nbsp;&middot;&nbsp; Export feedback data for <strong><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></strong> only</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="export-content">

        <!-- Stats -->
        <div class="stats-bar">
          <div class="stats-box">
            <i class="bi bi-clipboard-data"></i>
            <div><div class="sv"><?= number_format($stats['total']??0) ?></div><div class="sl">Total Feedback Records</div></div>
          </div>
          <div class="stats-box">
            <i class="bi bi-star-fill"></i>
            <div><div class="sv"><?= number_format((float)($stats['avg_rating']??0),2) ?></div><div class="sl">Avg Rating (All Time)</div></div>
          </div>
          <div class="stats-box">
            <i class="bi bi-calendar-check"></i>
            <div><div class="sv"><?= number_format($stats['this_month']??0) ?></div><div class="sl">This Month's Records</div></div>
          </div>
          <div class="stats-box">
            <i class="bi bi-emoji-smile"></i>
            <div><div class="sv"><?= ($stats['satisfaction_rate']??0)>0 ? $stats['satisfaction_rate'].'%' : '—' ?></div><div class="sl">Satisfaction Rate</div></div>
          </div>
        </div>

        <!-- Filters -->
        <div class="section-label"><i class="bi bi-funnel"></i> Export Filters</div>
        <div class="filters-panel">
          <div class="filter-field">
            <label>Date From</label>
            <input type="date" id="filterDateFrom"/>
          </div>
          <div class="filter-field">
            <label>Date To</label>
            <input type="date" id="filterDateTo"/>
          </div>
          <div class="filter-field">
            <label>Quick Range</label>
            <select id="quickRange" onchange="applyQuickRange()">
              <option value="this_month" selected>This Month</option>
              <option value="last_month">Last Month</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
              <option value="all_time">All Time</option>
              <option value="custom">Custom</option>
            </select>
          </div>
          <div class="filter-field">
            <label>Rating Filter</label>
            <select id="filterRating">
              <option value="">All Ratings</option>
              <option value="5">★★★★★ (5)</option>
              <option value="4">★★★★☆ (4)</option>
              <option value="3">★★★☆☆ (3)</option>
              <option value="2">★★☆☆☆ (2)</option>
              <option value="1">★☆☆☆☆ (1)</option>
            </select>
          </div>
          <div class="locked-badge">
            <i class="bi bi-lock-fill"></i>
            <?= htmlspecialchars($dept_code) ?> dept only
          </div>
        </div>

        <!-- Export Cards -->
        <div class="section-label"><i class="bi bi-download"></i> Choose Export Type</div>
        <div class="export-grid">

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon red"><i class="bi bi-clipboard-data-fill"></i></div>
              <div class="export-card-info">
                <h3>Raw Feedback Records</h3>
                <p>All individual feedback with ratings, SQD scores, demographics, and comments.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">Rating</span><span class="col-tag hl">SQD0–SQD8</span>
              <span class="col-tag">Respondent</span><span class="col-tag">Sex</span>
              <span class="col-tag">Age Group</span><span class="col-tag">Comment</span><span class="col-tag">Date</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('feedback','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('feedback','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon blue"><i class="bi bi-bar-chart-steps"></i></div>
              <div class="export-card-info">
                <h3>SQD Scores Report</h3>
                <p>Average scores for all 9 SQD dimensions. For ARTA compliance reporting.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">SQD0–SQD8 Avg</span><span class="col-tag hl">Overall SQD Avg</span>
              <span class="col-tag">Total Responses</span><span class="col-tag">Period</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('sqd','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('sqd','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon green"><i class="bi bi-clipboard2-data-fill"></i></div>
              <div class="export-card-info">
                <h3>Summary Report</h3>
                <p>Aggregated stats — totals, avg rating, satisfaction rate, and rating breakdown.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">Total Responses</span><span class="col-tag hl">Avg Rating</span>
              <span class="col-tag hl">Satisfaction %</span><span class="col-tag">By Rating Level</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('summary','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('summary','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon gold"><i class="bi bi-chat-left-quote-fill"></i></div>
              <div class="export-card-info">
                <h3>Comments &amp; Suggestions</h3>
                <p>All citizen comments and suggestions — useful for qualitative review.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">Comment</span><span class="col-tag hl">Suggestions</span>
              <span class="col-tag">Rating</span><span class="col-tag">Respondent</span><span class="col-tag">Date</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('comments','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('comments','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

        </div>

        <!-- Export History -->
        <div class="section-label"><i class="bi bi-clock-history"></i> Export History
          <span style="font-weight:400;color:#bbb;font-size:10px;text-transform:none;letter-spacing:0">(this session)</span>
        </div>
        <div class="export-log">
          <div class="export-log-header"><i class="bi bi-clock-history"></i> Recent Exports</div>
          <div class="log-list" id="exportLog">
            <?php if (empty($exportLogs)): ?>
            <div class="log-empty">
              <i class="bi bi-download"></i>
              No exports yet for your department.
            </div>
            <?php else: ?>
            <?php
            $typeLabels = ['feedback'=>'Raw Feedback Records','sqd'=>'SQD Scores Report','summary'=>'Summary Report','comments'=>'Comments & Suggestions','departments'=>'Departments Report'];
            $typeIcons  = ['feedback'=>'bi-clipboard-data-fill','sqd'=>'bi-bar-chart-steps','summary'=>'bi-clipboard2-data-fill','comments'=>'bi-chat-left-quote-fill','departments'=>'bi-building'];
            foreach ($exportLogs as $log):
              $lbl  = $typeLabels[$log['export_type']] ?? $log['export_type'];
              $icon = $typeIcons[$log['export_type']]  ?? 'bi-download';
              $fmt  = $log['export_format'];
              $ext  = $fmt === 'excel' ? '.xlsx' : '.csv';
              $fmtDate = fn($d) => date('M j, Y', strtotime($d));
            ?>
            <div class="log-item">
              <div class="log-icon <?= $fmt ?>"><i class="bi <?= $icon ?>"></i></div>
              <div class="log-name">
                <?= htmlspecialchars($lbl) ?>
                <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:6px">
                  <?= $fmtDate($log['date_from']) ?> – <?= $fmtDate($log['date_to']) ?>
                  · <?= number_format($log['record_count']) ?> records
                </span>
              </div>
              <span class="log-badge <?= $fmt ?>"><?= $ext ?></span>
              <div class="log-meta"><?= htmlspecialchars($log['exported_at']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script>
const DEPT_CODE = <?= json_encode($dept_code) ?>;


</script>
<script src="../js/department/dept_export.js"></script>
</body>
</html>