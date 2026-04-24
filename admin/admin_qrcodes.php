<?php
require "../php/auth_check.php";
requireSuperAdmin();
$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));

// Base URL of the feedback form 
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST']
          . '/lgu-connect/feedback.php';
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
<link rel="stylesheet" href="../assets/css/admin_qrcodes.css">

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
      <li><a href="admin_allfeedback.php"><span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> All Feedback <span class="nav-badge" id="sbFeedbackCount">0</span></a></li>
    </ul>
    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="admin_csmr_generator.php"><span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> CSMR Generator</a></li>
      <li><a href="admin_analytics.php"><span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Analytics</a></li>
      <li><a href="admin_predictive.php">
        <span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span> Predictive Analytics
      </a></li>
      <li><a href="admin_exportdata.php"><span class="nav-icon"><i class="bi bi-download"></i></span> Export Data</a></li>
    </ul>
    <div class="sb-section">System</div>
    <ul class="sb-nav">
      <li><a href="admin_manage_users.php"><span class="nav-icon"><i class="bi bi-people"></i></span> Manage Users</a></li>
      <li><a href="admin_qrcodes.php" class="active"><span class="nav-icon"><i class="bi bi-qr-code"></i></span> QR Codes</a></li>
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
        QR Codes
        <span class="tb-subtitle">Department Feedback QR Codes</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="location.reload()">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
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

    <div class="page-content">
      <div class="live-bar">
        <div class="live-dot"></div>
        <span class="live-text">Live &nbsp;&middot;&nbsp; QR codes for citizen feedback collection per department</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="qr-content">

        <!-- Info bar -->
        <div class="info-bar">
          <div class="info-bar-icon"><i class="bi bi-qr-code-scan"></i></div>
          <div class="info-bar-text">
            <h4>How QR Codes Work</h4>
            <p>Each department has a unique QR code. Citizens scan it with their phone to open the feedback form pre-filled with that department.</p>
          </div>
          <div class="info-bar-url">
            <i class="bi bi-link-45deg"></i>
            <span id="baseUrlDisplay"><?= htmlspecialchars($base_url) ?>?dept=<em>CODE</em></span>
          </div>
        </div>

        <!-- Controls -->
        <div class="controls-bar">
          <div style="font-size:13px;font-weight:600;color:#555;display:flex;align-items:center;gap:7px">
            <i class="bi bi-grid-3x3" style="color:var(--red-main)"></i>
            <span id="deptCountLabel">Loading departments…</span>
          </div>

          <div style="display:flex;align-items:center;gap:8px;margin-left:16px">
            <span style="font-size:12px;color:#888">QR Size:</span>
            <div class="size-chips">
              <span class="size-chip" data-size="128">Small</span>
              <span class="size-chip active" data-size="180">Medium</span>
              <span class="size-chip" data-size="240">Large</span>
            </div>
          </div>

          <div style="display:flex;align-items:center;gap:8px;margin-left:8px">
            <span style="font-size:12px;color:#888">Filter:</span>
            <select id="statusFilter" onchange="filterCards()">
              <option value="all">All Departments</option>
              <option value="active">Active Only</option>
              <option value="inactive">Inactive Only</option>
            </select>
          </div>

          <button class="btn-print-all" onclick="window.print()">
            <i class="bi bi-printer"></i> Print All QR Codes
          </button>
        </div>

        <!-- QR Grid -->
        <div class="qr-grid" id="qrGrid">
          <div class="qr-loading">
            <div class="spinner"></div>
            <p>Generating QR codes…</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- QRCode.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script src="../js/admin/admin_qrcodes.js"></script>
<script>

const BASE_URL   = <?= json_encode($base_url) ?>;
let   qrSize     = 180;
let   allDepts   = [];
let   deptStats  = {};


</script>
</body>
</html>