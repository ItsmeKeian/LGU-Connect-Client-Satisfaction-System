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
<title>Departments | LGU-Connect</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css"/>

<style>
  /* ── Avatar Dropdown ── */
  .tb-avatar { position: relative; cursor: pointer; user-select: none; }
  .avatar-dropdown {
    display: none; position: absolute; top: calc(100% + 10px); right: 0;
    width: 200px; background: #fff; border: 1px solid rgba(0,0,0,0.09);
    border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 9999; overflow: hidden; animation: dropIn 0.18s ease;
  }
  @keyframes dropIn {
    from { opacity:0; transform:translateY(-6px); }
    to   { opacity:1; transform:translateY(0); }
  }
  .avatar-dropdown.show { display: block; }
  .av-header { padding: 14px 16px 10px; border-bottom: 1px solid rgba(0,0,0,0.07); }
  .av-name { font-size:0.82rem; font-weight:700; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .av-role { font-size:0.68rem; color:#B5121B; font-weight:600; margin-top:2px; }
  .av-menu { padding: 6px 0; }
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

  /* ── Page header ── */
  .page-header {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 20px; gap: 12px;
  }
  .page-header-title { font-size: 1.1rem; font-weight: 700; color: #1a1a1a; }
  .page-header-sub   { font-size: 0.75rem; color: #6b6864; margin-top: 2px; }

  /* ── Search bar ── */
  .dept-search-wrap {
    position: relative; max-width: 280px; width: 100%;
  }
  .dept-search-wrap .bi-search {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%); color: #9a9390; font-size: 13px;
  }
  .dept-search-input {
    width: 100%; height: 38px; padding: 0 14px 0 34px;
    background: #fff; border: 1px solid rgba(0,0,0,0.1);
    border-radius: 8px; font-size: 0.82rem; color: #1a1a1a; outline: none;
    transition: all 0.2s;
  }
  .dept-search-input:focus { border-color: #B5121B; box-shadow: 0 0 0 3px rgba(181,18,27,0.08); }

  /* ── Stat summary cards (top) ── */
  .summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
  .summary-card {
    background: #fff; border-radius: 12px; padding: 16px 18px;
    border: 1px solid rgba(0,0,0,0.07); box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    display: flex; align-items: center; gap: 14px;
  }
  .summary-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
  }
  .summary-icon.red    { background: #fdf0f0; color: #B5121B; }
  .summary-icon.gold   { background: #fdf8e6; color: #C8991A; }
  .summary-icon.green  { background: #e8f5e9; color: #2e7d32; }
  .summary-icon.blue   { background: #e3f2fd; color: #1565c0; }
  .summary-val  { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; line-height: 1; }
  .summary-label{ font-size: 0.7rem; color: #6b6864; margin-top: 3px; }

  /* ── Department cards grid ── */
  .dept-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
  }

  .dept-card {
    background: #fff; border-radius: 14px;
    border: 1px solid rgba(0,0,0,0.07);
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    overflow: hidden; transition: all 0.22s ease;
  }
  .dept-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }

  .dept-card-top {
    height: 6px;
  }

  .dept-card-body { padding: 18px 18px 14px; }

  .dept-card-head {
    display: flex; align-items: flex-start;
    justify-content: space-between; margin-bottom: 12px;
  }

  .dept-badge-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; background: #fdf0f0; color: #B5121B;
    flex-shrink: 0;
  }

  .dept-actions { display: flex; gap: 6px; }
  .dept-action-btn {
    width: 30px; height: 30px; border-radius: 7px;
    border: 1px solid rgba(0,0,0,0.09); background: transparent;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; color: #6b6864; cursor: pointer;
    transition: all 0.15s;
  }
  .dept-action-btn:hover.edit   { background: #e3f2fd; color: #1565c0; border-color: #1565c0; }
  .dept-action-btn:hover.qr     { background: #e8f5e9; color: #2e7d32; border-color: #2e7d32; }
  .dept-action-btn:hover.delete { background: #fdf0f0; color: #B5121B; border-color: #B5121B; }

  .dept-name { font-size: 0.95rem; font-weight: 700; color: #1a1a1a; margin-bottom: 2px; }
  .dept-code { font-size: 0.7rem; color: #6b6864; }

  .dept-stats {
    display: grid; grid-template-columns: 1fr 1fr 1fr;
    gap: 8px; margin: 14px 0;
  }
  .dept-stat { text-align: center; }
  .dept-stat-val   { font-size: 1.05rem; font-weight: 700; color: #1a1a1a; }
  .dept-stat-label { font-size: 0.62rem; color: #9a9390; margin-top: 2px; }

  .dept-rating-bar {
    height: 5px; border-radius: 3px;
    background: #ede9e4; overflow: hidden; margin-bottom: 12px;
  }
  .dept-rating-fill {
    height: 100%; border-radius: 3px;
    background: linear-gradient(to right, #B5121B, #F0C030);
    transition: width 1s ease;
  }

  .dept-card-foot {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 10px 18px 14px;
    border-top: 1px solid rgba(0,0,0,0.05);
  }

  .dept-status {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.67rem; font-weight: 600; padding: 3px 10px;
    border-radius: 20px;
  }
  .dept-status.active   { background: #e8f5e9; color: #2e7d32; }
  .dept-status.inactive { background: #f5f5f5; color: #757575; }
  .dept-status .sdot {
    width: 5px; height: 5px; border-radius: 50%; background: currentColor;
  }

  .btn-view-dept {
    font-size: 0.72rem; font-weight: 600; color: #B5121B;
    text-decoration: none; display: flex; align-items: center; gap: 4px;
    transition: gap 0.15s;
  }
  .btn-view-dept:hover { gap: 8px; color: #8B0000; }

  /* ── Add Department button ── */
  .btn-add-dept {
    height: 38px; padding: 0 18px;
    background: linear-gradient(135deg, #B5121B, #8B0000);
    border: none; border-radius: 8px; color: #fff;
    font-size: 0.8rem; font-weight: 600;
    display: flex; align-items: center; gap: 7px;
    cursor: pointer; box-shadow: 0 3px 10px rgba(181,18,27,0.28);
    transition: all 0.2s; white-space: nowrap;
  }
  .btn-add-dept:hover {
    box-shadow: 0 5px 16px rgba(181,18,27,0.4);
    transform: translateY(-1px);
  }

  /* ── Empty state ── */
  .empty-state {
    grid-column: 1 / -1; text-align: center;
    padding: 60px 20px; color: #9a9390;
  }
  .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; opacity: 0.4; }
  .empty-state p { font-size: 0.85rem; }

  /* ── Modal overrides ── */
  .modal-header {
    background: linear-gradient(135deg, #B5121B, #8B0000);
    color: #fff; border-radius: 12px 12px 0 0;
    padding: 18px 24px;
  }
  .modal-title { font-size: 0.95rem; font-weight: 700; }
  .modal-header .btn-close { filter: invert(1); opacity: 0.8; }
  .modal-content { border-radius: 12px; border: none; box-shadow: 0 16px 48px rgba(0,0,0,0.18); }
  .modal-body { padding: 24px; }
  .modal-footer { padding: 14px 24px; border-top: 1px solid rgba(0,0,0,0.07); }

  .form-label { font-size: 0.78rem; font-weight: 600; color: #3a3a3a; margin-bottom: 5px; }
  .form-control, .form-select {
    font-size: 0.82rem; border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.12); padding: 9px 13px;
    transition: all 0.2s;
  }
  .form-control:focus, .form-select:focus {
    border-color: #B5121B;
    box-shadow: 0 0 0 3px rgba(181,18,27,0.1);
  }

  .btn-submit-dept {
    background: linear-gradient(135deg, #B5121B, #8B0000);
    border: none; color: #fff; padding: 9px 24px;
    border-radius: 8px; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-submit-dept:hover { box-shadow: 0 4px 12px rgba(181,18,27,0.35); }

  /* ── QR Modal ── */
  .qr-preview-wrap {
    display: flex; flex-direction: column; align-items: center;
    gap: 14px; padding: 10px 0;
  }
  .qr-preview-wrap img { border-radius: 10px; border: 2px solid rgba(0,0,0,0.08); }
  .qr-dept-name { font-size: 1rem; font-weight: 700; color: #1a1a1a; }
  .qr-link {
    font-size: 0.72rem; color: #6b6864;
    background: #f4f1ed; padding: 6px 14px;
    border-radius: 6px; word-break: break-all; text-align: center;
  }

  /* ── Alert toast ── */
  .toast-container { position: fixed; top: 20px; right: 20px; z-index: 99999; }

  /* ── Responsive ── */
  @media (max-width: 900px) {
    .summary-cards { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 600px) {
    .summary-cards { grid-template-columns: 1fr; }
    .dept-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>
<div class="app-shell">

  <!-- ══════════ SIDEBAR ══════════ -->
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
// ── Base URL for feedback form (adjust to your actual domain) ──
const BASE_URL = window.location.origin + '/lgu-connect/feedback.php?dept=';

// ── Department color map ──
const DEPT_COLORS = [
  '#B5121B','#1565c0','#2e7d32','#e65100',
  '#6a1b9a','#00838f','#f9a825','#4e342e',
  '#37474f','#ad1457'
];

// ── Modals ──
const deptModal   = new bootstrap.Modal(document.getElementById('deptModal'));
const qrModal     = new bootstrap.Modal(document.getElementById('qrModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
let deleteTargetId = null;

// ── Today's date ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH', {weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Mobile sidebar ──
document.getElementById('menuToggle').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('sb-open');
});

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});
document.getElementById('avatarDropdown').addEventListener('click', e => e.stopPropagation());

// ── Refresh button ──
document.getElementById('refreshBtn').addEventListener('click', loadDepartments);

// ── Search ──
document.getElementById('deptSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.dept-card').forEach(card => {
    const name = card.dataset.name.toLowerCase();
    const code = card.dataset.code.toLowerCase();
    card.closest('.dept-card-wrapper').style.display =
      (name.includes(q) || code.includes(q)) ? '' : 'none';
  });
});

// ── Load departments via AJAX ──
function loadDepartments() {
  $('#deptGrid').html(`
    <div class="empty-state">
      <div class="spinner-border text-danger" role="status"></div>
      <p class="mt-3">Loading departments...</p>
    </div>`);

  $.get('../php/get/get_departments.php', function(res) {
    if (!res.success || !res.data.length) {
      $('#deptGrid').html(`
        <div class="empty-state">
          <i class="bi bi-building-x"></i>
          <p>No departments found.<br>Click <strong>Add Department</strong> to get started.</p>
        </div>`);
      updateSummary([]);
      return;
    }
    updateSummary(res.data);
    renderCards(res.data);
  }).fail(() => showToast('Failed to load departments.', 'danger'));
}

// ── Render department cards ──
function renderCards(depts) {
  let html = '';
  depts.forEach((d, i) => {
    const color    = DEPT_COLORS[i % DEPT_COLORS.length];
    const rating   = parseFloat(d.avg_rating) || 0;
    const ratingW  = (rating / 5 * 100).toFixed(1);
    const stars    = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
    const statusCls = d.status === 'active' ? 'active' : 'inactive';
    const statusLbl = d.status === 'active' ? 'Active' : 'Inactive';
    const satisfaction = rating >= 4 ? 'Satisfied' : rating >= 3 ? 'Moderate' : 'Needs Improvement';
    const satColor = rating >= 4 ? '#2e7d32' : rating >= 3 ? '#e65100' : '#B5121B';

    html += `
    <div class="dept-card-wrapper">
      <div class="dept-card" data-name="${d.name}" data-code="${d.code}">
        <div class="dept-card-top" style="background:${color};"></div>
        <div class="dept-card-body">
          <div class="dept-card-head">
            <div class="dept-badge-icon" style="background:${color}18;color:${color};">
              <i class="bi bi-building"></i>
            </div>
            <div class="dept-actions">
              <button class="dept-action-btn edit" title="Edit"
                onclick="openEditModal(${JSON.stringify(d).replace(/'/g,'&#39;')})">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="dept-action-btn qr" title="View QR Code"
                onclick="openQrModal('${d.code}','${d.name}')">
                <i class="bi bi-qr-code"></i>
              </button>
              <button class="dept-action-btn delete" title="Delete"
                onclick="openDeleteModal(${d.id},'${d.name}')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>

          <div class="dept-name">${d.name}</div>
          <div class="dept-code" style="color:${color}; font-weight:600; font-size:0.72rem;">${d.code}</div>
          ${d.head ? `<div class="dept-code mt-1"><i class="bi bi-person me-1"></i>${d.head}</div>` : ''}

          <div class="dept-stats">
            <div class="dept-stat">
              <div class="dept-stat-val" style="color:${color};">${d.feedback_count ?? 0}</div>
              <div class="dept-stat-label">Responses</div>
            </div>
            <div class="dept-stat">
              <div class="dept-stat-val">${rating > 0 ? rating.toFixed(1) : '—'}</div>
              <div class="dept-stat-label">Avg Rating</div>
            </div>
            <div class="dept-stat">
              <div class="dept-stat-val" style="color:${satColor};font-size:0.7rem;">${rating > 0 ? satisfaction : '—'}</div>
              <div class="dept-stat-label">Status</div>
            </div>
          </div>

          <div class="dept-rating-bar">
            <div class="dept-rating-fill" style="width:${ratingW}%;background:linear-gradient(to right,${color},#F0C030);"></div>
          </div>
          <div style="font-size:0.72rem;color:#C8991A;letter-spacing:1px;">${stars}</div>
        </div>

        <div class="dept-card-foot">
          <span class="dept-status ${statusCls}">
            <span class="sdot"></span> ${statusLbl}
          </span>
          <a href="admin_allfeedback.php?dept=${d.code}" class="btn-view-dept">
            View Feedback <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>`;
  });
  $('#deptGrid').html(html);
}

// ── Update summary cards ──
function updateSummary(depts) {
  const total    = depts.length;
  const active   = depts.filter(d => d.status === 'active').length;
  const totalFB  = depts.reduce((s, d) => s + (parseInt(d.feedback_count) || 0), 0);
  const avgR     = depts.length
    ? (depts.reduce((s,d) => s + (parseFloat(d.avg_rating)||0), 0) / depts.length).toFixed(2)
    : '—';

  $('#sumTotal').text(total);
  $('#sumActive').text(active);
  $('#sumAvgRating').text(avgR);
  $('#sumTotalFeedback').text(totalFB);
}

// ── Add department modal ──
function openAddModal() {
  document.getElementById('deptModalTitle').innerHTML =
    '<i class="bi bi-building-add me-2"></i> Add New Department';
  document.getElementById('deptEditId').value = '';
  document.getElementById('deptName').value   = '';
  document.getElementById('deptCode').value   = '';
  document.getElementById('deptStatus').value = 'active';
  document.getElementById('deptDesc').value   = '';
  document.getElementById('deptHead').value   = '';
  deptModal.show();
}

// ── Edit department modal ──
function openEditModal(d) {
  document.getElementById('deptModalTitle').innerHTML =
    '<i class="bi bi-pencil-square me-2"></i> Edit Department';
  document.getElementById('deptEditId').value  = d.id;
  document.getElementById('deptName').value    = d.name;
  document.getElementById('deptCode').value    = d.code;
  document.getElementById('deptStatus').value  = d.status;
  document.getElementById('deptDesc').value    = d.description ?? '';
  document.getElementById('deptHead').value    = d.head ?? '';
  deptModal.show();
}

// ── Save department (add or edit) ──
function saveDepartment() {
  const id   = document.getElementById('deptEditId').value;
  const name = document.getElementById('deptName').value.trim();
  const code = document.getElementById('deptCode').value.trim().toUpperCase();

  if (!name || !code) {
    showToast('Department name and code are required.', 'danger');
    return;
  }

  const payload = {
    id:          id,
    name:        name,
    code:        code,
    status:      document.getElementById('deptStatus').value,
    description: document.getElementById('deptDesc').value.trim(),
    head:        document.getElementById('deptHead').value.trim(),
  };

  const btn = document.getElementById('deptSaveBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

  $.post('../php/save/save_department.php', payload, function(res) {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Department';
    if (res.success) {
      deptModal.hide();
      showToast(res.message, 'success');
      loadDepartments();
    } else {
      showToast(res.message || 'Error saving department.', 'danger');
    }
  }).fail(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Department';
    showToast('Server error. Please try again.', 'danger');
  });
}

// ── Delete modal ──
function openDeleteModal(id, name) {
  deleteTargetId = id;
  document.getElementById('deleteConfirmName').textContent = name;
  deleteModal.show();
}

function confirmDelete() {
  if (!deleteTargetId) return;
  const btn = document.getElementById('confirmDeleteBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

  $.post('../php/delete/delete_department.php', {id: deleteTargetId}, function(res) {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-trash me-1"></i> Yes, Delete';
    deleteModal.hide();
    if (res.success) {
      showToast(res.message, 'success');
      loadDepartments();
    } else {
      showToast(res.message || 'Error deleting.', 'danger');
    }
  });
}

// ── QR Code modal ──
function openQrModal(code, name) {
  const link = BASE_URL + encodeURIComponent(code);
  const qrSrc = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(link)}`;

  document.getElementById('qrDeptName').textContent = name;
  document.getElementById('qrLink').textContent      = link;
  document.getElementById('qrImage').src             = qrSrc;
  document.getElementById('qrDownloadBtn').href      = qrSrc;
  document.getElementById('qrDownloadBtn').download  = `QR_${code}.png`;
  qrModal.show();
}

function copyQrLink() {
  const link = document.getElementById('qrLink').textContent;
  navigator.clipboard.writeText(link).then(() => showToast('Link copied to clipboard!', 'success'));
}

// ── Toast helper ──
function showToast(msg, type = 'success') {
  const toastEl = document.getElementById('toastMsg');
  const toastTx = document.getElementById('toastText');
  toastEl.className = `toast align-items-center border-0 text-white bg-${type === 'success' ? 'success' : 'danger'}`;
  toastTx.textContent = msg;
  const t = new bootstrap.Toast(toastEl, {delay: 3000});
  t.show();
}

// ── Initial load ──
loadDepartments();
</script>
</body>
</html>