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

<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_manage_user.css"/>

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
      <li><a href="admin_csmr_generator.php">
        <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> CSMR Generator
      </a></li>
      <li><a href="admin_analytics.php">
        <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics
      </a></li>
      <li><a href="admin_predictive.php">
        <span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span> Predictive Analytics
      </a></li>
      <li><a href="admin_exportdata.php">
        <span class="nav-icon"><i class="bi bi-download"></i></span> Export Data
      </a></li>
    </ul>
    <div class="sb-section">System</div>
    <ul class="sb-nav">
      <li><a href="admin_manage_users.php" class="active">
        <span class="nav-icon"><i class="bi bi-people"></i></span> Manage Users
      </a></li>
      <li><a href="admin_qrcodes.php">
        <span class="nav-icon"><i class="bi bi-qr-code"></i></span> QR Codes
      </a></li>
      <li><a href="admin_settings.php">
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
<script src="../js/admin/admin_manage_user.js"></script>
</body>
</html>