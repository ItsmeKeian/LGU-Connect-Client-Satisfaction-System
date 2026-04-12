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
<title>Manage Users | LGU-Connect</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<style>
/* ── Page Variables ── */
:root {
  --red:       #B5121B;
  --red-dark:  #8e0e15;
  --red-soft:  #fdf0f0;
  --cream:     #f5f0eb;
  --card-bg:   #ffffff;
  --border:    #e8e2dc;
  --text-main: #1a1a1a;
  --text-muted:#6b6864;
  --text-light:#9a9390;
  --radius:    12px;
  --shadow:    0 2px 12px rgba(0,0,0,0.07);
}

body { background: var(--cream); font-family: 'Inter', sans-serif; }

/* ── Page Content ── */
.page-content { padding: 28px 32px; }


@keyframes pulse { 0%,100%{box-shadow:0 0 0 3px rgba(34,197,94,0.2)} 50%{box-shadow:0 0 0 6px rgba(34,197,94,0.08)} }
.live-date { margin-left: auto; font-size: 0.78rem; color: var(--text-light); }

/* ── Summary Cards ── */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px; margin-bottom: 24px;
}
.sum-card {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 20px 22px;
  display: flex; align-items: center; gap: 16px;
  box-shadow: var(--shadow);
}
.sum-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; flex-shrink: 0;
}
.sum-icon.red   { background: var(--red-soft); color: var(--red); }
.sum-icon.green { background: #f0fdf4; color: #16a34a; }
.sum-icon.blue  { background: #eff6ff; color: #2563eb; }
.sum-icon.gold  { background: #fffbeb; color: #d97706; }
.sum-val  { font-size: 1.6rem; font-weight: 700; color: var(--text-main); line-height: 1; }
.sum-label{ font-size: 0.75rem; color: var(--text-muted); margin-top: 3px; }

/* ── Table Card ── */
.table-card {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
}
.table-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 22px; border-bottom: 1px solid var(--border); flex-wrap: gap;
  gap: 12px;
}
.table-title { font-size: 1rem; font-weight: 700; color: var(--text-main); }
.table-sub   { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }
.table-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

/* Search */
.search-wrap {
  position: relative;
}
.search-wrap input {
  padding: 8px 14px 8px 36px;
  border: 1px solid var(--border); border-radius: 8px;
  font-size: 0.82rem; background: var(--cream); color: var(--text-main);
  width: 220px; outline: none; transition: border .2s;
}
.search-wrap input:focus { border-color: var(--red); }
.search-wrap i {
  position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
  color: var(--text-light); font-size: 0.85rem;
}

/* Filter select */
.filter-select {
  padding: 8px 12px; border: 1px solid var(--border);
  border-radius: 8px; font-size: 0.82rem;
  background: var(--cream); color: var(--text-main); outline: none;
  cursor: pointer; transition: border .2s;
}
.filter-select:focus { border-color: var(--red); }

/* Add button */
.btn-add-user {
  background: var(--red); color: #fff; border: none;
  padding: 9px 18px; border-radius: 8px; font-size: 0.83rem;
  font-weight: 600; cursor: pointer; display: flex; align-items: center;
  gap: 6px; transition: background .2s;
}
.btn-add-user:hover { background: var(--red-dark); }

/* Table */
.users-table { width: 100%; border-collapse: collapse; }
.users-table thead th {
  padding: 11px 16px; font-size: 0.7rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.06em;
  color: var(--text-muted); background: #faf8f6;
  border-bottom: 1px solid var(--border); text-align: left;
}
.users-table tbody tr {
  border-bottom: 1px solid #f0ece8; transition: background .15s;
}
.users-table tbody tr:last-child { border-bottom: none; }
.users-table tbody tr:hover { background: #fdf9f7; }
.users-table td { padding: 13px 16px; font-size: 0.83rem; color: var(--text-main); vertical-align: middle; }

/* Avatar cell */
.user-cell { display: flex; align-items: center; gap: 11px; }
.user-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: var(--red); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem; font-weight: 700; flex-shrink: 0;
}
.user-name  { font-weight: 600; color: var(--text-main); font-size: 0.85rem; }
.user-email { font-size: 0.72rem; color: var(--text-muted); margin-top: 1px; }

/* Role badge */
.role-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
}
.role-badge.superadmin { background: #fdf0f0; color: var(--red); }
.role-badge.dept_user  { background: #eff6ff; color: #2563eb; }

/* Dept pill */
.dept-pill {
  display: inline-block; padding: 3px 9px; border-radius: 6px;
  font-size: 0.72rem; font-weight: 700;
  background: #f0fdf4; color: #16a34a;
}
.dept-pill.none { background: #f5f5f5; color: var(--text-light); font-weight: 400; font-style: italic; }

/* Status badge */
.status-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
}
.status-badge.active   { background: #f0fdf4; color: #16a34a; }
.status-badge.inactive { background: #fef2f2; color: #dc2626; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; }
.status-dot.active   { background: #16a34a; }
.status-dot.inactive { background: #dc2626; }

/* Action buttons */
.action-wrap { display: flex; gap: 6px; }
.btn-edit, .btn-delete {
  border: none; padding: 5px 12px; border-radius: 6px;
  font-size: 0.72rem; font-weight: 600; cursor: pointer; transition: all .15s;
  display: flex; align-items: center; gap: 4px;
}
.btn-edit   { background: #eff6ff; color: #2563eb; }
.btn-edit:hover   { background: #dbeafe; }
.btn-delete { background: var(--red-soft); color: var(--red); }
.btn-delete:hover { background: #fce4e4; }

/* Empty state */
.empty-state { text-align: center; padding: 52px 24px; color: var(--text-muted); }
.empty-state i { font-size: 2.2rem; opacity: 0.3; display: block; margin-bottom: 10px; }

/* ── Modal ── */
.modal-content { border-radius: 14px; border: none; }
.modal-header  {
  background: var(--red); color: #fff;
  border-radius: 14px 14px 0 0; padding: 18px 22px;
}
.modal-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.btn-close-white { filter: invert(1) brightness(2); }
.modal-body  { padding: 24px; }
.modal-footer{ padding: 16px 24px; border-top: 1px solid var(--border); }

/* Form */
.form-label-sm { font-size: 0.78rem; font-weight: 600; color: var(--text-main); margin-bottom: 5px; }
.form-control-lg-custom {
  width: 100%; padding: 10px 14px; border: 1px solid var(--border);
  border-radius: 8px; font-size: 0.85rem; color: var(--text-main);
  outline: none; transition: border .2s; background: #fff;
}
.form-control-lg-custom:focus { border-color: var(--red); }
.form-group { margin-bottom: 16px; }

.pw-wrap { position: relative; }
.pw-wrap input { padding-right: 40px; }
.pw-toggle {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: var(--text-light);
  font-size: 0.9rem; padding: 0;
}

/* Dept field toggle */
#deptGroup { transition: all .2s; }

/* Confirm modal */
.confirm-icon {
  width: 56px; height: 56px; border-radius: 50%;
  background: var(--red-soft); color: var(--red);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; margin: 0 auto 14px;
}

/* Buttons */
.btn-save {
  background: var(--red); color: #fff; border: none;
  padding: 10px 24px; border-radius: 8px; font-size: 0.85rem;
  font-weight: 600; cursor: pointer; transition: background .2s;
}
.btn-save:hover { background: var(--red-dark); }
.btn-cancel-modal {
  background: #f5f0eb; color: var(--text-muted); border: none;
  padding: 10px 20px; border-radius: 8px; font-size: 0.85rem;
  font-weight: 600; cursor: pointer;
}
.btn-delete-confirm {
  background: var(--red); color: #fff; border: none;
  padding: 10px 24px; border-radius: 8px; font-size: 0.85rem;
  font-weight: 600; cursor: pointer;
}

/* Toast */
.toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; }

/* Pagination */
.pagination-bar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 22px; border-top: 1px solid var(--border);
  font-size: 0.78rem; color: var(--text-muted); flex-wrap: wrap; gap: 8px;
}
.page-link { color: var(--red) !important; }
.page-item.active .page-link { background: var(--red) !important; border-color: var(--red) !important; color: #fff !important; }
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
      <li><a href="admin_allfeedback.php">
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
      <li><a href="admin_manage_users.php" class="active">
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
        Manage Users
        <span class="tb-subtitle">System User Accounts</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" id="refreshBtn">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="btn-add-user" onclick="openAddModal()">
          <i class="bi bi-person-plus"></i> Add User
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
        <span class="live-text">
          User Accounts &nbsp;&middot;&nbsp; Manage system access and roles
        </span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="sum-card">
          <div class="sum-icon red"><i class="bi bi-people-fill"></i></div>
          <div>
            <div class="sum-val" id="sumTotal">—</div>
            <div class="sum-label">Total Users</div>
          </div>
        </div>
        <div class="sum-card">
          <div class="sum-icon green"><i class="bi bi-person-check-fill"></i></div>
          <div>
            <div class="sum-val" id="sumActive">—</div>
            <div class="sum-label">Active Users</div>
          </div>
        </div>
        <div class="sum-card">
          <div class="sum-icon blue"><i class="bi bi-shield-fill-check"></i></div>
          <div>
            <div class="sum-val" id="sumAdmins">—</div>
            <div class="sum-label">Super Admins</div>
          </div>
        </div>
        <div class="sum-card">
          <div class="sum-icon gold"><i class="bi bi-building"></i></div>
          <div>
            <div class="sum-val" id="sumDeptUsers">—</div>
            <div class="sum-label">Department Users</div>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <div class="table-title">All User Accounts</div>
            <div class="table-sub" id="recordCount">Loading...</div>
          </div>
          <div class="table-actions">
            <div class="search-wrap">
              <i class="bi bi-search"></i>
              <input type="text" id="searchInput" placeholder="Search name or email..." onkeydown="if(event.key==='Enter') loadUsers()">
            </div>
            <select class="filter-select" id="filterRole" onchange="loadUsers()">
              <option value="">All Roles</option>
              <option value="superadmin">Super Admin</option>
              <option value="dept_user">Department User</option>
            </select>
          </div>
        </div>

        <!-- Table -->
        <div style="overflow-x:auto;">
          <table class="users-table">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Username</th>
                <th>Role</th>
                <th>Department</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <tr><td colspan="8" class="text-center py-4" style="color:#9a9390;">
                <div class="spinner-border spinner-border-sm text-danger me-2"></div> Loading...
              </td></tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-bar">
          <span id="paginationInfo"></span>
          <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
        </div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main-area -->
</div><!-- /app-shell -->

<!-- ══════════ ADD/EDIT MODAL ══════════ -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">
          <i class="bi bi-person-plus"></i>
          <span id="modalTitle">Add New User</span>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="userId" value="0">

        <div class="form-group">
          <label class="form-label-sm">Full Name <span style="color:var(--red)">*</span></label>
          <input type="text" class="form-control-lg-custom" id="inputFullName" placeholder="e.g. Maria Santos">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label-sm">Username <span style="color:var(--red)">*</span></label>
            <input type="text" class="form-control-lg-custom" id="inputUsername" placeholder="e.g. mswd_user">
          </div>
          <div class="form-group">
            <label class="form-label-sm">Email <span style="color:var(--red)">*</span></label>
            <input type="email" class="form-control-lg-custom" id="inputEmail" placeholder="e.g. mswd@sanjulian.gov.ph">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label-sm">Password <span id="pwRequired" style="color:var(--red)">*</span>
            <span id="pwHint" style="font-weight:400;color:var(--text-muted);display:none;"> — leave blank to keep current</span>
          </label>
          <div class="pw-wrap">
            <input type="password" class="form-control-lg-custom" id="inputPassword" placeholder="Enter password">
            <button class="pw-toggle" type="button" onclick="togglePw()">
              <i class="bi bi-eye" id="pwEyeIcon"></i>
            </button>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label-sm">Role <span style="color:var(--red)">*</span></label>
            <select class="form-control-lg-custom" id="inputRole" onchange="handleRoleChange()">
              <option value="dept_user">Department User</option>
              <option value="superadmin">Super Admin</option>
            </select>
          </div>
          <div class="form-group" id="deptGroup">
            <label class="form-label-sm">Department <span style="color:var(--red)">*</span></label>
            <select class="form-control-lg-custom" id="inputDept">
              <option value="">— Select Department —</option>
            </select>
          </div>
        </div>

      </div>
      <div class="modal-footer" style="justify-content:flex-end;gap:10px;">
        <button class="btn-cancel-modal" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-save" onclick="saveUser()">
          <i class="bi bi-check2"></i> <span id="saveBtnText">Save User</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════ DELETE CONFIRM MODAL ══════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
    <div class="modal-content">
      <div class="modal-body" style="padding:32px;text-align:center;">
        <div class="confirm-icon"><i class="bi bi-person-x"></i></div>
        <h5 style="font-weight:700;margin-bottom:8px;">Delete User?</h5>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:4px;">
          You are about to delete:
        </p>
        <p style="font-weight:700;font-size:0.95rem;margin-bottom:20px;" id="deleteUserName">—</p>
        <p style="color:var(--text-muted);font-size:0.8rem;">This action cannot be undone.</p>
        <input type="hidden" id="deleteUserId">
        <div style="display:flex;gap:10px;justify-content:center;margin-top:20px;">
          <button class="btn-cancel-modal" data-bs-dismiss="modal">Cancel</button>
          <button class="btn-delete-confirm" onclick="confirmDelete()">
            <i class="bi bi-trash"></i> Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════ TOAST ══════════ -->
<div class="toast-container">
  <div id="toastMsg" class="toast align-items-center border-0 text-white" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastText"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script>
// ── Modals ──
const userModal   = new bootstrap.Modal(document.getElementById('userModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

// ── State ──
let currentPage    = 1;
let currentFilters = {};

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

document.getElementById('refreshBtn').addEventListener('click', () => loadUsers());

// ── Load Departments into modal dropdown ──
function loadDeptDropdown() {
  $.get('../php/get/get_departments.php', function(res) {
    if (!res.success) return;
    let opts = '<option value="">— Select Department —</option>';
    res.data.forEach(d => {
      opts += `<option value="${d.code}">${d.code} — ${d.name}</option>`;
    });
    $('#inputDept').html(opts);
  });
}

// ── Load Users ──
function loadUsers(page = 1) {
  currentPage = page;
  const search = $('#searchInput').val().trim();
  const role   = $('#filterRole').val();
  currentFilters = { search, role };

  $('#usersTableBody').html(`
    <tr><td colspan="8" class="text-center py-4" style="color:#6b6864;">
      <div class="spinner-border spinner-border-sm text-danger me-2"></div> Loading...
    </td></tr>`);

  $.get('../php/get/get_users.php', { page, per_page: 10, ...currentFilters }, function(res) {
    if (!res.success) { showToast(res.message || 'Failed to load users.', 'danger'); return; }

    // Summary cards
    $('#sumTotal').text(res.summary.total ?? 0);
    $('#sumActive').text(res.summary.active ?? 0);
    $('#sumAdmins').text(res.summary.superadmins ?? 0);
    $('#sumDeptUsers').text(res.summary.dept_users ?? 0);

    if (!res.data.length) {
      $('#usersTableBody').html(`
        <tr><td colspan="8" class="text-center py-4" style="color:#9a9390;">
          <i class="bi bi-people" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.3;"></i>
          No users found.
        </td></tr>`);
      $('#recordCount').text('0 users');
      $('#paginationInfo').text('');
      $('#paginationLinks').html('');
      return;
    }

    let rows = '';
    res.data.forEach((u, i) => {
      const num      = (page - 1) * 10 + i + 1;
      const letter   = u.full_name.charAt(0).toUpperCase();
      const roleClass= u.role === 'superadmin' ? 'superadmin' : 'dept_user';
      const roleLabel= u.role === 'superadmin' ? '<i class="bi bi-shield-fill-check"></i> Super Admin' : '<i class="bi bi-person-badge"></i> Dept User';
      const deptHtml = u.department
        ? `<span class="dept-pill">${u.department}</span>`
        : `<span class="dept-pill none">—</span>`;
      const statusClass= (u.status ?? 'active') === 'active' ? 'active' : 'inactive';
      const created  = new Date(u.created_at).toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric'});

      rows += `
      <tr>
        <td style="color:#9a9390;font-size:0.72rem;">${num}</td>
        <td>
          <div class="user-cell">
            <div class="user-avatar">${letter}</div>
            <div>
              <div class="user-name">${htmlEsc(u.full_name)}</div>
              <div class="user-email">${htmlEsc(u.email ?? '')}</div>
            </div>
          </div>
        </td>
        <td style="font-size:0.8rem;color:var(--text-muted);">@${htmlEsc(u.username)}</td>
        <td><span class="role-badge ${roleClass}">${roleLabel}</span></td>
        <td>${deptHtml}</td>
        <td>
          <span class="status-badge ${statusClass}">
            <span class="status-dot ${statusClass}"></span>
            ${statusClass.charAt(0).toUpperCase() + statusClass.slice(1)}
          </span>
        </td>
        <td style="font-size:0.75rem;color:var(--text-muted);">${created}</td>
        <td>
          <div class="action-wrap">
            <button class="btn-edit" onclick="openEditModal(${JSON.stringify(u).replace(/"/g,'&quot;')})">
              <i class="bi bi-pencil"></i> Edit
            </button>
            <button class="btn-delete" onclick="openDeleteModal(${u.id}, '${htmlEsc(u.full_name)}')">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>`;
    });

    $('#usersTableBody').html(rows);
    $('#recordCount').text(`${res.total} user${res.total !== 1 ? 's' : ''} found`);
    renderPagination(res.total, 10, page);

  }).fail(() => showToast('Server error loading users.', 'danger'));
}

// ── Pagination ──
function renderPagination(total, perPage, current) {
  const totalPages = Math.ceil(total / perPage);
  const from = (current - 1) * perPage + 1;
  const to   = Math.min(current * perPage, total);
  $('#paginationInfo').text(total ? `Showing ${from}–${to} of ${total}` : '');
  if (totalPages <= 1) { $('#paginationLinks').html(''); return; }
  let links = '';
  links += `<li class="page-item ${current===1?'disabled':''}">
    <a class="page-link" href="#" onclick="loadUsers(${current-1});return false;">‹</a></li>`;
  for (let p = Math.max(1,current-2); p <= Math.min(totalPages,current+2); p++) {
    links += `<li class="page-item ${p===current?'active':''}">
      <a class="page-link" href="#" onclick="loadUsers(${p});return false;">${p}</a></li>`;
  }
  links += `<li class="page-item ${current===totalPages?'disabled':''}">
    <a class="page-link" href="#" onclick="loadUsers(${current+1});return false;">›</a></li>`;
  $('#paginationLinks').html(links);
}

// ── Role change: show/hide dept field ──
function handleRoleChange() {
  const role = $('#inputRole').val();
  if (role === 'superadmin') {
    $('#deptGroup').hide();
    $('#inputDept').val('');
  } else {
    $('#deptGroup').show();
  }
}

// ── Open Add Modal ──
function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Add New User';
  document.getElementById('saveBtnText').textContent = 'Save User';
  document.getElementById('userId').value = 0;
  document.getElementById('inputFullName').value = '';
  document.getElementById('inputUsername').value = '';
  document.getElementById('inputEmail').value = '';
  document.getElementById('inputPassword').value = '';
  document.getElementById('inputRole').value = 'dept_user';
  document.getElementById('inputDept').value = '';
  document.getElementById('pwRequired').style.display = 'inline';
  document.getElementById('pwHint').style.display = 'none';
  $('#deptGroup').show();
  userModal.show();
}

// ── Open Edit Modal ──
function openEditModal(u) {
  document.getElementById('modalTitle').textContent = 'Edit User';
  document.getElementById('saveBtnText').textContent = 'Update User';
  document.getElementById('userId').value = u.id;
  document.getElementById('inputFullName').value = u.full_name;
  document.getElementById('inputUsername').value = u.username;
  document.getElementById('inputEmail').value = u.email ?? '';
  document.getElementById('inputPassword').value = '';
  document.getElementById('inputRole').value = u.role;
  document.getElementById('inputDept').value = u.department ?? '';
  document.getElementById('pwRequired').style.display = 'none';
  document.getElementById('pwHint').style.display = 'inline';
  handleRoleChange();
  userModal.show();
}

// ── Save User ──
function saveUser() {
  const id       = $('#userId').val();
  const fullName = $('#inputFullName').val().trim();
  const username = $('#inputUsername').val().trim();
  const email    = $('#inputEmail').val().trim();
  const password = $('#inputPassword').val().trim();
  const role     = $('#inputRole').val();
  const dept     = $('#inputDept').val();

  if (!fullName || !username || !email) {
    showToast('Name, username, and email are required.', 'danger'); return;
  }
  if (!id && !password) {
    showToast('Password is required for new users.', 'danger'); return;
  }
  if (role === 'dept_user' && !dept) {
    showToast('Please select a department.', 'danger'); return;
  }

  const data = { id, full_name: fullName, username, email, password, role, department: dept };

  $.post('../php/save/save_user.php', data, function(res) {
    if (res.success) {
      userModal.hide();
      showToast(res.message, 'success');
      loadUsers(currentPage);
    } else {
      showToast(res.message || 'Failed to save user.', 'danger');
    }
  }).fail(() => showToast('Server error.', 'danger'));
}

// ── Delete ──
function openDeleteModal(id, name) {
  document.getElementById('deleteUserId').value = id;
  document.getElementById('deleteUserName').textContent = name;
  deleteModal.show();
}

function confirmDelete() {
  const id = $('#deleteUserId').val();
  $.post('../php/delete/delete_user.php', { id }, function(res) {
    deleteModal.hide();
    if (res.success) {
      showToast(res.message, 'success');
      loadUsers(currentPage);
    } else {
      showToast(res.message || 'Failed to delete user.', 'danger');
    }
  }).fail(() => showToast('Server error.', 'danger'));
}

// ── Password toggle ──
function togglePw() {
  const input = document.getElementById('inputPassword');
  const icon  = document.getElementById('pwEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}

// ── Toast ──
function showToast(msg, type = 'success') {
  const el = document.getElementById('toastMsg');
  const tx = document.getElementById('toastText');
  el.className = `toast align-items-center border-0 text-white bg-${type === 'success' ? 'success' : 'danger'}`;
  tx.textContent = msg;
  new bootstrap.Toast(el, { delay: 3000 }).show();
}

// ── HTML escape helper ──
function htmlEsc(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Initial Load ──
loadDeptDropdown();
loadUsers(1);
</script>
</body>
</html>