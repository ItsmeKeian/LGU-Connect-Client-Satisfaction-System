<?php
require "../php/auth_check.php";
requireDeptUser();
require "../php/dbconnect.php";

$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));
$dept_code    = CURRENT_DEPT;
$deptStmt     = $conn->prepare("SELECT * FROM departments WHERE code = ? LIMIT 1");
$deptStmt->execute([$dept_code]);
$deptInfo     = $deptStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Feedback Inbox | <?= htmlspecialchars($deptInfo['name'] ?? 'Department') ?></title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_allfeedback.css"/>
<style>
.sb-dept-badge{margin:8px 10px 4px;background:rgba(139,26,26,.35);border:1px solid rgba(139,26,26,.5);border-radius:8px;padding:9px 12px;display:flex;align-items:center;gap:9px}
.sb-dept-badge-icon{width:28px;height:28px;background:#8B1A1A;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;flex-shrink:0}
.sb-dept-badge-name{font-size:11px;font-weight:600;color:#fff;line-height:1.3}
.sb-dept-badge-code{font-size:10px;color:rgba(255,255,255,.45);margin-top:1px}
.per-page-wrap{display:flex;align-items:center;gap:7px;font-size:12px;color:#888}
.per-page-wrap select{padding:4px 8px;font-size:12px;border:1px solid #ddd;border-radius:6px;background:#fff;color:#333}
.pagination-wrap{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid #f0f0f0;flex-wrap:wrap;gap:10px}
.pagination-info{font-size:12.5px;color:#888}
.page-link{color:#8B1A1A;border-color:#f0f0f0;font-size:13px;padding:5px 11px}
.page-item.active .page-link{background:#8B1A1A;border-color:#8B1A1A;color:#fff}
.page-link:hover{color:#6e1414;background:#fdf0f0;border-color:#e8c4c4}
.page-item.disabled .page-link{color:#ccc}
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
      <li><a href="dept_feedback.php" class="active"><span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> Feedback Inbox <span class="nav-badge" id="sbFeedbackCount">0</span></a></li>
      <li><a href="dept_qrcode.php"><span class="nav-icon"><i class="bi bi-qr-code"></i></span> My QR Code</a></li>
    </ul>
    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="dept_csmr.php"><span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> Generate CSMR</a></li>
   
      <li><a href="dept_export.php"><span class="nav-icon"><i class="bi bi-download"></i></span> Export Data</a></li>
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
        Feedback Inbox
        <span class="tb-subtitle"><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?> Only</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" id="refreshBtn"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
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
        <span class="live-text">Live &nbsp;&middot;&nbsp; Feedback for <strong><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></strong> only</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- Summary cards -->
      <div class="summary-cards">
        <div class="summary-card">
          <div class="summary-icon si-red"><i class="bi bi-clipboard-data"></i></div>
          <div><div class="summary-val" id="sumTotal">—</div><div class="summary-label">Total Feedback</div></div>
        </div>
        <div class="summary-card">
          <div class="summary-icon si-gold"><i class="bi bi-star-fill"></i></div>
          <div><div class="summary-val" id="sumAvg">—</div><div class="summary-label">Avg Rating</div></div>
        </div>
        <div class="summary-card">
          <div class="summary-icon si-green"><i class="bi bi-emoji-smile"></i></div>
          <div><div class="summary-val" id="sumSatisfied">—</div><div class="summary-label">Satisfied (4–5★)</div></div>
        </div>
        <div class="summary-card">
          <div class="summary-icon si-blue"><i class="bi bi-calendar-check"></i></div>
          <div><div class="summary-val" id="sumToday">—</div><div class="summary-label">Today</div></div>
        </div>
      </div>

      <!-- Filter bar -->
      <div class="filter-bar">
        <label><i class="bi bi-funnel me-1"></i> Filters:</label>
        <select class="filter-select" id="filterRating">
          <option value="">All Ratings</option>
          <option value="5">★★★★★ (5)</option>
          <option value="4">★★★★☆ (4)</option>
          <option value="3">★★★☆☆ (3)</option>
          <option value="2">★★☆☆☆ (2)</option>
          <option value="1">★☆☆☆☆ (1)</option>
        </select>
        <select class="filter-select" id="filterType">
          <option value="">All Types</option>
          <option value="citizen">Citizen</option>
          <option value="employee">Employee</option>
          <option value="business_owner">Business Owner</option>
          <option value="other">Other</option>
        </select>
        <select class="filter-select" id="filterPeriod">
          <option value="">All Time</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="quarter">This Quarter</option>
        </select>
        <div class="filter-search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" class="filter-search-input" id="filterSearch" placeholder="Search comments...">
        </div>
        <button class="btn-filter-apply" onclick="applyFilters()"><i class="bi bi-search"></i> Search</button>
        <button class="btn-filter-reset" onclick="resetFilters()"><i class="bi bi-x-lg"></i> Reset</button>
      </div>

      <!-- Table -->
      <div class="table-wrap">
        <div class="table-card-header">
          <div>
            <div class="table-card-title">Feedback Records</div>
            <div class="table-record-count" id="recordCount">Loading...</div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="per-page-wrap">
              Show
              <select onchange="changePerPage(this.value)">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              per page
            </div>
            <button class="btn-export" onclick="exportCSV()"><i class="bi bi-filetype-csv"></i> Export CSV</button>
            <button class="btn-export" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>#</th><th>Rating</th><th>Respondent</th>
                <th>Sex</th><th>Age Group</th><th>Comment</th>
                <th>Submitted</th><th>Action</th>
              </tr>
            </thead>
            <tbody id="feedbackTableBody">
              <tr><td colspan="8" class="text-center py-4" style="color:#6b6864">
                <div class="spinner-border spinner-border-sm text-danger me-2"></div>Loading...
              </td></tr>
            </tbody>
          </table>
        </div>

        <div class="pagination-wrap">
          <div class="pagination-info" id="paginationInfo">—</div>
          <nav><ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul></nav>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#8B1A1A,#6e1414);color:#fff;border-radius:12px 12px 0 0">
        <span class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i>Feedback Details</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody"></div>
    </div>
  </div>
</div>

<div class="toast-container">
  <div id="toastMsg" class="toast align-items-center border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastText" style="font-size:.82rem"></div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script>

const DEPT_CODE = <?= json_encode($dept_code) ?>;


</script>
<script src="../js/department/dept_feedback.js"></script>
</body>
</html>