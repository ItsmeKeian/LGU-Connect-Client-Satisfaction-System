<?php
require "../php/auth_check.php";
requireDeptUser();
require "../php/dbconnect.php";

$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));
$dept_code    = CURRENT_DEPT;

$deptStmt = $conn->prepare("SELECT * FROM departments WHERE code = ? LIMIT 1");
$deptStmt->execute([$dept_code]);
$deptInfo = $deptStmt->fetch(PDO::FETCH_ASSOC);

$statsStmt = $conn->prepare("
    SELECT COUNT(*) AS total,
           ROUND(AVG(rating),2) AS avg_rating,
           ROUND(SUM(CASE WHEN rating>=4 THEN 1 ELSE 0 END)*100.0/NULLIF(COUNT(*),0),1) AS satisfaction_rate,
           SUM(CASE WHEN MONTH(submitted_at)=MONTH(NOW()) AND YEAR(submitted_at)=YEAR(NOW()) THEN 1 ELSE 0 END) AS this_month
    FROM feedback WHERE department_code = ?
");
$statsStmt->execute([$dept_code]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// ── Export logs from DB ──
$logsStmt = $conn->prepare("
    SELECT export_type, export_format, date_from, date_to, record_count,
           DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS exported_at
    FROM export_logs
    WHERE dept_code = ? AND exported_by = ?
    ORDER BY created_at DESC LIMIT 20
");
$logsStmt->execute([$dept_code, CURRENT_USER]);
$exportLogs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Export Data | <?= htmlspecialchars($deptInfo['name'] ?? 'Department') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css"/>
<style>
.sb-dept-badge{margin:8px 10px 4px;background:rgba(139,26,26,.35);border:1px solid rgba(139,26,26,.5);border-radius:8px;padding:9px 12px;display:flex;align-items:center;gap:9px}
.sb-dept-badge-icon{width:28px;height:28px;background:#8B1A1A;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;flex-shrink:0}
.sb-dept-badge-name{font-size:11px;font-weight:600;color:#fff;line-height:1.3}
.sb-dept-badge-code{font-size:10px;color:rgba(255,255,255,.45);margin-top:1px}
:root{--red:#8B1A1A;--red-dark:#6e1414;--red-light:#fdf0f0;--red-border:#e8c4c4;}
.export-content{padding:24px;}
.section-label{font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;display:flex;align-items:center;gap:8px;}
.section-label i{color:var(--red);}
.section-label::after{content:'';flex:1;height:1px;background:#efefef;}
.stats-bar{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;}
@media(max-width:800px){.stats-bar{grid-template-columns:repeat(2,1fr);}}
.stats-box{background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:14px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 1px 4px rgba(0,0,0,.04);}
.stats-box i{font-size:22px;color:var(--red);opacity:.7;flex-shrink:0;}
.stats-box .sv{font-size:22px;font-weight:700;color:#1a1a1a;line-height:1;}
.stats-box .sl{font-size:11px;color:#999;margin-top:3px;}
.filters-panel{background:#fff;border-radius:12px;border:1px solid #e8e8e8;padding:18px 20px;margin-bottom:24px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px;}
.filter-field{display:flex;flex-direction:column;gap:6px;}
.filter-field label{font-size:11px;font-weight:600;color:#777;text-transform:uppercase;letter-spacing:.05em;}
.filter-field select,.filter-field input[type=date]{padding:8px 12px;font-size:13px;border:1px solid #ddd;border-radius:7px;background:#fafafa;color:#333;min-width:150px;font-family:inherit;}
.filter-field select:focus,.filter-field input:focus{outline:none;border-color:var(--red);box-shadow:0 0 0 3px rgba(139,26,26,.08);}
.locked-badge{display:flex;align-items:center;gap:7px;background:var(--red-light);border:1px solid var(--red-border);border-radius:8px;padding:8px 14px;font-size:12px;color:var(--red);font-weight:600;margin-left:auto;}
.export-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px;}
.export-card{background:#fff;border-radius:12px;border:1px solid #e8e8e8;overflow:hidden;transition:box-shadow .2s,border-color .2s;}
.export-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);border-color:var(--red-border);}
.export-card-header{padding:16px 18px 12px;display:flex;align-items:flex-start;gap:12px;}
.export-card-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:21px;flex-shrink:0;}
.export-card-icon.red{background:#fff0f0;color:var(--red);}
.export-card-icon.blue{background:#eef5ff;color:#1a6fbf;}
.export-card-icon.green{background:#eef8f0;color:#1e7c3b;}
.export-card-icon.gold{background:#fff8ee;color:#b06c10;}
.export-card-info h3{font-size:14px;font-weight:700;color:#1a1a1a;margin:0 0 4px;}
.export-card-info p{font-size:12px;color:#888;margin:0;line-height:1.5;}
.col-preview{padding:0 18px 12px;display:flex;flex-wrap:wrap;gap:5px;}
.col-tag{background:#f5f5f5;color:#666;font-size:10.5px;padding:2px 8px;border-radius:10px;border:1px solid #ececec;}
.col-tag.hl{background:var(--red-light);color:var(--red);border-color:var(--red-border);}
.export-card-footer{padding:12px 18px;border-top:1px solid #f5f5f5;display:flex;gap:8px;}
.btn-ex{flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;border:none;transition:all .18s;}
.btn-ex.csv{background:#f0f9f0;color:#1e7c3b;border:1px solid #c8e6c9;}
.btn-ex.csv:hover{background:#1e7c3b;color:#fff;}
.btn-ex.excel{background:#e8f5e9;color:#155724;border:1px solid #a5d6a7;}
.btn-ex.excel:hover{background:#155724;color:#fff;}
.export-log{background:#fff;border-radius:12px;border:1px solid #e8e8e8;overflow:hidden;}
.export-log-header{padding:14px 18px;border-bottom:1px solid #f5f5f5;font-size:13.5px;font-weight:600;color:#1a1a1a;display:flex;align-items:center;gap:7px;}
.export-log-header i{color:var(--red);}
.log-list{max-height:220px;overflow-y:auto;}
.log-item{display:flex;align-items:center;gap:12px;padding:11px 18px;border-bottom:1px solid #fafafa;font-size:12.5px;}
.log-item:last-child{border-bottom:none;}
.log-item:hover{background:#fafafa;}
.log-icon{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.log-icon.csv{background:#f0f9f0;color:#1e7c3b;}
.log-icon.excel{background:#e8f5e9;color:#155724;}
.log-name{flex:1;color:#333;font-weight:500;}
.log-meta{font-size:11px;color:#aaa;white-space:nowrap;}
.log-badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;margin-right:4px;}
.log-badge.csv{background:#f0f9f0;color:#1e7c3b;}
.log-badge.excel{background:#e8f5e9;color:#155724;}
.log-empty{padding:28px;text-align:center;color:#bbb;font-size:13px;}
.log-empty i{font-size:28px;display:block;margin-bottom:8px;opacity:.4;}
</style>
</head>
<body>
<div class="app-shell">
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
    <div class="sb-dept-badge">
      <div class="sb-dept-badge-icon"><i class="bi bi-building"></i></div>
      <div>
        <div class="sb-dept-badge-name"><?= htmlspecialchars($deptInfo['name'] ?? 'Department') ?></div>
        <div class="sb-dept-badge-code"><?= htmlspecialchars($dept_code) ?></div>
      </div>
    </div>
    <div class="sb-role">
      <div class="role-dot"></div>
      <div>
        <div class="role-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
        <div class="role-sub">Department User</div>
      </div>
    </div>
    <div class="sb-section">My Department</div>
    <ul class="sb-nav">
      <li><a href="dept_dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span> My Dashboard</a></li>
      <li><a href="dept_feedback.php"><span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> Feedback Inbox <span class="nav-badge" id="sbFeedbackCount">0</span></a></li>
      <li><a href="dept_qrcode.php"><span class="nav-icon"><i class="bi bi-qr-code"></i></span> My QR Code</a></li>
    </ul>
    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="dept_csmr.php"><span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> Generate CSMR</a></li>
      <li><a href="dept_export.php" class="active"><span class="nav-icon"><i class="bi bi-download"></i></span> Export Data</a></li>
    </ul>
    <div class="sb-footer">
      <a href="../php/logout.php" onclick="return confirm('Are you sure you want to sign out?')">
        <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span> Sign Out
      </a>
    </div>
  </aside>

  <div class="main-area">
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        Export Data
        <span class="tb-subtitle"><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?> Only</span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        <button class="tb-btn primary" onclick="location.href='dept_csmr.php'">
          <i class="bi bi-file-earmark-text"></i> Generate CSMR
        </button>
        <div class="tb-avatar" id="topbarAvatar" onclick="toggleAvatarDropdown(event)">
          <?= $avatarLetter ?>
          <div class="avatar-dropdown" id="avatarDropdown">
            <div class="av-header">
              <div class="av-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
              <div class="av-role">Department User</div>
              <div style="font-size:.68rem;color:#888;margin-top:1px"><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></div>
            </div>
            <div class="av-menu">
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
        <span class="live-text">Live &nbsp;&middot;&nbsp; Export feedback data for <strong><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></strong> only</span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <div class="export-content">

        <!-- Stats -->
        <div class="stats-bar">
          <div class="stats-box">
            <i class="bi bi-clipboard-data"></i>
            <div><div class="sv"><?= number_format($stats['total']??0) ?></div><div class="sl">Total Feedback Records</div></div>
          </div>
          <div class="stats-box">
            <i class="bi bi-star-fill"></i>
            <div><div class="sv"><?= number_format((float)($stats['avg_rating']??0),2) ?></div><div class="sl">Avg Rating (All Time)</div></div>
          </div>
          <div class="stats-box">
            <i class="bi bi-calendar-check"></i>
            <div><div class="sv"><?= number_format($stats['this_month']??0) ?></div><div class="sl">This Month's Records</div></div>
          </div>
          <div class="stats-box">
            <i class="bi bi-emoji-smile"></i>
            <div><div class="sv"><?= ($stats['satisfaction_rate']??0)>0 ? $stats['satisfaction_rate'].'%' : '—' ?></div><div class="sl">Satisfaction Rate</div></div>
          </div>
        </div>

        <!-- Filters -->
        <div class="section-label"><i class="bi bi-funnel"></i> Export Filters</div>
        <div class="filters-panel">
          <div class="filter-field">
            <label>Date From</label>
            <input type="date" id="filterDateFrom"/>
          </div>
          <div class="filter-field">
            <label>Date To</label>
            <input type="date" id="filterDateTo"/>
          </div>
          <div class="filter-field">
            <label>Quick Range</label>
            <select id="quickRange" onchange="applyQuickRange()">
              <option value="this_month" selected>This Month</option>
              <option value="last_month">Last Month</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
              <option value="all_time">All Time</option>
              <option value="custom">Custom</option>
            </select>
          </div>
          <div class="filter-field">
            <label>Rating Filter</label>
            <select id="filterRating">
              <option value="">All Ratings</option>
              <option value="5">★★★★★ (5)</option>
              <option value="4">★★★★☆ (4)</option>
              <option value="3">★★★☆☆ (3)</option>
              <option value="2">★★☆☆☆ (2)</option>
              <option value="1">★☆☆☆☆ (1)</option>
            </select>
          </div>
          <div class="locked-badge">
            <i class="bi bi-lock-fill"></i>
            <?= htmlspecialchars($dept_code) ?> dept only
          </div>
        </div>

        <!-- Export Cards -->
        <div class="section-label"><i class="bi bi-download"></i> Choose Export Type</div>
        <div class="export-grid">

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon red"><i class="bi bi-clipboard-data-fill"></i></div>
              <div class="export-card-info">
                <h3>Raw Feedback Records</h3>
                <p>All individual feedback with ratings, SQD scores, demographics, and comments.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">Rating</span><span class="col-tag hl">SQD0–SQD8</span>
              <span class="col-tag">Respondent</span><span class="col-tag">Sex</span>
              <span class="col-tag">Age Group</span><span class="col-tag">Comment</span><span class="col-tag">Date</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('feedback','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('feedback','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon blue"><i class="bi bi-bar-chart-steps"></i></div>
              <div class="export-card-info">
                <h3>SQD Scores Report</h3>
                <p>Average scores for all 9 SQD dimensions. For ARTA compliance reporting.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">SQD0–SQD8 Avg</span><span class="col-tag hl">Overall SQD Avg</span>
              <span class="col-tag">Total Responses</span><span class="col-tag">Period</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('sqd','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('sqd','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon green"><i class="bi bi-clipboard2-data-fill"></i></div>
              <div class="export-card-info">
                <h3>Summary Report</h3>
                <p>Aggregated stats — totals, avg rating, satisfaction rate, and rating breakdown.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">Total Responses</span><span class="col-tag hl">Avg Rating</span>
              <span class="col-tag hl">Satisfaction %</span><span class="col-tag">By Rating Level</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('summary','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('summary','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

          <div class="export-card">
            <div class="export-card-header">
              <div class="export-card-icon gold"><i class="bi bi-chat-left-quote-fill"></i></div>
              <div class="export-card-info">
                <h3>Comments &amp; Suggestions</h3>
                <p>All citizen comments and suggestions — useful for qualitative review.</p>
              </div>
            </div>
            <div class="col-preview">
              <span class="col-tag hl">Comment</span><span class="col-tag hl">Suggestions</span>
              <span class="col-tag">Rating</span><span class="col-tag">Respondent</span><span class="col-tag">Date</span>
            </div>
            <div class="export-card-footer">
              <button class="btn-ex csv" onclick="doExport('comments','csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
              <button class="btn-ex excel" onclick="doExport('comments','excel')"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
            </div>
          </div>

        </div>

        <!-- Export History -->
        <div class="section-label"><i class="bi bi-clock-history"></i> Export History
          <span style="font-weight:400;color:#bbb;font-size:10px;text-transform:none;letter-spacing:0">(this session)</span>
        </div>
        <div class="export-log">
          <div class="export-log-header"><i class="bi bi-clock-history"></i> Recent Exports</div>
          <div class="log-list" id="exportLog">
            <?php if (empty($exportLogs)): ?>
            <div class="log-empty">
              <i class="bi bi-download"></i>
              No exports yet for your department.
            </div>
            <?php else: ?>
            <?php
            $typeLabels = ['feedback'=>'Raw Feedback Records','sqd'=>'SQD Scores Report','summary'=>'Summary Report','comments'=>'Comments & Suggestions','departments'=>'Departments Report'];
            $typeIcons  = ['feedback'=>'bi-clipboard-data-fill','sqd'=>'bi-bar-chart-steps','summary'=>'bi-clipboard2-data-fill','comments'=>'bi-chat-left-quote-fill','departments'=>'bi-building'];
            foreach ($exportLogs as $log):
              $lbl  = $typeLabels[$log['export_type']] ?? $log['export_type'];
              $icon = $typeIcons[$log['export_type']]  ?? 'bi-download';
              $fmt  = $log['export_format'];
              $ext  = $fmt === 'excel' ? '.xlsx' : '.csv';
              $fmtDate = fn($d) => date('M j, Y', strtotime($d));
            ?>
            <div class="log-item">
              <div class="log-icon <?= $fmt ?>"><i class="bi <?= $icon ?>"></i></div>
              <div class="log-name">
                <?= htmlspecialchars($lbl) ?>
                <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:6px">
                  <?= $fmtDate($log['date_from']) ?> – <?= $fmtDate($log['date_to']) ?>
                  · <?= number_format($log['record_count']) ?> records
                </span>
              </div>
              <span class="log-badge <?= $fmt ?>"><?= $ext ?></span>
              <div class="log-meta"><?= htmlspecialchars($log['exported_at']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script>
const DEPT_CODE = <?= json_encode($dept_code) ?>;


document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
document.getElementById('menuToggle')?.addEventListener('click',()=>
  document.getElementById('sidebar').classList.toggle('sb-open'));
function toggleAvatarDropdown(e){e.stopPropagation();document.getElementById('avatarDropdown').classList.toggle('show');}
document.addEventListener('click',()=>document.getElementById('avatarDropdown')?.classList.remove('show'));

$.get('../php/get/get_feedback.php',{dept:DEPT_CODE,per_page:1,page:1},function(res){
  if(res.success) $('#sbFeedbackCount').text(res.summary.total||0);
});

// Default dates
const now=new Date();
document.getElementById('filterDateFrom').value=new Date(now.getFullYear(),now.getMonth(),1).toISOString().split('T')[0];
document.getElementById('filterDateTo').value=new Date(now.getFullYear(),now.getMonth()+1,0).toISOString().split('T')[0];

function applyQuickRange(){
  const val=document.getElementById('quickRange').value;
  const n=new Date(); let from,to;
  switch(val){
    case 'this_month':   from=new Date(n.getFullYear(),n.getMonth(),1);to=new Date(n.getFullYear(),n.getMonth()+1,0);break;
    case 'last_month':   from=new Date(n.getFullYear(),n.getMonth()-1,1);to=new Date(n.getFullYear(),n.getMonth(),0);break;
    case 'this_quarter': {const q=Math.floor(n.getMonth()/3);from=new Date(n.getFullYear(),q*3,1);to=new Date(n.getFullYear(),q*3+3,0);break;}
    case 'this_year':    from=new Date(n.getFullYear(),0,1);to=new Date(n.getFullYear(),11,31);break;
    case 'all_time':     from=new Date('2000-01-01');to=new Date();break;
    case 'custom':       return;
    default: return;
  }
  document.getElementById('filterDateFrom').value=from.toISOString().split('T')[0];
  document.getElementById('filterDateTo').value=to.toISOString().split('T')[0];
}

function doExport(type,format){
  const from=document.getElementById('filterDateFrom').value;
  const to=document.getElementById('filterDateTo').value;
  const rating=document.getElementById('filterRating').value;
  if(!from||!to){alert('Please select a date range.');return;}

  const params=new URLSearchParams({
    type,format,
    dept_id:   DEPT_CODE,  // ✅ always locked
    date_from: from,
    date_to:   to,
    rating:    rating,
  });
  window.location.href='../php/get/get_export_data.php?'+params.toString();

  const labels={feedback:'Raw Feedback Records',sqd:'SQD Scores Report',summary:'Summary Report',comments:'Comments & Suggestions'};
  const icons={feedback:'bi-clipboard-data-fill',sqd:'bi-bar-chart-steps',summary:'bi-clipboard2-data-fill',comments:'bi-chat-left-quote-fill'};

  exportHistory.unshift({
    type,format,
    label: labels[type]||type,
    icon:  icons[type]||'bi-download',
    from,to,
    time:  new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'}),
  });
  renderLog();
}

function renderLog(){
  if(!exportHistory.length) return;
  const fmt=d=>new Date(d).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
  document.getElementById('exportLog').innerHTML=exportHistory.map(e=>`
    <div class="log-item">
      <div class="log-icon ${e.format}"><i class="bi ${e.icon}"></i></div>
      <div class="log-name">${e.label}
        <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:6px">${fmt(e.from)} – ${fmt(e.to)}</span>
      </div>
      <span class="log-badge ${e.format}">.${e.format==='excel'?'xlsx':'csv'}</span>
      <div class="log-meta">${e.time}</div>
    </div>`).join('');
}
</script>
</body>
</html>