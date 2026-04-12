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
<title>LGU-Connect | Municipality of San Julian</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_departments.css"/>


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
      <li><a href="admin_departments.php" class="active">
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
      <li><a href="admin_manage_users.php">
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
      <a href="../php/logout.php" onclick="return confirm('Are you sure you want to sign out?')">
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
        Departments
        <span class="tb-subtitle">Manage All Departments</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" id="refreshBtn">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="tb-btn primary" onclick="location.href='csmr_generator.php'">
          <i class="bi bi-file-earmark-text"></i> Generate CSMR
        </button>
        <!-- Avatar dropdown -->
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
        <span class="live-text">Live &nbsp;&middot;&nbsp; Departments Registry</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- Summary stat cards -->
      <div class="summary-cards" id="summaryCards">
        <div class="summary-card">
          <div class="summary-icon red"><i class="bi bi-building"></i></div>
          <div>
            <div class="summary-val" id="sumTotal">—</div>
            <div class="summary-label">Total Departments</div>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon green"><i class="bi bi-check-circle"></i></div>
          <div>
            <div class="summary-val" id="sumActive">—</div>
            <div class="summary-label">Active</div>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon gold"><i class="bi bi-star-fill"></i></div>
          <div>
            <div class="summary-val" id="sumAvgRating">—</div>
            <div class="summary-label">Avg Rating (All)</div>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon blue"><i class="bi bi-clipboard-data"></i></div>
          <div>
            <div class="summary-val" id="sumTotalFeedback">—</div>
            <div class="summary-label">Total Feedback</div>
          </div>
        </div>
      </div>

      <!-- Page header: search + add button -->
      <div class="page-header">
        <div>
          <div class="page-header-title">All Departments</div>
          <div class="page-header-sub">Click a card to view department details</div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="dept-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="dept-search-input" id="deptSearch"
                   placeholder="Search department...">
          </div>
          <button class="btn-add-dept" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> Add Department
          </button>
        </div>
      </div>

      <!-- Department cards grid -->
      <div class="dept-grid" id="deptGrid">
        <!-- Populated by JS -->
        <div class="empty-state">
          <div class="spinner-border text-danger" role="status"></div>
          <p class="mt-3">Loading departments...</p>
        </div>
      </div>

    </div>
    <!-- /page-content -->
  </div>
</div>

<!-- ══════════ ADD / EDIT DEPARTMENT MODAL ══════════ -->
<div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title" id="deptModalTitle">
          <i class="bi bi-building-add me-2"></i> Add New Department
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="deptEditId" value="">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Department Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="deptName"
                   placeholder="e.g. Municipal Social Welfare and Development">
          </div>
          <div class="col-md-6">
            <label class="form-label">Department Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="deptCode"
                   placeholder="e.g. MSWD" maxlength="10"
                   style="text-transform:uppercase">
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" id="deptStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="deptDesc" rows="2"
                      placeholder="Brief description of services offered..."></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Head / Officer-in-Charge</label>
            <input type="text" class="form-control" id="deptHead"
                   placeholder="e.g. Maria Santos">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-submit-dept" id="deptSaveBtn" onclick="saveDepartment()">
          <i class="bi bi-check-lg me-1"></i> Save Department
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════ QR CODE MODAL ══════════ -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title"><i class="bi bi-qr-code me-2"></i> QR Code</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="qr-preview-wrap">
          <img id="qrImage" src="" alt="QR Code" width="180" height="180">
          <div class="qr-dept-name" id="qrDeptName"></div>
          <div class="qr-link" id="qrLink"></div>
        </div>
      </div>
      <div class="modal-footer justify-content-center gap-2">
        <a id="qrDownloadBtn" href="#" download class="btn-submit-dept" style="text-decoration:none;padding:9px 18px;">
          <i class="bi bi-download me-1"></i> Download QR
        </a>
        <button class="btn btn-light" onclick="copyQrLink()">
          <i class="bi bi-link-45deg me-1"></i> Copy Link
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════ DELETE CONFIRM MODAL ══════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg,#c62828,#8B0000);">
        <span class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i> Confirm Delete</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <i class="bi bi-building-x" style="font-size:40px;color:#B5121B;"></i>
        <p class="mt-3 mb-1" style="font-size:0.9rem;font-weight:600;">Delete this department?</p>
        <p class="text-muted" style="font-size:0.78rem;" id="deleteConfirmName"></p>
        <p style="font-size:0.73rem;color:#c62828;">
          All feedback records for this department will also be affected.
        </p>
      </div>
      <div class="modal-footer justify-content-center gap-2">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-submit-dept" id="confirmDeleteBtn"
                style="background:linear-gradient(135deg,#c62828,#8B0000);"
                onclick="confirmDelete()">
          <i class="bi bi-trash me-1"></i> Yes, Delete
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Toast notifications -->
<div class="toast-container">
  <div id="toastMsg" class="toast align-items-center border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body fw-500" id="toastText" style="font-size:0.82rem;"></div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<!-- Bootstrap JS + jQuery -->
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script src="../js/admin/admin_departments.js"></script>

</body>
</html>