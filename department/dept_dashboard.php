<?php
require "../php/auth_check.php";
requireDeptUser();

require "../php/dbconnect.php";

$avatarLetter = strtoupper(substr(CURRENT_USER, 0, 1));
$dept_code    = CURRENT_DEPT;

// Get department info
$deptStmt = $conn->prepare("SELECT * FROM departments WHERE code = ? LIMIT 1");
$deptStmt->execute([$dept_code]);
$deptInfo = $deptStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($deptInfo['name'] ?? 'Department') ?> | LGU-Connect</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css"/>
<style>
.sb-dept-badge {
  margin: 8px 10px 4px;
  background: rgba(139,26,26,.35);
  border: 1px solid rgba(139,26,26,.5);
  border-radius: 8px;
  padding: 9px 12px;
  display: flex;
  align-items: center;
  gap: 9px;
}
.sb-dept-badge-icon {
  width: 28px; height: 28px;
  background: #8B1A1A;
  border-radius: 6px;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; color: #fff;
  flex-shrink: 0;
}
.sb-dept-badge-name { font-size: 11px; font-weight: 600; color: #fff; line-height: 1.3; }
.sb-dept-badge-code { font-size: 10px; color: rgba(255,255,255,.45); margin-top: 1px; }
</style>
</head>
<body>
<div class="app-shell">

  <!-- ══ SIDEBAR ══ -->
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

    <!-- Department badge -->
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
      <li><a href="dept_dashboard.php" class="active">
        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span> My Dashboard
      </a></li>
      <li><a href="dept_feedback.php">
        <span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> Feedback Inbox
        <span class="nav-badge" id="sbFeedbackCount">0</span>
      </a></li>
      <li><a href="dept_qrcode.php">
        <span class="nav-icon"><i class="bi bi-qr-code"></i></span> My QR Code
      </a></li>
    </ul>

    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="dept_csmr.php">
        <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> Generate CSMR
      </a></li>
      <li><a href="dept_analytics.php">
        <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> My Analytics
      </a></li>
      <li><a href="dept_export.php">
        <span class="nav-icon"><i class="bi bi-download"></i></span> Export Data
      </a></li>
    </ul>

    <div class="sb-footer">
      <a href="../php/logout.php" onclick="return confirm('Are you sure you want to sign out?')">
        <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span> Sign Out
      </a>
    </div>
  </aside>

  <!-- ══ MAIN AREA ══ -->
  <div class="main-area">

    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title">
        My Dashboard
        <span class="tb-subtitle"><?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?></span>
      </div>
      <div class="topbar-actions">
        <button class="tb-btn" id="refreshBtn">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="tb-btn primary" onclick="location.href='dept_csmr.php'">
          <i class="bi bi-file-earmark-text"></i> Generate CSMR
        </button>
        <div class="tb-avatar" id="topbarAvatar" onclick="toggleAvatarDropdown(event)">
          <?= $avatarLetter ?>
          <div class="avatar-dropdown" id="avatarDropdown">
            <div class="av-header">
              <div class="av-name"><?= htmlspecialchars(CURRENT_USER) ?></div>
              <div class="av-role">Department User</div>
              <div style="font-size:.68rem;color:#888;margin-top:1px">
                <?= htmlspecialchars($deptInfo['name'] ?? $dept_code) ?>
              </div>
            </div>
            <div class="av-menu">
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

      <div class="live-bar">
        <div class="live-dot"></div>
        <span class="live-text">
          Live &nbsp;&middot;&nbsp; Last updated: <span id="lastUpdated">just now</span>
        </span>
        <span class="live-date" id="todayDate"></span>
      </div>

      <!-- ── KPI Cards ── -->
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
            <div class="sc-label">Average Rating</div>
            <div class="sc-change sc-neu" id="statAvgChange">Loading...</div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-grn">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-emoji-smile"></i></div>
            <div class="sc-value" id="statSat">—</div>
            <div class="sc-label">Satisfaction Rate</div>
            <div class="sc-change sc-neu" id="statSatChange">Loading...</div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="stat-card sc-blu">
            <div class="sc-top-bar"></div>
            <div class="sc-icon"><i class="bi bi-calendar-check"></i></div>
            <div class="sc-value" id="statMonth">—</div>
            <div class="sc-label">This Month</div>
            <div class="sc-change sc-neu" id="statMonthChange">Loading...</div>
          </div>
        </div>
      </div>

      <!-- ── Charts Row ── -->
      <div class="row g-3 mb-3">

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Rating Distribution</div>
                <div class="card-sub-custom">Count per rating level</div>
              </div>
              <span class="badge" style="background:#fdf0f0;color:#B5121B;font-size:.63rem;padding:4px 10px;border-radius:20px">All Time</span>
            </div>
            <div class="card-body p-3">
              <canvas id="chartRating" height="200"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Satisfaction Trend</div>
                <div class="card-sub-custom">Monthly avg rating — last 6 months</div>
              </div>
              <span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:.63rem;padding:4px 10px;border-radius:20px">6 Months</span>
            </div>
            <div class="card-body p-3">
              <canvas id="chartTrend" height="200"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">SQD Scores</div>
                <div class="card-sub-custom">Average score per dimension (ARTA standard)</div>
              </div>
              <span class="badge" style="background:#fdf8e6;color:#C8991A;font-size:.63rem;padding:4px 10px;border-radius:20px">ARTA SQD</span>
            </div>
            <div class="card-body p-3" id="sqdBody">
              <div style="text-align:center;padding:40px;color:#aaa;font-size:13px">Loading SQD scores…</div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
            <div class="card-header-custom">
              <div>
                <div class="card-title-custom">Feedback Volume</div>
                <div class="card-sub-custom">Daily submissions — last 14 days</div>
              </div>
              <span class="badge" style="background:#e3f2fd;color:#1565c0;font-size:.63rem;padding:4px 10px;border-radius:20px">2 Weeks</span>
            </div>
            <div class="card-body p-3">
              <canvas id="chartVolume" height="200"></canvas>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Bottom Row ── -->
      <div class="row g-3">

        <div class="col-lg-8">
          <div class="card border-0 shadow-sm h-100" style="border-radius:12px;overflow:hidden">
            <div class="card-header-custom">
              <div class="card-title-custom">Recent Feedback</div>
              <button class="tb-btn" onclick="location.href='dept_feedback.php'">
                View All <i class="bi bi-arrow-right"></i>
              </button>
            </div>
            <ul class="feedback-list" id="recentFeedbackList">
              <li class="feedback-item">
                <div style="padding:20px;text-align:center;color:#6b6864;width:100%">
                  <div class="spinner-border spinner-border-sm text-danger me-2"></div>
                  Loading recent feedback…
                </div>
              </li>
            </ul>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="d-flex flex-column gap-3 h-100">

            <!-- This Month -->
            <div class="card border-0 shadow-sm" style="border-radius:12px">
              <div class="card-body p-3">
                <div class="card-title-custom mb-3">This Month</div>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#B5121B" id="miniResponses">—</div>
                      <div class="ms-label">Responses</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#C8991A" id="miniAvg">—</div>
                      <div class="ms-label">Avg Score</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#2e7d32;font-size:.78rem" id="miniSat">—</div>
                      <div class="ms-label">Satisfaction</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mini-stat-box">
                      <div class="ms-val" style="color:#1565c0" id="miniToday">—</div>
                      <div class="ms-label">Today</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm" style="border-radius:12px">
              <div class="card-body p-3">
                <div class="card-title-custom mb-3">Quick Actions</div>
                <div class="d-flex flex-column gap-2">
                  <a href="dept_csmr.php" class="action-btn-custom primary">
                    <i class="bi bi-file-earmark-text"></i> Generate CSMR Report
                  </a>
                  <a href="dept_feedback.php" class="action-btn-custom">
                    <i class="bi bi-clipboard-check"></i> View All Feedback
                  </a>
                  <a href="dept_analytics.php" class="action-btn-custom">
                    <i class="bi bi-bar-chart-line"></i> My Analytics
                  </a>
                  <a href="dept_export.php" class="action-btn-custom">
                    <i class="bi bi-download"></i> Export My Data
                  </a>
                  <a href="dept_qrcode.php" class="action-btn-custom">
                    <i class="bi bi-qr-code"></i> My QR Code
                  </a>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div><!-- /page-content -->
  </div><!-- /main-area -->
</div><!-- /app-shell -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/mobile_toggle.js"></script>
<script>
const DEPT_CODE = <?= json_encode($dept_code) ?>;

let chartRating = null;
let chartTrend  = null;
let chartVolume = null;

// Date
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// Avatar dropdown
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => document.getElementById('avatarDropdown')?.classList.remove('show'));

// Sidebar feedback badge
$.get('../php/get/get_feedback.php', {dept: DEPT_CODE, per_page:1, page:1}, function(res) {
  if (res.success) $('#sbFeedbackCount').text(res.summary.total || 0);
});

// ── Main loader ──
function loadDashboard() {
  $.ajax({
    url: '../php/get/get_dept_dashboard.php',
    method: 'GET',
    data: { dept: DEPT_CODE },
    dataType: 'json',
    success(res) {
      if (!res.success) return;
      const d = res.data;

      // KPIs
      $('#statTotal').text(Number(d.total).toLocaleString());
      $('#statAvg').text(parseFloat(d.avg_rating || 0).toFixed(2));
      $('#statSat').text((d.satisfaction_rate || '0') + '%');
      $('#statMonth').text(Number(d.this_month || 0).toLocaleString());

      $('#statTotalChange').html(`<i class="bi bi-calendar-check" style="font-size:10px"></i> ${d.this_month||0} this month`);
      $('#statAvgChange').html(`<i class="bi bi-star-fill" style="font-size:10px"></i> Out of 5.0`);
      $('#statSatChange').html(`<i class="bi bi-people" style="font-size:10px"></i> ${d.total||0} total responses`);
      $('#statMonthChange').html(`<i class="bi bi-calendar-day" style="font-size:10px"></i> ${d.today||0} today`);

      // Mini stats
      $('#miniResponses').text(Number(d.this_month||0).toLocaleString());
      $('#miniAvg').text(parseFloat(d.avg_rating||0).toFixed(2));
      $('#miniSat').text((d.satisfaction_rate||'0')+'%');
      $('#miniToday').text(Number(d.today||0).toLocaleString());

      renderRatingChart(d.rating_dist);
      renderTrendChart(d.trend);
      renderVolumeChart(d.volume);
      renderSQD(d.sqd);
      renderRecentFeedback(d.recent);

      document.getElementById('lastUpdated').textContent =
        new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'});
    }
  });
}

function renderRatingChart(dist) {
  if (!dist) return;
  const labels = ['Excellent (5)','Good (4)','Average (3)','Poor (2)','Very Poor (1)'];
  const data   = [dist['5']||0,dist['4']||0,dist['3']||0,dist['2']||0,dist['1']||0];
  const colors = ['#1e7c3b','#1565c0','#b06c10','#c0392b','#922b21'];
  if (chartRating) chartRating.destroy();
  chartRating = new Chart(document.getElementById('chartRating'),{
    type:'bar',
    data:{labels,datasets:[{data,backgroundColor:colors,borderRadius:6,borderSkipped:false}]},
    options:{responsive:true,plugins:{legend:{display:false}},
      scales:{y:{min:0,ticks:{stepSize:1,precision:0},grid:{color:'rgba(0,0,0,.05)'}},x:{grid:{display:false}}}}
  });
}

function renderTrendChart(trend) {
  if (!trend||!trend.length) return;
  const labels = trend.map(t=>t.month_label);
  const ratings= trend.map(t=>parseFloat(t.avg_rating)||0);
  const counts = trend.map(t=>parseInt(t.total)||0);
  if (chartTrend) chartTrend.destroy();
  chartTrend = new Chart(document.getElementById('chartTrend'),{
    type:'line',
    data:{labels,datasets:[
      {label:'Avg Rating',data:ratings,borderColor:'#B5121B',backgroundColor:'rgba(181,18,27,.08)',
       borderWidth:2,tension:.4,fill:true,pointBackgroundColor:'#B5121B',pointRadius:4,yAxisID:'y'},
      {label:'Responses',data:counts,borderColor:'#1565c0',backgroundColor:'transparent',
       borderWidth:1.5,borderDash:[4,3],tension:.4,pointRadius:3,pointBackgroundColor:'#1565c0',yAxisID:'y2'}
    ]},
    options:{responsive:true,interaction:{mode:'index',intersect:false},
      plugins:{legend:{labels:{font:{size:11},boxWidth:12}}},
      scales:{y:{min:0,max:5,grid:{color:'rgba(0,0,0,.05)'},title:{display:true,text:'Avg Rating',font:{size:10}}},
              y2:{position:'right',grid:{drawOnChartArea:false},title:{display:true,text:'Responses',font:{size:10}},ticks:{stepSize:1,precision:0}},
              x:{grid:{display:false}}}}
  });
}

function renderVolumeChart(volume) {
  if (!volume) return;
  if (chartVolume) chartVolume.destroy();
  chartVolume = new Chart(document.getElementById('chartVolume'),{
    type:'bar',
    data:{labels:volume.map(v=>v.day_label),datasets:[{
      label:'Submissions',data:volume.map(v=>parseInt(v.total)||0),
      backgroundColor:'rgba(181,18,27,.15)',borderColor:'#B5121B',
      borderWidth:1.5,borderRadius:4,borderSkipped:false}]},
    options:{responsive:true,plugins:{legend:{display:false}},
      scales:{y:{min:0,ticks:{stepSize:1,precision:0},grid:{color:'rgba(0,0,0,.05)'}},x:{grid:{display:false}}}}
  });
}

function renderSQD(sqd) {
  if (!sqd) return;
  const items = [
    {key:'sqd0',label:'SQD0 — Citizens Charter'},
    {key:'sqd1',label:'SQD1 — Service Speed'},
    {key:'sqd2',label:'SQD2 — Transaction Time'},
    {key:'sqd3',label:'SQD3 — Staff Courtesy'},
    {key:'sqd4',label:'SQD4 — No Extra Fees'},
    {key:'sqd5',label:'SQD5 — Process Compliance'},
    {key:'sqd6',label:'SQD6 — Service Quality'},
    {key:'sqd7',label:'SQD7 — Timely Delivery'},
    {key:'sqd8',label:'SQD8 — Overall Satisfaction'},
  ];
  let html = '<div style="display:flex;flex-direction:column;gap:8px">';
  items.forEach(item => {
    const val = parseFloat(sqd[item.key]||0);
    const pct = (val/5*100).toFixed(1);
    const col = val>=4?'#1e7c3b':val>=3?'#1565c0':'#c0392b';
    html += `<div style="display:flex;align-items:center;gap:8px">
      <div style="font-size:11px;color:#666;width:160px;flex-shrink:0">${item.label}</div>
      <div style="flex:1;height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden">
        <div style="width:${pct}%;height:100%;background:${col};border-radius:4px;transition:width .8s ease"></div>
      </div>
      <div style="font-size:11.5px;font-weight:600;color:${col};width:36px;text-align:right;flex-shrink:0">
        ${val>0?val.toFixed(2):'—'}
      </div></div>`;
  });
  html += '</div>';
  document.getElementById('sqdBody').innerHTML = html;
}

function renderRecentFeedback(feedbacks) {
  if (!feedbacks||!feedbacks.length) {
    $('#recentFeedbackList').html('<li style="padding:20px;text-align:center;color:#9a9390">No feedback yet.</li>');
    return;
  }
  const stars = r => '★'.repeat(Math.round(r))+'☆'.repeat(5-Math.round(r));
  let items = '';
  feedbacks.forEach(f => {
    const type    = (f.respondent_type||'citizen').replace('_',' ');
    const comment = f.comment
      ? escHtml(f.comment).substring(0,80)+(f.comment.length>80?'…':'')
      : '<em style="color:#bbb">No comment provided.</em>';
    items += `
      <li class="feedback-item">
        <div class="fb-avatar" style="background:linear-gradient(135deg,#B5121B,#8B0000)">
          ${(f.respondent_type||'C')[0].toUpperCase()}
        </div>
        <div class="fb-body">
          <div class="fb-dept" style="color:#B5121B;text-transform:capitalize">${escHtml(type)}</div>
          <div class="fb-comment">${comment}</div>
          <div class="fb-meta">
            <span style="color:#F0C030">${stars(f.rating)}</span>
            <span>${f.rating}/5</span>
            <span>${escHtml(f.submitted_at)}</span>
          </div>
        </div>
      </li>`;
  });
  $('#recentFeedbackList').html(items);
}

function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

$(document).ready(function() {
  loadDashboard();
  $('#refreshBtn').on('click', loadDashboard);
  setInterval(loadDashboard, 30000);
});
</script>
</body>
</html>