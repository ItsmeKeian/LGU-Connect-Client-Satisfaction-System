<?php
// admin/admin_dashboard.php
require "../php/auth_check.php";

if (IS_DEPT_USER) {
    header("Location: ../department/dept_dashboard.php");
    exit();
}

// Get first letter of name for avatar
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Your custom sidebar/topbar CSS (pure CSS — keep as-is) -->
<link rel="stylesheet" href="../assets/css/dashboard.css"/>

<style>
  /* ── Avatar dropdown override — sits on top of everything ── */
  .tb-avatar {
    position: relative;
    cursor: pointer;
    user-select: none;
  }

  .avatar-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 200px;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.09);
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 9999;
    overflow: hidden;
    animation: dropIn 0.18s ease;
  }

  @keyframes dropIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .avatar-dropdown.show { display: block; }

  .av-header {
    padding: 14px 16px 10px;
    border-bottom: 1px solid rgba(0,0,0,0.07);
  }

  .av-name {
    font-size: 0.82rem;
    font-weight: 700;
    color: #1a1a1a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .av-role {
    font-size: 0.68rem;
    color: #B5121B;
    font-weight: 600;
    margin-top: 2px;
  }

  .av-menu { padding: 6px 0; }

  .av-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 16px;
    font-size: 0.8rem;
    color: #3a3a3a;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.15s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
  }

  .av-item:hover { background: #fdf0f0; color: #B5121B; }

  .av-item.danger { color: #B5121B; }
  .av-item.danger:hover { background: #B5121B; color: #fff; }

  .av-divider {
    height: 1px;
    background: rgba(0,0,0,0.07);
    margin: 4px 0;
  }

  .av-item i { font-size: 15px; width: 16px; }

  /* ── Stat card Bootstrap override ── */
  .stat-card .sc-value { font-size: 1.9rem; }

  /* ── Table improvements ── */
  .table thead th {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #6b6864;
    background: #faf8f5;
    border-bottom: 1px solid rgba(0,0,0,0.07);
    white-space: nowrap;
    padding: 10px 16px;
  }

  .table tbody td {
    font-size: 0.82rem;
    color: #1a1a1a;
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f5f2ee;
  }

  .table tbody tr:last-child td { border-bottom: none; }
  .table tbody tr:hover td { background: #fdf9f4; }

  /* ── Rating bar ── */
  .rating-bar-wrap { display: flex; align-items: center; gap: 8px; }
  .rating-bar {
    height: 6px;
    border-radius: 3px;
    background: #ede9e4;
    flex: 1;
    overflow: hidden;
    min-width: 60px;
  }
  .rating-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 1.2s ease;
  }

  /* ── Feedback list ── */
  .feedback-list { list-style: none; padding: 0; margin: 0; }
  .feedback-item {
    padding: 13px 20px;
    border-bottom: 1px solid #f5f2ee;
    display: flex;
    align-items: flex-start;
    gap: 13px;
    transition: background 0.15s;
  }
  .feedback-item:last-child { border-bottom: none; }
  .feedback-item:hover { background: #fdf9f4; }

  .fb-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #B5121B, #8B0000);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 0.65rem; font-weight: 700;
    flex-shrink: 0;
  }

  .fb-dept  { font-size: 0.7rem; font-weight: 700; color: #B5121B; margin-bottom: 3px; }
  .fb-comment {
    font-size: 0.8rem; color: #1a1a1a; line-height: 1.5;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 320px;
  }
  .fb-meta  { font-size: 0.67rem; color: #6b6864; margin-top: 5px; }

  /* ── Quick action buttons ── */
  .action-btn-custom {
    width: 100%; height: 40px;
    background: transparent;
    border: 1px solid rgba(0,0,0,0.09);
    border-radius: 8px;
    font-size: 0.78rem; font-weight: 500;
    color: #6b6864; cursor: pointer;
    display: flex; align-items: center;
    gap: 8px; padding: 0 14px;
    transition: all 0.2s;
    text-decoration: none;
  }
  .action-btn-custom:hover {
    background: #f4f1ed; color: #1a1a1a;
    border-color: rgba(0,0,0,0.15);
  }
  .action-btn-custom.primary {
    background: linear-gradient(135deg, #B5121B, #8B0000);
    border-color: transparent; color: #fff;
    box-shadow: 0 3px 10px rgba(181,18,27,0.28);
  }
  .action-btn-custom.primary:hover {
    box-shadow: 0 5px 16px rgba(181,18,27,0.4);
    transform: translateY(-1px);
  }

  /* ── Mini stats ── */
  .mini-stat-box {
    background: #f4f1ed;
    border-radius: 8px;
    padding: 12px 14px;
  }
  .mini-stat-box .ms-val {
    font-size: 1.3rem; font-weight: 700; line-height: 1;
  }
  .mini-stat-box .ms-label {
    font-size: 0.67rem; color: #6b6864; margin-top: 3px;
  }

  /* ── Card headers ── */
  .card-header-custom {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(0,0,0,0.07);
    display: flex; align-items: center;
    justify-content: space-between;
    background: #fff;
  }
  .card-title-custom {
    font-size: 0.88rem; font-weight: 700; color: #1a1a1a;
  }
  .card-sub-custom {
    font-size: 0.7rem; color: #6b6864; margin-top: 2px;
  }
</style>
</head>
<body>
<div class="app-shell">

  <!-- ══════════════ SIDEBAR (pure CSS — unchanged) ══════════════ -->
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

    <!-- Role badge — now PHP-populated -->
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
      <li><a href="departments.php">
        <span class="nav-icon"><i class="bi bi-building"></i></span> Departments
      </a></li>
      <li><a href="feedback.php">
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

    <!-- ✅ Logout in sidebar -->
    <div class="sb-footer">
      <a href="../php/logout.php" onclick="return confirm('Are you sure you want to sign out?')">
        <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span> Sign Out
      </a>
    </div>

  </aside>
  <!-- ══════════════ /SIDEBAR ══════════════ -->

  <!-- ══════════════ MAIN AREA ══════════════ -->
  <div class="main-area">

    <!-- ── Topbar (pure CSS — only avatar upgraded) ── -->
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        System Overview
        <span class="tb-subtitle">All Departments</span>
      </div>
      <div class="topbar-actions">
        <div class="search-wrap">
          <span class="search-icon"><i class="bi bi-search"></i></span>
          <input type="text" class="tb-search" id="globalSearch" placeholder="Search departments..."/>
        </div>
        <button class="tb-btn" id="refreshBtn"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        <button class="tb-btn primary" onclick="location.href='csmr_generator.php'">
          <i class="bi bi-file-earmark-text"></i> Generate CSMR
        </button>

        <!-- ✅ Clickable avatar with dropdown -->
        <div class="tb-avatar" id="topbarAvatar" onclick="toggleAvatarDropdown(event)">
          <?= $avatarLetter ?>
          <div class="avatar-dropdown" id="avatarDropdown">
            <div class="av-header">
              <div class="av-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
              <div class="av-role">Super Administrator</div>
            </div>
            <div class="av-menu">
              <a href="settings.php" class="av-item">
                <i class="bi bi-person-circle"></i> My Profile
              </a>
              <a href="settings.php" class="av-item">
                <i class="bi bi-gear"></i> Settings
              </a>
              <div class="av-divider"></div>
              <a href="../php/logout.php"
                 class="av-item danger"
                 onclick="return confirm('Are you sure you want to sign out?')">
                <i class="bi bi-box-arrow-right"></i> Sign Out
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>
    <!-- /topbar -->

    <!-- ══════════════ PAGE CONTENT (Bootstrap) ══════════════ -->
    <div class="page-content">

      <!-- Live bar -->
      <div class="live-bar">
        <div class="live-dot" id="liveDot"></div>
        <span class="live-text">
          Live &nbsp;&middot;&nbsp; Last updated: <span id="lastUpdated">just now</span>
        </span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- ── STAT CARDS (Bootstrap row) ── -->
      <div class="row g-3 mb-3">

        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-red">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-clipboard-data"></i></div>
            <div class="sc-value" id="statTotal">—</div>
            <div class="sc-label">Total Feedback Received</div>
            <div class="sc-change sc-up" id="statTotalChange">
              <i class="bi bi-arrow-up"></i> Loading...
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-gold">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-star-fill"></i></div>
            <div class="sc-value" id="statAvg">—</div>
            <div class="sc-label">System-Wide Avg. Rating</div>
            <div class="sc-change sc-up" id="statAvgChange">
              <i class="bi bi-arrow-up"></i> Loading...
            </div>
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
            <div class="sc-icon"><i class="bi bi-file-earmark-check"></i></div>
            <div class="sc-value" id="statReports">—</div>
            <div class="sc-label">Pending CSMR Reports</div>
            <div class="sc-change sc-down" id="statReportsChange">
              <i class="bi bi-arrow-down"></i> Loading...
            </div>
          </div>
        </div>

      </div>
      <!-- /stat cards -->

      <!-- ── CHARTS ROW (Bootstrap 2-col) ── -->
      <div class="row g-3 mb-3">

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Satisfaction Trend</div>
                <div class="card-sub-custom">Monthly average — all departments</div>
              </div>
              <span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:0.63rem;padding:4px 10px;border-radius:20px;">
                <i class="bi bi-arrow-up"></i> Improving
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
                Q1 2026
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
                <div class="card-title-custom">Service Quality Dimensions</div>
                <div class="card-sub-custom">System-wide SQD average scores</div>
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
      <!-- /charts -->

      <!-- ── DEPARTMENT PERFORMANCE TABLE ── -->
      <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
        <div class="card-header-custom">
          <div class="card-title-custom">Department Performance Summary</div>
          <button class="tb-btn" onclick="location.href='csmr_generator.php'">
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
                <th>Satisfaction</th>
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
              <button class="tb-btn" onclick="location.href='feedback.php'">View All</button>
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
                      <div class="ms-val" style="color:#2e7d32;font-size:0.95rem;">—</div>
                      <div class="ms-label">Top Dept</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#1565c0;">—</div>
                      <div class="ms-label">Due Reports</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
              <div class="card-body p-3">
                <div class="card-title-custom mb-3">Quick Actions</div>
                <div class="d-flex flex-column gap-2">
                  <a href="csmr_generator.php" class="action-btn-custom primary">
                    <i class="bi bi-file-earmark-text"></i> Generate CSMR Report
                  </a>
                  <a href="qrcodes.php" class="action-btn-custom">
                    <i class="bi bi-qr-code"></i> Manage QR Codes
                  </a>
                  <a href="export.php" class="action-btn-custom">
                    <i class="bi bi-download"></i> Export CSV / Excel
                  </a>
                  <a href="users.php" class="action-btn-custom">
                    <i class="bi bi-people"></i> Manage Users
                  </a>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
      <!-- /bottom row -->

    </div>
    <!-- /page-content -->

  </div>
  <!-- /main-area -->

</div>
<!-- /app-shell -->

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery + Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<!-- Your dashboard JS -->
<script src="../js/admin_dashboard.js"></script>

<script>
  // ── Today's date ──
  document.getElementById('todayDate').textContent =
    new Date().toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  // ── Mobile sidebar toggle ──
  document.getElementById('menuToggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('sb-open');
  });

  // ── Avatar dropdown toggle ──
  function toggleAvatarDropdown(e) {
    e.stopPropagation();
    document.getElementById('avatarDropdown').classList.toggle('show');
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function () {
    document.getElementById('avatarDropdown').classList.remove('show');
  });

  // Prevent dropdown from closing when clicking inside it
  document.getElementById('avatarDropdown').addEventListener('click', function (e) {
    e.stopPropagation();
  });
</script>

</body>
</html>