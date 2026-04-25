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
<link rel="stylesheet" href="../assets/css/admin_settings.css"/>
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
      <li><a href="admin_qrcodes.php"><span class="nav-icon"><i class="bi bi-qr-code"></i></span> QR Codes</a></li>
      <li><a href="admin_settings.php" class="active"><span class="nav-icon"><i class="bi bi-gear"></i></span> Settings</a></li>
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
        Settings
        <span class="tb-subtitle">System Configuration</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
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
        <span class="live-text">Live &nbsp;&middot;&nbsp; Manage LGU information, system preferences, and database maintenance</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="settings-content">

        <!-- ── LEFT: Settings Nav ── -->
        <div class="settings-nav">
          <div class="settings-nav-header">Settings</div>
          <a class="settings-nav-item active" onclick="showPanel('lgu')" href="#">
            <i class="bi bi-building-fill"></i> LGU Information
          </a>
          <a class="settings-nav-item" onclick="showPanel('system')" href="#">
            <i class="bi bi-sliders"></i> System Preferences
          </a>
          <a class="settings-nav-item" onclick="showPanel('maintenance')" href="#">
            <i class="bi bi-tools"></i> DB Maintenance
          </a>
        </div>

        <!-- ── RIGHT: Panels ── -->
        <div>

          <!-- ══ PANEL 1: LGU Information ══ -->
          <div class="settings-panel active" id="panel-lgu">

            <!-- Basic Info -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="bi bi-building-fill"></i>
                <div>
                  <h3>LGU Basic Information</h3>
                  <p>Used in CSMR reports, print headers, and feedback forms</p>
                </div>
              </div>
              <div class="settings-card-body">
                <div class="form-grid">
                  <div class="form-group">
                    <label>Municipality Name</label>
                    <input type="text" id="lgu_name" placeholder="e.g. Municipality of San Julian"/>
                  </div>
                  <div class="form-group">
                    <label>Province</label>
                    <input type="text" id="lgu_province" placeholder="e.g. Eastern Samar"/>
                  </div>
                  <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="lgu_address" placeholder="e.g. San Julian, Eastern Samar"/>
                  </div>
                  <div class="form-group">
                    <label>Region</label>
                    <input type="text" id="lgu_region" placeholder="e.g. Region VIII"/>
                  </div>
                  <div class="form-group">
                    <label>Municipal Mayor</label>
                    <input type="text" id="lgu_mayor" placeholder="Full name of the Mayor"/>
                  </div>
                </div>
                <div class="btn-save-wrap" style="margin-top:18px">
                  <button class="btn-save" onclick="saveLGUSettings()">
                    <i class="bi bi-floppy"></i> Save LGU Info
                  </button>
                </div>
              </div>
            </div>

            <!-- Contact Info -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="bi bi-telephone-fill"></i>
                <div>
                  <h3>Contact Information</h3>
                  <p>Displayed in official reports and feedback receipts</p>
                </div>
              </div>
              <div class="settings-card-body">
                <div class="form-grid">
                  <div class="form-group">
                    <label>Official Email</label>
                    <input type="email" id="lgu_email" placeholder="e.g. sanjulian@gov.ph"/>
                  </div>
                  <div class="form-group">
                    <label>Phone / Landline</label>
                    <input type="tel" id="lgu_phone" placeholder="e.g. (055) 123-4567"/>
                  </div>
                  <div class="form-group">
                    <label>Official Website</label>
                    <input type="url" id="lgu_website" placeholder="e.g. https://sanjulian.gov.ph"/>
                    <span class="field-hint">Include https://</span>
                  </div>
                </div>
                <div class="btn-save-wrap" style="margin-top:18px">
                  <button class="btn-save" onclick="saveContactSettings()">
                    <i class="bi bi-floppy"></i> Save Contact Info
                  </button>
                </div>
              </div>
            </div>

          </div><!-- /panel-lgu -->

          <!-- ══ PANEL 2: System Preferences ══ -->
          <div class="settings-panel" id="panel-system">

            <div class="settings-card">
              <div class="settings-card-header">
                <i class="bi bi-toggles"></i>
                <div>
                  <h3>Feedback Collection</h3>
                  <p>Control how citizen feedback is collected</p>
                </div>
              </div>
              <div class="settings-card-body">
                <div class="toggle-row">
                  <div class="toggle-info">
                    <h4>Feedback Form Status</h4>
                    <p>When disabled, citizens cannot submit new feedback. Existing data is not affected.</p>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" id="feedback_open" checked/>
                    <span class="toggle-slider"></span>
                  </label>
                </div>
                <div class="btn-save-wrap" style="margin-top:18px">
                  <button class="btn-save" onclick="saveSystemSettings()">
                    <i class="bi bi-floppy"></i> Save Preferences
                  </button>
                </div>
              </div>
            </div>

            <div class="settings-card">
              <div class="settings-card-header">
                <i class="bi bi-sliders2"></i>
                <div>
                  <h3>System Parameters</h3>
                  <p>Fine-tune system behavior and limits</p>
                </div>
              </div>
              <div class="settings-card-body">
                <div class="form-grid">
                  <div class="form-group">
                    <label>Dashboard Refresh Interval (seconds)</label>
                    <input type="number" id="refresh_interval" min="5" max="300" placeholder="30"/>
                    <span class="field-hint">How often the dashboard auto-refreshes. Min: 5s, Max: 300s</span>
                  </div>
                  <div class="form-group">
                    <label>Max Export Rows</label>
                    <input type="number" id="max_export_rows" min="100" max="10000" placeholder="500"/>
                    <span class="field-hint">Maximum rows per CSV/Excel export. Default: 500</span>
                  </div>
                  <div class="form-group" style="grid-column:1/-1">
                    <label>CSMR Report Footer Note</label>
                    <textarea id="csmr_footer_note" placeholder="Optional note to appear at the bottom of all CSMR reports…"></textarea>
                    <span class="field-hint">Leave blank to use the default footer</span>
                  </div>
                </div>
                <div class="btn-save-wrap" style="margin-top:6px">
                  <button class="btn-save" onclick="saveSystemSettings()">
                    <i class="bi bi-floppy"></i> Save Parameters
                  </button>
                </div>
              </div>
            </div>

          </div><!-- /panel-system -->

          <!-- ══ PANEL 3: DB Maintenance ══ -->
          <div class="settings-panel" id="panel-maintenance">

            <!-- System Info -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                  <h3>System Information</h3>
                  <p>Current database and server status</p>
                </div>
              </div>
              <div class="settings-card-body">
                <!-- DB Stats row -->
                <div class="db-stats-grid" id="dbStatsGrid">
                  <div class="db-stat-box"><div class="dsv" id="ds-feedback">—</div><div class="dsl">Feedback Records</div></div>
                  <div class="db-stat-box"><div class="dsv" id="ds-departments">—</div><div class="dsl">Departments</div></div>
                  <div class="db-stat-box"><div class="dsv" id="ds-users">—</div><div class="dsl">Users</div></div>
                  <div class="db-stat-box"><div class="dsv" id="ds-export_logs">—</div><div class="dsl">Export Logs</div></div>
                  <div class="db-stat-box"><div class="dsv" id="ds-dbsize">—</div><div class="dsl">DB Size (KB)</div></div>
                </div>

                <!-- Sysinfo table -->
                <table class="sysinfo-table">
                  <tr><td>Database Name</td><td id="si-dbname">—</td></tr>
                  <tr><td>MySQL Version</td><td id="si-mysql">—</td></tr>
                  <tr><td>PHP Version</td><td id="si-php">—</td></tr>
                  <tr><td>Oldest Feedback</td><td id="si-oldest">—</td></tr>
                  <tr><td>Newest Feedback</td><td id="si-newest">—</td></tr>
                  <tr><td>Total Feedback Records</td><td id="si-total">—</td></tr>
                </table>

                <div style="margin-top:14px;text-align:right">
                  <button class="btn-maint neutral" onclick="loadDBStats()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                  </button>
                </div>
              </div>
            </div>

            <!-- Maintenance Actions -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="bi bi-tools"></i>
                <div>
                  <h3>Maintenance Actions</h3>
                  <p>Irreversible actions — proceed with caution</p>
                </div>
              </div>
              <div class="settings-card-body">

                <!-- Clear old feedback -->
                <div class="maint-action">
                  <div class="maint-info">
                    <h4><i class="bi bi-trash3" style="color:#c0392b;margin-right:5px"></i> Clear Old Feedback</h4>
                    <p>Permanently delete feedback records older than the selected period. Cannot be undone.</p>
                  </div>
                  <div class="inline-row">
                    <select id="clearMonths">
                      <option value="3">Older than 3 months</option>
                      <option value="6">Older than 6 months</option>
                      <option value="12" selected>Older than 1 year</option>
                      <option value="24">Older than 2 years</option>
                    </select>
                    <button class="btn-maint danger" onclick="clearOldFeedback()">
                      <i class="bi bi-trash3"></i> Clear
                    </button>
                  </div>
                </div>

                <!-- Clear export logs -->
                <div class="maint-action">
                  <div class="maint-info">
                    <h4><i class="bi bi-clock-history" style="color:#b06c10;margin-right:5px"></i> Clear Export History</h4>
                    <p>Remove all export log entries from the database. The exported files themselves are not affected.</p>
                  </div>
                  <button class="btn-maint warning" onclick="clearExportLogs()">
                    <i class="bi bi-eraser"></i> Clear Logs
                  </button>
                </div>

              </div>
            </div>

          </div><!-- /panel-maintenance -->

        </div><!-- /panels wrapper -->
      </div><!-- /settings-content -->
    </div><!-- /page-content -->
  </div><!-- /main-area -->
</div><!-- /app-shell -->

<!-- Toast -->
<div class="toast-bar" id="toastBar">
  <i class="bi bi-check-circle-fill" id="toastIcon"></i>
  <span id="toastMsg">Saved!</span>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../js/admin/admin_sidebarcount.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script src="../js/admin/admin_settings.js"></script>
</body>
</html>