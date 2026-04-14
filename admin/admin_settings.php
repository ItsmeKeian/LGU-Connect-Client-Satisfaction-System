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
<title>Settings | LGU-Connect</title>
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<style>
:root {
  --red-main: #8B1A1A;
  --red-light: #f8f0f0;
  --red-border: #e8c4c4;
  --card-radius: 12px;
}

.settings-content { padding: 24px; display: grid; grid-template-columns: 220px 1fr; gap: 24px; align-items: start; }
@media(max-width:900px) { .settings-content { grid-template-columns: 1fr; } }

/* ── Settings Nav ── */
.settings-nav {
  background: #fff;
  border-radius: var(--card-radius);
  border: 1px solid #e8e8e8;
  overflow: hidden;
  position: sticky;
  top: 24px;
}
.settings-nav-header {
  padding: 14px 16px;
  font-size: 11px;
  font-weight: 700;
  color: #999;
  text-transform: uppercase;
  letter-spacing: .08em;
  border-bottom: 1px solid #f5f5f5;
}
.settings-nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  font-size: 13.5px;
  color: #444;
  cursor: pointer;
  border-bottom: 1px solid #fafafa;
  transition: all .15s;
  text-decoration: none;
  border-left: 3px solid transparent;
}
.settings-nav-item:hover { background: #fafafa; color: var(--red-main); }
.settings-nav-item.active { background: var(--red-light); color: var(--red-main); border-left-color: var(--red-main); font-weight: 600; }
.settings-nav-item i { font-size: 15px; width: 18px; text-align: center; }
.settings-nav-item:last-child { border-bottom: none; }

/* ── Settings Panels ── */
.settings-panel { display: none; flex-direction: column; gap: 20px; }
.settings-panel.active { display: flex; }

.settings-card {
  background: #fff;
  border-radius: var(--card-radius);
  border: 1px solid #e8e8e8;
  overflow: hidden;
}
.settings-card-header {
  padding: 16px 22px;
  border-bottom: 1px solid #f5f5f5;
  display: flex;
  align-items: center;
  gap: 10px;
}
.settings-card-header i { color: var(--red-main); font-size: 16px; }
.settings-card-header h3 { font-size: 14px; font-weight: 600; margin: 0; color: #1a1a1a; }
.settings-card-header p  { font-size: 12px; color: #aaa; margin: 2px 0 0; }
.settings-card-body { padding: 22px; }

/* ── Form fields ── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.form-grid.single { grid-template-columns: 1fr; }
@media(max-width:700px) { .form-grid { grid-template-columns: 1fr; } }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label {
  font-size: 12px;
  font-weight: 600;
  color: #555;
  text-transform: uppercase;
  letter-spacing: .05em;
}
.form-group input[type=text],
.form-group input[type=email],
.form-group input[type=tel],
.form-group input[type=url],
.form-group input[type=number],
.form-group select,
.form-group textarea {
  padding: 9px 12px;
  font-size: 13.5px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fafafa;
  color: #222;
  transition: border-color .2s, box-shadow .2s;
  font-family: inherit;
}
.form-group textarea { resize: vertical; min-height: 80px; }
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--red-main);
  box-shadow: 0 0 0 3px rgba(139,26,26,.08);
  background: #fff;
}
.form-group .field-hint { font-size: 11px; color: #aaa; margin-top: 2px; }

/* ── Toggle switch ── */
.toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 0;
  border-bottom: 1px solid #f5f5f5;
}
.toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
.toggle-row:first-child { padding-top: 0; }
.toggle-info h4 { font-size: 13.5px; font-weight: 600; color: #1a1a1a; margin: 0 0 3px; }
.toggle-info p  { font-size: 12px; color: #888; margin: 0; }
.toggle-switch {
  position: relative;
  width: 44px; height: 24px;
  flex-shrink: 0;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
  position: absolute;
  inset: 0;
  background: #ccc;
  border-radius: 24px;
  cursor: pointer;
  transition: background .2s;
}
.toggle-slider:before {
  content: '';
  position: absolute;
  width: 18px; height: 18px;
  left: 3px; top: 3px;
  background: #fff;
  border-radius: 50%;
  transition: transform .2s;
  box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.toggle-switch input:checked + .toggle-slider { background: var(--red-main); }
.toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }

/* ── Save button ── */
.btn-save {
  background: var(--red-main);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 11px 24px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background .2s;
}
.btn-save:hover { background: #6e1414; }
.btn-save.loading { opacity: .7; pointer-events: none; }
.btn-save-wrap { display: flex; justify-content: flex-end; margin-top: 6px; }

/* ── Success / error toast ── */
.toast-bar {
  position: fixed;
  bottom: 28px;
  right: 28px;
  padding: 12px 20px;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,.15);
  z-index: 9999;
  transform: translateY(80px);
  opacity: 0;
  transition: transform .3s, opacity .3s;
}
.toast-bar.show { transform: translateY(0); opacity: 1; }
.toast-bar.success { background: #1e7c3b; color: #fff; }
.toast-bar.error   { background: #c0392b; color: #fff; }

/* ── DB Stats cards ── */
.db-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 12px;
  margin-bottom: 20px;
}
.db-stat-box {
  background: #fafafa;
  border: 1px solid #efefef;
  border-radius: 8px;
  padding: 14px;
  text-align: center;
}
.db-stat-box .dsv { font-size: 22px; font-weight: 700; color: var(--red-main); line-height: 1; }
.db-stat-box .dsl { font-size: 11px; color: #aaa; margin-top: 4px; }

/* ── Maintenance actions ── */
.maint-action {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 0;
  border-bottom: 1px solid #f5f5f5;
  gap: 16px;
  flex-wrap: wrap;
}
.maint-action:last-child { border-bottom: none; padding-bottom: 0; }
.maint-action:first-child { padding-top: 0; }
.maint-info h4 { font-size: 13.5px; font-weight: 600; color: #1a1a1a; margin: 0 0 3px; }
.maint-info p  { font-size: 12px; color: #888; margin: 0; }
.btn-maint {
  padding: 8px 18px;
  border-radius: 7px;
  font-size: 12.5px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all .18s;
  white-space: nowrap;
  flex-shrink: 0;
}
.btn-maint.danger  { background: #fff0f0; color: #c0392b; border: 1px solid #fcc; }
.btn-maint.danger:hover  { background: #c0392b; color: #fff; }
.btn-maint.warning { background: #fff8ee; color: #b06c10; border: 1px solid #fde8c0; }
.btn-maint.warning:hover { background: #b06c10; color: #fff; }
.btn-maint.neutral { background: #f5f5f5; color: #444; border: 1px solid #e0e0e0; }
.btn-maint.neutral:hover { background: #333; color: #fff; }

/* ── System info table ── */
.sysinfo-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sysinfo-table td { padding: 9px 12px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
.sysinfo-table td:first-child { color: #888; font-weight: 500; width: 40%; }
.sysinfo-table td:last-child  { color: #333; font-weight: 600; }
.sysinfo-table tr:last-child td { border-bottom: none; }

/* ── Months select inline ── */
.inline-row { display: flex; align-items: center; gap: 10px; }
.inline-row select {
  padding: 7px 10px;
  font-size: 12.5px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: #fafafa;
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
      <li><a href="admin_allfeedback.php"><span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> All Feedback <span class="nav-badge" id="sbFeedbackCount">0</span></a></li>
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
<script>

// ── Date ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Panel switching ──
function showPanel(name) {
  document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('panel-' + name).classList.add('active');
  event.currentTarget.classList.add('active');

  // Load DB stats when maintenance panel is opened
  if (name === 'maintenance') loadDBStats();
}

// ── Load settings from DB ──
$.ajax({
  url: '../php/get/get_settings.php',
  method: 'GET',
  dataType: 'json',
  success(res) {
    if (!res.success) return;
    const s = res.settings;

    // LGU fields
    ['lgu_name','lgu_address','lgu_province','lgu_region',
     'lgu_mayor','lgu_email','lgu_phone','lgu_website'].forEach(key => {
      const el = document.getElementById(key);
      if (el) el.value = s[key] || '';
    });

    // System fields
    const fbOpen = document.getElementById('feedback_open');
    if (fbOpen) fbOpen.checked = s['feedback_open'] === '1';

    ['refresh_interval','max_export_rows','csmr_footer_note'].forEach(key => {
      const el = document.getElementById(key);
      if (el) el.value = s[key] || '';
    });
  }
});

// ── Save LGU Info ──
function saveLGUSettings() {
  const settings = {
    lgu_name:     document.getElementById('lgu_name').value,
    lgu_address:  document.getElementById('lgu_address').value,
    lgu_province: document.getElementById('lgu_province').value,
    lgu_region:   document.getElementById('lgu_region').value,
    lgu_mayor:    document.getElementById('lgu_mayor').value,
  };
  saveSettings('lgu', settings);
}

function saveContactSettings() {
  const settings = {
    lgu_email:   document.getElementById('lgu_email').value,
    lgu_phone:   document.getElementById('lgu_phone').value,
    lgu_website: document.getElementById('lgu_website').value,
  };
  saveSettings('lgu', settings);
}

// ── Save System Preferences ──
function saveSystemSettings() {
  const settings = {
    feedback_open:    document.getElementById('feedback_open').checked ? '1' : '0',
    refresh_interval: document.getElementById('refresh_interval').value,
    max_export_rows:  document.getElementById('max_export_rows').value,
    csmr_footer_note: document.getElementById('csmr_footer_note').value,
  };
  saveSettings('system', settings);
}

// ── Generic save ──
function saveSettings(group, settings) {
  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'save_settings', group, settings },
    success(res) {
      showToast(res.success, res.message || (res.success ? 'Settings saved!' : 'Save failed.'));
    },
    error(xhr) {
      console.error(xhr.responseText);
      showToast(false, 'Server error. Check console.');
    }
  });
}

// ── DB Stats ──
function loadDBStats() {
  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'get_db_stats' },
    success(res) {
      if (!res.success) return;
      const s = res.stats;

      // Row count boxes
      Object.entries(s.rows).forEach(([tbl, cnt]) => {
        const el = document.getElementById('ds-' + tbl);
        if (el) el.textContent = Number(cnt).toLocaleString();
      });
      document.getElementById('ds-dbsize').textContent = Number(s.db_size_kb).toLocaleString();

      // Sysinfo table
      document.getElementById('si-dbname').textContent  = s.db_name       || '—';
      document.getElementById('si-mysql').textContent   = s.mysql_version  || '—';
      document.getElementById('si-php').textContent     = s.php_version    || '—';
      document.getElementById('si-oldest').textContent  = s.feedback_range?.oldest || 'No data';
      document.getElementById('si-newest').textContent  = s.feedback_range?.newest || 'No data';
      document.getElementById('si-total').textContent   = Number(s.feedback_range?.total || 0).toLocaleString();
    }
  });
}

// ── Maintenance: clear old feedback ──
function clearOldFeedback() {
  const months = document.getElementById('clearMonths').value;
  const label  = document.getElementById('clearMonths').selectedOptions[0].text;
  if (!confirm(`Delete all feedback ${label}?\n\nThis action CANNOT be undone.`)) return;

  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'clear_old_feedback', months },
    success(res) {
      showToast(res.success, res.message);
      if (res.success) loadDBStats();
    }
  });
}

// ── Maintenance: clear export logs ──
function clearExportLogs() {
  if (!confirm('Clear all export history logs? This cannot be undone.')) return;
  $.ajax({
    url: '../php/save/save_settings.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'clear_export_logs' },
    success(res) {
      showToast(res.success, res.message);
      if (res.success) loadDBStats();
    }
  });
}

// ── Toast notification ──
let toastTimer = null;
function showToast(success, msg) {
  const bar  = document.getElementById('toastBar');
  const icon = document.getElementById('toastIcon');
  const text = document.getElementById('toastMsg');

  bar.className  = 'toast-bar ' + (success ? 'success' : 'error');
  icon.className = 'bi ' + (success ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill');
  text.textContent = msg;

  bar.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => bar.classList.remove('show'), 3500);
}

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});
</script>
</body>
</html>