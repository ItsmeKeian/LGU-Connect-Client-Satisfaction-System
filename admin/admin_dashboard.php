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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css"/>
</head>
<body>
<div class="app-shell">

  <!-- ══════════════ SIDEBAR ══════════════ -->
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
      <li><a href="admin_dashboard.php" class="active">
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
      <li><a href="admin_manage_users.php">
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
      <a href="../php/logout.php" onclick="return confirm('Are you sure you want to sign out?')">
        <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span> Sign Out
      </a>
    </div>
  </aside>

  <!-- ══════════════ MAIN AREA ══════════════ -->
  <div class="main-area">

    <!-- Topbar -->
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        System Overview
        <span class="tb-subtitle">All Departments</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" id="refreshBtn">
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

    <!-- PAGE CONTENT -->
    <div class="page-content">

      <!-- Live bar -->
      <div class="live-bar">
        <div class="live-dot" id="liveDot"></div>
        <span class="live-text">
          Live &nbsp;&middot;&nbsp; Last updated: <span id="lastUpdated">just now</span>
        </span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- ── STAT CARDS ── -->
      <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-red">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-clipboard-data"></i></div>
            <div class="sc-value" id="statTotal">—</div>
            <div class="sc-label">Total Feedback Received</div>
            <div class="sc-change sc-neu" id="statTotalChange">Loading...</div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-gold">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-star-fill"></i></div>
            <div class="sc-value" id="statAvg">—</div>
            <div class="sc-label">System-Wide Avg. Rating</div>
            <div class="sc-change sc-neu" id="statAvgChange">Loading...</div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-grn">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-building-check"></i></div>
            <div class="sc-value" id="statDepts">—</div>
            <div class="sc-label">Active Departments</div>
            <div class="sc-change sc-neu" id="statDeptsChange">Loading...</div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-blu">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-emoji-smile"></i></div>
            <div class="sc-value" id="statReports">—</div>
            <div class="sc-label">Satisfaction Rate</div>
            <div class="sc-change sc-neu" id="statReportsChange">Loading...</div>
          </div>
        </div>
      </div>

      <!-- ── CHARTS ROW ── -->
      <div class="row g-3 mb-3">

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Satisfaction Trend</div>
                <div class="card-sub-custom">Monthly avg rating & satisfaction rate</div>
              </div>
              <span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:0.63rem;padding:4px 10px;border-radius:20px;">
                Last 8 months
              </span>
            </div>
            <div class="card-body p-3">
              <canvas id="chartTrend" height="200"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Department Comparison</div>
                <div class="card-sub-custom">Average rating per department</div>
              </div>
              <span class="badge" style="background:#fdf0f0;color:#B5121B;font-size:0.63rem;padding:4px 10px;border-radius:20px;">
                All time
              </span>
            </div>
            <div class="card-body p-3">
              <canvas id="chartDeptBar" height="200"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Service Quality Dimensions (SQD)</div>
                <div class="card-sub-custom">System-wide avg scores — ARTA standard</div>
              </div>
              <span class="badge" style="background:#fdf8e6;color:#C8991A;font-size:0.63rem;padding:4px 10px;border-radius:20px;">
                ARTA SQD
              </span>
            </div>
            <div class="card-body p-3">
              <canvas id="chartSQD" height="200"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Feedback Volume</div>
                <div class="card-sub-custom">Daily submissions — last 14 days</div>
              </div>
              <span class="badge" style="background:#e3f2fd;color:#1565c0;font-size:0.63rem;padding:4px 10px;border-radius:20px;">
                2 weeks
              </span>
            </div>
            <div class="card-body p-3">
              <canvas id="chartVolume" height="200"></canvas>
            </div>
          </div>
        </div>

      </div>

      <!-- ── DEPARTMENT TABLE ── -->
      <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
        <div class="card-header-custom">
          <div class="card-title-custom">Department Performance Summary</div>
          <button class="tb-btn" onclick="location.href='admin_csmr_generator.php'">
            Full Report <i class="bi bi-arrow-right"></i>
          </button>
        </div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Department</th>
                <th>Responses</th>
                <th>Avg Rating</th>
                <th>Stars</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="deptTableBody">
              <tr>
                <td colspan="6" class="text-center py-4" style="color:#6b6864;">
                  <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                  Loading department data...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── BOTTOM ROW ── -->
      <div class="row g-3">

        <!-- Recent Feedback -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px;overflow:hidden;">
            <div class="card-header-custom">
              <div class="card-title-custom">Recent Feedback</div>
              <button class="tb-btn" onclick="location.href='admin_allfeedback.php'">View All</button>
            </div>
            <ul class="feedback-list" id="recentFeedbackList">
              <li class="feedback-item">
                <div style="padding:20px;text-align:center;color:#6b6864;width:100%;">
                  <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                  Loading recent feedback...
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Right column -->
        <div class="col-lg-4">
          <div class="d-flex flex-column gap-3 h-100">

            <!-- This Month mini stats -->
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
              <div class="card-body p-3">
                <div class="card-title-custom mb-3">This Month</div>
                <div class="row g-2" id="monthlyMiniStats">
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#B5121B;">—</div>
                      <div class="ms-label">Responses</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#C8991A;">—</div>
                      <div class="ms-label">Avg Score</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#2e7d32;font-size:0.78rem;">—</div>
                      <div class="ms-label">Top Dept</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#1565c0;">—</div>
                      <div class="ms-label">Satisfaction</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions — FIXED URLs -->
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
              <div class="card-body p-3">
                <div class="card-title-custom mb-3">Quick Actions</div>
                <div class="d-flex flex-column gap-2">
                  <a href="admin_csmr_generator.php" class="action-btn-custom primary">
                    <i class="bi bi-file-earmark-text"></i> Generate CSMR Report
                  </a>
                  <a href="admin_qrcodes.php" class="action-btn-custom">
                    <i class="bi bi-qr-code"></i> Manage QR Codes
                  </a>
                  <a href="admin_exportdata.php" class="action-btn-custom">
                    <i class="bi bi-download"></i> Export CSV / Excel
                  </a>
                  <a href="admin_manage_users.php" class="action-btn-custom">
                    <i class="bi bi-people"></i> Manage Users
                  </a>
                  <a href="admin_analytics.php" class="action-btn-custom">
                    <i class="bi bi-bar-chart-line"></i> View Analytics
                  </a>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
      <!-- /bottom row -->

    </div><!-- /page-content -->
  </div><!-- /main-area -->
</div><!-- /app-shell -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script src="../js/admin/admin_dashboard.js"></script>
</body>
</html>