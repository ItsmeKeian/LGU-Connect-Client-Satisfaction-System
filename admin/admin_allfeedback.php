<?php
require "../php/auth_check.php";
if (IS_DEPT_USER) {
    header("Location: ../department/dept_dashboard.php");
    exit();
}
$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>All Feedback | LGU-Connect</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css"/>

<style>
  /* ── Avatar Dropdown ── */
  .tb-avatar { position:relative; cursor:pointer; user-select:none; }
  .avatar-dropdown {
    display:none; position:absolute; top:calc(100% + 10px); right:0;
    width:200px; background:#fff; border:1px solid rgba(0,0,0,0.09);
    border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.12);
    z-index:9999; overflow:hidden; animation:dropIn 0.18s ease;
  }
  @keyframes dropIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
  .avatar-dropdown.show { display:block; }
  .av-header { padding:14px 16px 10px; border-bottom:1px solid rgba(0,0,0,0.07); }
  .av-name { font-size:0.82rem; font-weight:700; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .av-role { font-size:0.68rem; color:#B5121B; font-weight:600; margin-top:2px; }
  .av-menu { padding:6px 0; }
  .av-item {
    display:flex; align-items:center; gap:10px; padding:9px 16px;
    font-size:0.8rem; color:#3a3a3a; text-decoration:none;
    cursor:pointer; transition:background 0.15s; border:none;
    background:none; width:100%; text-align:left;
  }
  .av-item:hover { background:#fdf0f0; color:#B5121B; }
  .av-item.danger { color:#B5121B; }
  .av-item.danger:hover { background:#B5121B; color:#fff; }
  .av-divider { height:1px; background:rgba(0,0,0,0.07); margin:4px 0; }
  .av-item i { font-size:15px; width:16px; }

  /* ── Summary cards ── */
  .summary-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
  .summary-card {
    background:#fff; border-radius:12px; padding:16px 18px;
    border:1px solid rgba(0,0,0,0.07); box-shadow:0 1px 4px rgba(0,0,0,0.06);
    display:flex; align-items:center; gap:14px;
  }
  .summary-icon {
    width:42px; height:42px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; flex-shrink:0;
  }
  .si-red   { background:#fdf0f0; color:#B5121B; }
  .si-gold  { background:#fdf8e6; color:#C8991A; }
  .si-green { background:#e8f5e9; color:#2e7d32; }
  .si-blue  { background:#e3f2fd; color:#1565c0; }
  .summary-val   { font-size:1.5rem; font-weight:700; color:#1a1a1a; line-height:1; }
  .summary-label { font-size:0.7rem; color:#6b6864; margin-top:3px; }

  /* ── Filter bar ── */
  .filter-bar {
    background:#fff; border-radius:12px; padding:14px 18px;
    border:1px solid rgba(0,0,0,0.07); box-shadow:0 1px 4px rgba(0,0,0,0.06);
    display:flex; align-items:center; gap:10px; flex-wrap:wrap;
    margin-bottom:16px;
  }
  .filter-bar label { font-size:0.75rem; font-weight:600; color:#3a3a3a; white-space:nowrap; }
  .filter-select {
    height:36px; padding:0 12px; border-radius:8px;
    border:1px solid rgba(0,0,0,0.12); font-size:0.8rem;
    color:#1a1a1a; background:#fff; outline:none;
    transition:all 0.2s; min-width:140px;
  }
  .filter-select:focus { border-color:#B5121B; box-shadow:0 0 0 3px rgba(181,18,27,0.08); }

  .filter-search-wrap { position:relative; flex:1; min-width:180px; }
  .filter-search-wrap .bi { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#9a9390; font-size:13px; }
  .filter-search-input {
    width:100%; height:36px; padding:0 14px 0 32px;
    border-radius:8px; border:1px solid rgba(0,0,0,0.12);
    font-size:0.8rem; color:#1a1a1a; outline:none; transition:all 0.2s;
  }
  .filter-search-input:focus { border-color:#B5121B; box-shadow:0 0 0 3px rgba(181,18,27,0.08); }

  .btn-filter-apply {
    height:36px; padding:0 18px;
    background:linear-gradient(135deg,#B5121B,#8B0000);
    border:none; border-radius:8px; color:#fff;
    font-size:0.8rem; font-weight:600; cursor:pointer;
    display:flex; align-items:center; gap:6px;
    box-shadow:0 3px 10px rgba(181,18,27,0.25);
    transition:all 0.2s; white-space:nowrap;
  }
  .btn-filter-apply:hover { box-shadow:0 5px 14px rgba(181,18,27,0.38); transform:translateY(-1px); }

  .btn-filter-reset {
    height:36px; padding:0 14px;
    background:transparent; border:1px solid rgba(0,0,0,0.12);
    border-radius:8px; color:#6b6864; font-size:0.8rem;
    cursor:pointer; transition:all 0.2s;
  }
  .btn-filter-reset:hover { background:#f4f1ed; color:#1a1a1a; }

  /* ── Table card ── */
  .table-wrap {
    background:#fff; border-radius:12px; overflow:hidden;
    border:1px solid rgba(0,0,0,0.07); box-shadow:0 1px 4px rgba(0,0,0,0.06);
  }
  .table-card-header {
    padding:14px 20px; border-bottom:1px solid rgba(0,0,0,0.07);
    display:flex; align-items:center; justify-content:space-between; gap:12px;
  }
  .table-card-title { font-size:0.88rem; font-weight:700; color:#1a1a1a; }
  .table-record-count { font-size:0.72rem; color:#6b6864; }

  .table thead th {
    font-size:0.65rem; font-weight:700; letter-spacing:0.8px;
    text-transform:uppercase; color:#6b6864; background:#faf8f5;
    border-bottom:1px solid rgba(0,0,0,0.07);
    white-space:nowrap; padding:10px 16px;
  }
  .table tbody td {
    font-size:0.8rem; color:#1a1a1a; padding:11px 16px;
    vertical-align:middle; border-bottom:1px solid #f5f2ee;
  }
  .table tbody tr:last-child td { border-bottom:none; }
  .table tbody tr:hover td { background:#fdf9f4; cursor:pointer; }

  /* Stars */
  .star-display { color:#F0C030; font-size:12px; letter-spacing:1px; }
  .star-display.small { font-size:11px; }

  /* Rating badge */
  .rating-pill {
    display:inline-flex; align-items:center; gap:4px;
    font-size:0.72rem; font-weight:700; padding:3px 10px;
    border-radius:20px;
  }
  .rp-5 { background:#e8f5e9; color:#2e7d32; }
  .rp-4 { background:#f0f8ff; color:#1565c0; }
  .rp-3 { background:#fdf8e6; color:#C8991A; }
  .rp-2 { background:#fff3e0; color:#e65100; }
  .rp-1 { background:#fdf0f0; color:#B5121B; }

  /* Dept pill */
  .dept-pill {
    display:inline-block; font-size:0.65rem; font-weight:700;
    padding:3px 9px; border-radius:20px;
    background:#fdf0f0; color:#B5121B;
  }

  /* Type badge */
  .type-badge {
    font-size:0.65rem; font-weight:600; padding:3px 9px;
    border-radius:6px; background:#f4f1ed; color:#6b6864;
    text-transform:capitalize;
  }

  /* Pagination */
  .pagination-wrap {
    padding:14px 20px; border-top:1px solid rgba(0,0,0,0.07);
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:8px;
  }
  .pagination-info { font-size:0.75rem; color:#6b6864; }
  .pagination .page-link {
    font-size:0.78rem; color:#B5121B; border-color:rgba(0,0,0,0.1);
    padding:5px 12px;
  }
  .pagination .page-item.active .page-link {
    background:#B5121B; border-color:#B5121B; color:#fff;
  }
  .pagination .page-link:hover { background:#fdf0f0; }

  /* View modal */
  .modal-header {
    background:linear-gradient(135deg,#B5121B,#8B0000);
    color:#fff; border-radius:12px 12px 0 0; padding:18px 24px;
  }
  .modal-title { font-size:0.95rem; font-weight:700; }
  .modal-header .btn-close { filter:invert(1); opacity:0.8; }
  .modal-content { border-radius:12px; border:none; box-shadow:0 16px 48px rgba(0,0,0,0.18); }
  .modal-body { padding:24px; }

  .detail-row {
    display:flex; gap:8px; padding:9px 0;
    border-bottom:1px solid rgba(0,0,0,0.05); font-size:0.82rem;
  }
  .detail-row:last-child { border-bottom:none; }
  .detail-label { font-weight:700; color:#3a3a3a; min-width:130px; flex-shrink:0; }
  .detail-val { color:#1a1a1a; }

  .sqd-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:8px; }
  .sqd-item {
    background:#faf8f5; border-radius:8px; padding:10px 12px;
    font-size:0.75rem;
  }
  .sqd-item .sqd-label { color:#6b6864; margin-bottom:4px; }
  .sqd-item .sqd-val   { font-weight:700; color:#1a1a1a; font-size:0.9rem; }

  /* Export button */
  .btn-export {
    height:36px; padding:0 16px;
    background:transparent; border:1px solid rgba(0,0,0,0.12);
    border-radius:8px; font-size:0.78rem; color:#6b6864;
    cursor:pointer; display:flex; align-items:center; gap:6px;
    transition:all 0.2s;
  }
  .btn-export:hover { background:#f4f1ed; color:#1a1a1a; }

  /* Toast */
  .toast-container { position:fixed; top:20px; right:20px; z-index:99999; }

  /* Responsive */
  @media (max-width:900px) {
    .summary-cards { grid-template-columns:repeat(2,1fr); }
    .filter-bar { flex-direction:column; align-items:stretch; }
  }
  @media (max-width:600px) {
    .summary-cards { grid-template-columns:1fr; }
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
      <li><a href="admin_dashboard.php">
        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span> Dashboard
      </a></li>
      <li><a href="admin_departments.php">
        <span class="nav-icon"><i class="bi bi-building"></i></span> Departments
      </a></li>
      <li><a href="admin_allfeedback.php" class="active">
        <span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> All Feedback
        <span class="nav-badge" id="sbFeedbackCount">0</span>
      </a></li>
    </ul>
    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="csmr_generator.php">
        <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> CSMR Generator
      </a></li>
      <li><a href="analytics.php">
        <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics
      </a></li>
      <li><a href="export.php">
        <span class="nav-icon"><i class="bi bi-download"></i></span> Export Data
      </a></li>
    </ul>
    <div class="sb-section">System</div>
    <ul class="sb-nav">
      <li><a href="users.php">
        <span class="nav-icon"><i class="bi bi-people"></i></span> Manage Users
      </a></li>
      <li><a href="qrcodes.php">
        <span class="nav-icon"><i class="bi bi-qr-code"></i></span> QR Codes
      </a></li>
      <li><a href="settings.php">
        <span class="nav-icon"><i class="bi bi-gear"></i></span> Settings
      </a></li>
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
        All Feedback
        <span class="tb-subtitle">System-Wide Records</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" id="refreshBtn">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="tb-btn primary" onclick="location.href='csmr_generator.php'">
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
              <a href="settings.php" class="av-item"><i class="bi bi-person-circle"></i> My Profile</a>
              <a href="settings.php" class="av-item"><i class="bi bi-gear"></i> Settings</a>
              <div class="av-divider"></div>
              <a href="../php/logout.php" class="av-item danger"
                 onclick="return confirm('Sign out?')">
                <i class="bi bi-box-arrow-right"></i> Sign Out
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ PAGE CONTENT ══════════ -->
    <div class="page-content">

      <!-- Live bar -->
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
          <!-- Populated by JS -->
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
          <input type="text" class="filter-search-input" id="filterSearch"
                 placeholder="Search comments...">
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
          <div class="d-flex gap-2">
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

        <!-- Pagination -->
        <div class="pagination-wrap">
          <div class="pagination-info" id="paginationInfo">—</div>
          <nav>
            <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
          </nav>
        </div>
      </div>

    </div>
    <!-- /page-content -->
  </div>
</div>

<!-- ══════════ VIEW FEEDBACK MODAL ══════════ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title">
          <i class="bi bi-clipboard-check me-2"></i> Feedback Details
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody">
        <!-- Populated by JS -->
      </div>
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

<!-- Scripts -->
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>

<script>
const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

// State
let currentPage = 1;
let currentFilters = {};
let allFeedback = [];

// SQD Labels
const SQD_LABELS = {
  sqd0: 'Aware of Citizens Charter',
  sqd1: 'Requirements are reasonable',
  sqd2: 'Steps are simple',
  sqd3: 'Time is reasonable',
  sqd4: 'Cost is reasonable',
  sqd5: 'Office is comfortable/clean',
  sqd6: 'Staff are helpful/courteous',
  sqd7: 'Service is fast',
  sqd8: 'Staff followed rules'
};

const RATING_LABELS = {
  5: 'Strongly Agree',
  4: 'Agree',
  3: 'Neutral',
  2: 'Disagree',
  1: 'Strongly Disagree'
};

// ── Init ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH', {weekday:'long',year:'numeric',month:'long',day:'numeric'});

document.getElementById('menuToggle').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('sb-open');
});

function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => document.getElementById('avatarDropdown').classList.remove('show'));
document.getElementById('avatarDropdown').addEventListener('click', e => e.stopPropagation());

document.getElementById('refreshBtn').addEventListener('click', () => loadFeedback(currentFilters));

// Enter key on search
document.getElementById('filterSearch').addEventListener('keydown', e => {
  if (e.key === 'Enter') applyFilters();
});

// ── Load departments into filter dropdown ──
function loadDeptFilter() {
  $.get('../php/get/get_departments.php', function(res) {
    if (!res.success) return;
    let opts = '<option value="">All Departments</option>';
    res.data.forEach(d => {
      opts += `<option value="${d.code}">${d.code} — ${d.name}</option>`;
    });
    $('#filterDept').html(opts);

    // If URL has dept param, pre-select it
    const urlDept = new URLSearchParams(window.location.search).get('dept');
    if (urlDept) { $('#filterDept').val(urlDept); }
  });
}

// ── Load feedback ──
function loadFeedback(filters = {}, page = 1) {
  currentPage = page;
  currentFilters = filters;

  const params = { page, per_page: 15, ...filters };

  $('#feedbackTableBody').html(`
    <tr><td colspan="9" class="text-center py-4" style="color:#6b6864;">
      <div class="spinner-border spinner-border-sm text-danger me-2"></div>
      Loading...
    </td></tr>`);

  $.get('../php/get/get_feedback.php', params, function(res) {
    if (!res.success) {
      showToast('Failed to load feedback.', 'danger');
      return;
    }

    // Update summary cards
    $('#sumTotal').text(res.summary.total ?? 0);
    $('#sumAvg').text(res.summary.avg_rating ? parseFloat(res.summary.avg_rating).toFixed(2) : '—');
    $('#sumSatisfied').text(res.summary.satisfied ?? 0);
    $('#sumToday').text(res.summary.today ?? 0);
    $('#sbFeedbackCount').text(res.summary.total ?? 0);

    allFeedback = res.data;

    if (!res.data.length) {
      $('#feedbackTableBody').html(`
        <tr><td colspan="9" class="text-center py-4" style="color:#6b6864;">
          <i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.4;"></i>
          No feedback records found.
        </td></tr>`);
      $('#recordCount').text('0 records');
      $('#paginationInfo').text('No records');
      $('#paginationLinks').html('');
      return;
    }

    // Render rows
    let rows = '';
    res.data.forEach((f, i) => {
      const ratingClass = `rp-${f.rating}`;
      const stars = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
      const typeLabel = (f.respondent_type || 'citizen').replace('_', ' ');
      const ageLabel = formatAge(f.age_group);
      const comment = f.comment ? f.comment.substring(0, 60) + (f.comment.length > 60 ? '...' : '') : '<span style="color:#9a9390;font-style:italic;">No comment</span>';
      const date = new Date(f.submitted_at).toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'});
      const rowNum = (page - 1) * 15 + i + 1;

      rows += `
      <tr onclick="viewFeedback(${JSON.stringify(f).replace(/"/g,'&quot;')})" style="cursor:pointer;">
        <td style="color:#9a9390;font-size:0.72rem;">${rowNum}</td>
        <td><span class="dept-pill">${f.department_code}</span></td>
        <td>
          <span class="rating-pill ${ratingClass}">${f.rating} <span class="star-display small">★</span></span>
        </td>
        <td><span class="type-badge">${typeLabel}</span></td>
        <td style="text-transform:capitalize;">${f.sex ? f.sex.replace('_',' ') : '—'}</td>
        <td>${ageLabel}</td>
        <td style="max-width:200px;">${comment}</td>
        <td style="white-space:nowrap;font-size:0.75rem;color:#6b6864;">${date}</td>
        <td>
          <button class="btn btn-sm" style="background:#fdf0f0;color:#B5121B;border:none;font-size:0.72rem;border-radius:6px;padding:4px 10px;"
            onclick="event.stopPropagation();viewFeedback(${JSON.stringify(f).replace(/"/g,'&quot;')})">
            <i class="bi bi-eye"></i> View
          </button>
        </td>
      </tr>`;
    });

    $('#feedbackTableBody').html(rows);
    $('#recordCount').text(`${res.total} total records`);

    renderPagination(res.total, res.per_page, page);

  }).fail(() => showToast('Server error loading feedback.', 'danger'));
}

// ── Pagination ──
function renderPagination(total, perPage, current) {
  const totalPages = Math.ceil(total / perPage);
  const from = (current - 1) * perPage + 1;
  const to   = Math.min(current * perPage, total);
  $('#paginationInfo').text(`Showing ${from}–${to} of ${total} records`);

  if (totalPages <= 1) { $('#paginationLinks').html(''); return; }

  let links = '';
  links += `<li class="page-item ${current===1?'disabled':''}">
    <a class="page-link" href="#" onclick="loadFeedback(currentFilters,${current-1});return false;">‹</a></li>`;

  for (let p = Math.max(1, current-2); p <= Math.min(totalPages, current+2); p++) {
    links += `<li class="page-item ${p===current?'active':''}">
      <a class="page-link" href="#" onclick="loadFeedback(currentFilters,${p});return false;">${p}</a></li>`;
  }

  links += `<li class="page-item ${current===totalPages?'disabled':''}">
    <a class="page-link" href="#" onclick="loadFeedback(currentFilters,${current+1});return false;">›</a></li>`;

  $('#paginationLinks').html(links);
}

// ── Apply filters ──
function applyFilters() {
  const filters = {
    dept:   $('#filterDept').val(),
    rating: $('#filterRating').val(),
    type:   $('#filterType').val(),
    period: $('#filterPeriod').val(),
    search: $('#filterSearch').val().trim(),
  };
  loadFeedback(filters, 1);
}

function resetFilters() {
  $('#filterDept').val('');
  $('#filterRating').val('');
  $('#filterType').val('');
  $('#filterPeriod').val('');
  $('#filterSearch').val('');
  loadFeedback({}, 1);
}

// ── View feedback modal ──
function viewFeedback(f) {
  const stars = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
  const date  = new Date(f.submitted_at).toLocaleString('en-PH');

  let sqdHtml = '';
  Object.keys(SQD_LABELS).forEach(key => {
    if (f[key] !== null && f[key] !== undefined) {
      sqdHtml += `
      <div class="sqd-item">
        <div class="sqd-label">${SQD_LABELS[key]}</div>
        <div class="sqd-val">${f[key]} — <span style="font-size:0.72rem;font-weight:400;color:#6b6864;">${RATING_LABELS[f[key]] ?? '—'}</span></div>
      </div>`;
    }
  });

  const html = `
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-building me-1"></i> Department</span>
      <span class="detail-val"><strong>${f.department_code}</strong></span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-star me-1"></i> Overall Rating</span>
      <span class="detail-val">
        <span class="rating-pill rp-${f.rating} me-2">${f.rating} ★</span>
        <span class="star-display">${stars}</span>
      </span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-person me-1"></i> Respondent</span>
      <span class="detail-val" style="text-transform:capitalize;">${(f.respondent_type||'citizen').replace('_',' ')}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-gender-ambiguous me-1"></i> Sex</span>
      <span class="detail-val" style="text-transform:capitalize;">${f.sex ? f.sex.replace('_',' ') : '—'}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-calendar3 me-1"></i> Age Group</span>
      <span class="detail-val">${formatAge(f.age_group)}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-chat-left-text me-1"></i> Comment</span>
      <span class="detail-val">${f.comment || '<span style="color:#9a9390;font-style:italic;">No comment provided</span>'}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-lightbulb me-1"></i> Suggestions</span>
      <span class="detail-val">${f.suggestions || '<span style="color:#9a9390;font-style:italic;">None</span>'}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-clock me-1"></i> Submitted</span>
      <span class="detail-val">${date}</span>
    </div>

    ${sqdHtml ? `
    <div style="margin-top:16px;">
      <div style="font-size:0.8rem;font-weight:700;color:#3a3a3a;margin-bottom:8px;">
        <i class="bi bi-list-check me-1"></i> Service Quality Dimensions (SQD)
      </div>
      <div class="sqd-grid">${sqdHtml}</div>
    </div>` : ''}
  `;

  document.getElementById('viewModalBody').innerHTML = html;
  viewModal.show();
}

// ── CSV Export ──
function exportCSV() {
  const filters = { ...currentFilters, export: 'csv' };
  const params  = new URLSearchParams(filters).toString();
  window.location.href = `../php/get/get_feedback.php?${params}`;
}

// ── Helpers ──
function formatAge(age) {
  const map = {
    below_18: 'Below 18',
    '18_30':  '18–30',
    '31_45':  '31–45',
    '46_60':  '46–60',
    above_60: 'Above 60'
  };
  return map[age] ?? '—';
}

function showToast(msg, type = 'success') {
  const el = document.getElementById('toastMsg');
  const tx = document.getElementById('toastText');
  el.className = `toast align-items-center border-0 text-white bg-${type === 'success' ? 'success' : 'danger'}`;
  tx.textContent = msg;
  new bootstrap.Toast(el, {delay:3000}).show();
}

// ── Initial load ──
loadDeptFilter();
loadFeedback({}, 1);
</script>
</body>
</html>