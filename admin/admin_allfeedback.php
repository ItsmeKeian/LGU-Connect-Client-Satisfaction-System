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
<link rel="stylesheet" href="../assets/css/admin_allfeedback.css"/>
<style>
/* ── Per-page selector ── */
.per-page-wrap {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 12px;
  color: #888;
}
.per-page-wrap select {
  padding: 4px 8px;
  font-size: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: #fff;
  color: #333;
  cursor: pointer;
}
.per-page-wrap select:focus {
  outline: none;
  border-color: #B5121B;
}

/* ── Pagination improvements ── */
.pagination-wrap {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  border-top: 1px solid #f0f0f0;
  flex-wrap: wrap;
  gap: 10px;
}
.pagination-info {
  font-size: 12.5px;
  color: #888;
}
.page-link {
  color: #B5121B;
  border-color: #f0f0f0;
  font-size: 13px;
  padding: 5px 11px;
}
.page-item.active .page-link {
  background-color: #B5121B;
  border-color: #B5121B;
  color: #fff;
}
.page-link:hover {
  color: #8B0000;
  background: #fdf0f0;
  border-color: #e8c4c4;
}
.page-item.disabled .page-link {
  color: #ccc;
}
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
      <li><a href="admin_allfeedback.php" class="active">
        <span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> All Feedback
        <span class="nav-badge" id="sbFeedbackCount">0</span>
      </a></li>
    </ul>
    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="admin_csmr_generator.php"><span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> CSMR Generator</a></li>
      <li><a href="admin_analytics.php"><span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics</a></li>
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
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        All Feedback
        <span class="tb-subtitle">System-Wide Records</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" id="refreshBtn"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
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

    <div class="page-content">
      <div class="live-bar">
        <div class="live-dot"></div>
        <span class="live-text">Live &nbsp;&middot;&nbsp; All submitted feedback across departments</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- Summary stat cards -->
      <div class="summary-cards">
        <div class="summary-card">
          <div class="summary-icon si-red"><i class="bi bi-clipboard-data"></i></div>
          <div>
            <div class="summary-val" id="sumTotal">—</div>
            <div class="summary-label">Total Feedback</div>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon si-gold"><i class="bi bi-star-fill"></i></div>
          <div>
            <div class="summary-val" id="sumAvg">—</div>
            <div class="summary-label">Overall Avg Rating</div>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon si-green"><i class="bi bi-emoji-smile"></i></div>
          <div>
            <div class="summary-val" id="sumSatisfied">—</div>
            <div class="summary-label">Satisfied (4–5 ★)</div>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon si-blue"><i class="bi bi-calendar-check"></i></div>
          <div>
            <div class="summary-val" id="sumToday">—</div>
            <div class="summary-label">Submitted Today</div>
          </div>
        </div>
      </div>

      <!-- Filter bar -->
      <div class="filter-bar">
        <label><i class="bi bi-funnel me-1"></i> Filters:</label>
        <select class="filter-select" id="filterDept">
          <option value="">All Departments</option>
        </select>
        <select class="filter-select" id="filterRating">
          <option value="">All Ratings</option>
          <option value="5">★★★★★ (5)</option>
          <option value="4">★★★★☆ (4)</option>
          <option value="3">★★★☆☆ (3)</option>
          <option value="2">★★☆☆☆ (2)</option>
          <option value="1">★☆☆☆☆ (1)</option>
        </select>
        <select class="filter-select" id="filterType">
          <option value="">All Respondent Types</option>
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
        <button class="btn-filter-apply" onclick="applyFilters()">
          <i class="bi bi-search"></i> Search
        </button>
        <button class="btn-filter-reset" onclick="resetFilters()">
          <i class="bi bi-x-lg"></i> Reset
        </button>
      </div>

      <!-- Feedback table -->
      <div class="table-wrap">
        <div class="table-card-header">
          <div>
            <div class="table-card-title">Feedback Records</div>
            <div class="table-record-count" id="recordCount">Loading...</div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <!-- ✅ Per-page selector -->
            <div class="per-page-wrap">
              <span>Show</span>
              <select onchange="changePerPage(this.value)">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span>per page</span>
            </div>
            <button class="btn-export" onclick="exportCSV()">
              <i class="bi bi-filetype-csv"></i> Export CSV
            </button>
            <button class="btn-export" onclick="window.print()">
              <i class="bi bi-printer"></i> Print
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table mb-0" id="feedbackTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Department</th>
                <th>Rating</th>
                <th>Respondent</th>
                <th>Sex</th>
                <th>Age Group</th>
                <th>Comment</th>
                <th>Submitted</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="feedbackTableBody">
              <tr>
                <td colspan="9" class="text-center py-4" style="color:#6b6864;">
                  <div class="spinner-border spinner-border-sm text-danger me-2"></div>
                  Loading feedback records...
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ✅ Pagination -->
        <div class="pagination-wrap">
          <div class="pagination-info" id="paginationInfo">—</div>
          <nav aria-label="Feedback pagination">
            <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
          </nav>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- View Feedback Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title">
          <i class="bi bi-clipboard-check me-2"></i> Feedback Details
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody"></div>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast-container">
  <div id="toastMsg" class="toast align-items-center border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body fw-500" id="toastText" style="font-size:0.82rem;"></div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script src="../js/admin/admin_allfeedback.js"></script>
</body>
</html>