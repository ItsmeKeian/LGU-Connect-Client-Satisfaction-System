
// ── Chart instances (kept for destroy/recreate) ──
let chartTrend  = null;
let chartRating = null;
let chartType   = null;
let chartSex    = null;
let chartAge    = null;

// ── Date display ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Load departments ──
$.ajax({
  url: '../php/get/get_departments.php',
  method: 'GET',
  success(res) {
    const depts = Array.isArray(res) ? res : (res.data || res.departments || []);
    const sel   = document.getElementById('filterDept');
    depts.forEach(d => {
      const opt       = document.createElement('option');
      opt.value       = d.code;
      opt.textContent = d.name;
      sel.appendChild(opt);
    });
  }
});

// ── Period chips ──
document.querySelectorAll('.chip[data-period]').forEach(chip => {
  chip.addEventListener('click', () => {
    document.querySelectorAll('.chip[data-period]').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
  });
});

// ── Load analytics ──
function loadAnalytics() {
  const period = document.querySelector('.chip.active')?.dataset.period || 'this_month';
  const dept   = document.getElementById('filterDept').value;

  document.getElementById('spinnerOverlay').classList.add('show');
  document.getElementById('loadBtn').classList.add('loading');

  $.ajax({
    url: '../php/get/get_analytics_data.php',
    method: 'POST',
    dataType: 'json',
    data: { period, dept_id: dept },
    success(res) {
      if (!res.success) { alert('Error: ' + res.message); return; }
      renderKPIs(res.kpi, res.period);
      renderTrendChart(res.trend);
      renderRatingChart(res.kpi);
      renderSQDScores(res.kpi);
      renderDemoCharts(res.by_type, res.by_sex, res.by_age);
      renderDeptTable(res.by_dept);
      renderComments(res.recent_comments);
    },
    error(xhr) {
      console.error('Analytics error:', xhr.responseText);
      alert('Server error. Check console (F12).');
    },
    complete() {
      document.getElementById('spinnerOverlay').classList.remove('show');
      document.getElementById('loadBtn').classList.remove('loading');
    }
  });
}

// ── KPI Cards ──
function renderKPIs(kpi, period) {
  document.getElementById('kpiTotal').textContent      = Number(kpi.total_responses).toLocaleString();
  document.getElementById('kpiSat').textContent        = (kpi.satisfaction_rate || '0.0') + '%';
  document.getElementById('kpiAvg').textContent        = parseFloat(kpi.avg_rating || 0).toFixed(1);
  document.getElementById('kpiDepts').textContent      = kpi.dept_count;
  document.getElementById('kpiPeriod').textContent     = formatDate(period.from) + ' – ' + formatDate(period.to);
  document.getElementById('kpiRatingLabel').textContent = ratingLabel(kpi.avg_rating) + ' · Out of 5.0';
}

// ── Trend Chart (Line) ──
function renderTrendChart(trend) {
  const labels = trend.map(t => {
    const d = new Date(t.day);
    return d.toLocaleDateString('en-PH', { month:'short', day:'numeric' });
  });
  const data   = trend.map(t => parseInt(t.total));
  const avg    = trend.map(t => parseFloat(t.avg_rating));

  if (chartTrend) chartTrend.destroy();
  const ctx = document.getElementById('chartTrend').getContext('2d');
  chartTrend = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Responses',
          data,
          borderColor: '#8B1A1A',
          backgroundColor: 'rgba(139,26,26,.08)',
          borderWidth: 2,
          fill: true,
          tension: .35,
          pointRadius: 3,
          pointBackgroundColor: '#8B1A1A',
          yAxisID: 'y',
        },
        {
          label: 'Avg Rating',
          data: avg,
          borderColor: '#1a6fbf',
          backgroundColor: 'transparent',
          borderWidth: 1.5,
          borderDash: [4,3],
          fill: false,
          tension: .35,
          pointRadius: 2,
          pointBackgroundColor: '#1a6fbf',
          yAxisID: 'y2',
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { labels: { font: { size: 11 }, boxWidth: 12 } } },
      scales: {
        x:  { grid: { color: '#f5f5f5' }, ticks: { font: { size: 10 }, maxTicksLimit: 10 } },
        y:  { grid: { color: '#f5f5f5' }, ticks: { font: { size: 10 }, stepSize: 1 }, title: { display: true, text: 'Responses', font: { size: 10 } } },
        y2: { position: 'right', min: 0, max: 5, grid: { drawOnChartArea: false }, ticks: { font: { size: 10 } }, title: { display: true, text: 'Avg Rating', font: { size: 10 } } }
      }
    }
  });

  // Update subLabel
  document.getElementById('trendSubLabel').textContent =
    trend.length > 0 ? trend.length + ' day(s) of data' : 'No data for period';
}

// ── Rating Distribution Chart (Bar) ──
function renderRatingChart(kpi) {
  const labels = ['Excellent (5)','Good (4)','Average (3)','Poor (2)','Very Poor (1)'];
  const data   = [kpi.cnt_5, kpi.cnt_4, kpi.cnt_3, kpi.cnt_2, kpi.cnt_1].map(Number);
  const colors = ['#1e7c3b','#1a6fbf','#b06c10','#c0392b','#922b21'];

  if (chartRating) chartRating.destroy();
  const ctx = document.getElementById('chartRating').getContext('2d');
  chartRating = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Responses',
        data,
        backgroundColor: colors,
        borderRadius: 5,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { grid: { color: '#f5f5f5' }, ticks: { font: { size: 10 }, stepSize: 1 } }
      }
    }
  });
}

// ── SQD Scores (custom HTML bars) ──
function renderSQDScores(kpi) {
  const sqds = [
    { key:'sqd0', label:'SQD0 — Anti-Red Tape Awareness' },
    { key:'sqd1', label:'SQD1 — Service Speed & Timeliness' },
    { key:'sqd2', label:'SQD2 — Updated Service Info' },
    { key:'sqd3', label:'SQD3 — Staff Courtesy & Helpfulness' },
    { key:'sqd4', label:'SQD4 — No Unnecessary Requirements' },
    { key:'sqd5', label:'SQD5 — No Extra Payment Asked' },
    { key:'sqd6', label:'SQD6 — Simple & Fast Process' },
    { key:'sqd7', label:'SQD7 — Service Delivered as Promised' },
    { key:'sqd8', label:'SQD8 — Overall Satisfaction' },
  ];

  let html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 24px">';
  sqds.forEach(s => {
    const val  = parseFloat(kpi['avg_' + s.key] || 0);
    const pct  = Math.round(val / 5 * 100);
    const color= val >= 4 ? '#1e7c3b' : val >= 3 ? '#1a6fbf' : val >= 2 ? '#b06c10' : '#c0392b';
    html += `
      <div class="sqd-row">
        <div class="sqd-label">${s.label}</div>
        <div class="sqd-bar-wrap">
          <div class="sqd-bar-fill" style="width:${pct}%;background:${color}"></div>
        </div>
        <div class="sqd-score" style="color:${color}">${val.toFixed(2)}</div>
      </div>`;
  });
  html += '</div>';
  document.getElementById('sqdScoresBody').innerHTML = html;
}

// ── Donut charts (Type, Sex, Age) ──
function renderDemoCharts(byType, bySex, byAge) {
  const donutOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } }
    },
    cutout: '62%'
  };

  // Respondent type
  const typeLabels = byType.map(t => capitalize(t.respondent_type.replace(/_/g,' ')));
  const typeData   = byType.map(t => parseInt(t.total));
  const typeColors = ['#8B1A1A','#1a6fbf','#1e7c3b','#b06c10','#888'];
  if (chartType) chartType.destroy();
  chartType = new Chart(document.getElementById('chartType').getContext('2d'), {
    type: 'doughnut',
    data: { labels: typeLabels, datasets: [{ data: typeData, backgroundColor: typeColors, borderWidth: 0 }] },
    options: donutOpts
  });

  // Sex
  const sexMap    = { male:'#1a6fbf', female:'#e06090', prefer_not_to_say:'#aaa' };
  const sexLabels = bySex.map(s => capitalize(s.sex || 'Unknown'));
  const sexData   = bySex.map(s => parseInt(s.total));
  const sexColors = bySex.map(s => sexMap[s.sex] || '#aaa');
  if (chartSex) chartSex.destroy();
  chartSex = new Chart(document.getElementById('chartSex').getContext('2d'), {
    type: 'doughnut',
    data: { labels: sexLabels, datasets: [{ data: sexData, backgroundColor: sexColors, borderWidth: 0 }] },
    options: donutOpts
  });

  // Age group
  const ageLabels = byAge.map(a => (a.age_group || 'Unknown').replace(/_/g,' '));
  const ageData   = byAge.map(a => parseInt(a.total));
  const ageColors = ['#8B1A1A','#c0392b','#e67e22','#1a6fbf','#1e7c3b'];
  if (chartAge) chartAge.destroy();
  chartAge = new Chart(document.getElementById('chartAge').getContext('2d'), {
    type: 'doughnut',
    data: { labels: ageLabels, datasets: [{ data: ageData, backgroundColor: ageColors, borderWidth: 0 }] },
    options: donutOpts
  });
}

// ── Department table ──
function renderDeptTable(byDept) {
  const tbody = document.getElementById('deptTableBody');
  if (!byDept || byDept.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:30px;color:#aaa;font-size:13px">No department data</td></tr>';
    return;
  }
  tbody.innerHTML = '';
  byDept.forEach(d => {
    const sat     = parseFloat(d.satisfaction_rate);
    const cls     = sat >= 80 ? 'high' : sat >= 60 ? 'mid' : sat >= 40 ? 'low' : 'vlow';
    const barClr  = sat >= 80 ? '#1e7c3b' : sat >= 60 ? '#1a6fbf' : sat >= 40 ? '#b06c10' : '#c0392b';
    tbody.innerHTML += `
      <tr>
        <td><strong>${escHtml(d.dept_name)}</strong></td>
        <td>${d.total}</td>
        <td>${parseFloat(d.avg_rating).toFixed(1)} ★</td>
        <td><span class="sat-badge ${cls}">${sat}%</span></td>
        <td>
          <div class="mini-bar-wrap">
            <div class="mini-bar-fill" style="width:${sat}%;background:${barClr}"></div>
          </div>
        </td>
      </tr>`;
  });
}

// ── Recent Comments ──
function renderComments(comments) {
  const el = document.getElementById('commentsBody');
  if (!comments || comments.length === 0) {
    el.innerHTML = '<div class="empty-analytics"><i class="bi bi-chat-left-text"></i><p>No comments for this period</p></div>';
    return;
  }
  const stars = r => '★'.repeat(Math.round(r)) + '☆'.repeat(5 - Math.round(r));
  let html = '<div class="comment-feed">';
  comments.forEach(c => {
    html += `
      <div class="comment-item">
        <div class="comment-meta">
          <span class="comment-dept">${escHtml(c.dept_name)}</span>
          <span class="comment-rating">${stars(c.rating)} ${c.rating}/5</span>
          <span class="comment-date">${escHtml(c.submitted_at)}</span>
        </div>
        <div class="comment-text">"${escHtml(c.comment)}"</div>
      </div>`;
  });
  html += '</div>';
  el.innerHTML = html;
}

// ── Helpers ──
function ratingLabel(r) {
  r = parseFloat(r);
  if (r >= 4.21) return 'Excellent';
  if (r >= 3.41) return 'Good';
  if (r >= 2.61) return 'Average';
  if (r >= 1.81) return 'Poor';
  return 'Very Poor';
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
}
function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
}
function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});

// ── Auto-load on page open ──
loadAnalytics();