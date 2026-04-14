// ============================================================
// admin_dashboard.js — LGU-Connect
// All data from get_dashboard_stats.php (real DB, no fake data)
// ============================================================

let chartTrend  = null;
let chartDept   = null;
let chartSQD    = null;
let chartVolume = null;

// ── Colour palette ──
const COLORS = {
  red:    '#B5121B',
  redDark:'#8B0000',
  gold:   '#F0C030',
  green:  '#2e7d32',
  blue:   '#1565c0',
  purple: '#6a1b9a',
  teal:   '#00838f',
  orange: '#e65100',
  brown:  '#4e342e',
};

const DEPT_COLORS = [
  COLORS.red, COLORS.blue, COLORS.green, COLORS.orange,
  COLORS.purple, COLORS.teal, COLORS.gold, COLORS.brown,
  '#00695c','#ad1457','#37474f','#558b2f'
];

// ── Main loader ──
function loadDashboard() {
  $.ajax({
    url: '../php/get/get_dashboard_stats.php',
    method: 'GET',
    dataType: 'json',
    success(res) {
      if (!res.success) {
        console.error('Dashboard error:', res.message);
        return;
      }
      const d = res.data;

      renderKPIs(d);
      renderTrendChart(d.trend);
      renderDeptChart(d.dept_chart);
      renderSQDChart(d.sqd);
      renderVolumeChart(d.volume);
      renderDeptTable(d.dept_chart);
      renderRecentFeedback(d.recent_feedback);
      renderMonthlyMini(d.monthly);

      // Update last updated
      document.getElementById('lastUpdated').textContent =
        new Date().toLocaleTimeString('en-PH', {hour:'2-digit', minute:'2-digit'});
    },
    error(xhr) {
      console.error('Dashboard AJAX error:', xhr.responseText);
    }
  });
}

// ── KPI Cards ──
function renderKPIs(d) {
  $('#statTotal').text(Number(d.total_feedback).toLocaleString());
  $('#statAvg').text(d.avg_rating);
  $('#statDepts').text(d.active_depts);
  $('#statReports').text(d.satisfaction_rate); // ✅ show satisfaction rate, not pending_reports

  // Change indicator
  const up   = d.change >= 0;
  const icon = up ? 'bi-arrow-up' : 'bi-arrow-down';
  const cls  = up ? 'sc-up' : 'sc-down';
  const txt  = `${Math.abs(d.change)} (${d.change_pct}%) vs last month`;
  $('#statTotalChange')
    .removeClass('sc-up sc-down sc-neu')
    .addClass(cls)
    .html(`<i class="bi ${icon}"></i> ${txt}`);

  $('#statAvgChange').html(
    `<i class="bi bi-star-fill" style="font-size:10px"></i> ${d.satisfaction_rate} satisfied`
  );
  $('#statDeptsChange').text(`${d.total_feedback} total responses`);
  $('#statReportsChange').html(
    `<i class="bi bi-people" style="font-size:10px"></i> ${d.this_month} this month`
  );
}

// ── Trend Chart (line) — real monthly data ──
function renderTrendChart(trend) {
  const labels = trend.map(t => t.month_label);
  const avgData = trend.map(t => parseFloat(t.avg_rating) || 0);
  const satData = trend.map(t => parseFloat(t.satisfaction_rate) || 0);

  if (chartTrend) chartTrend.destroy();
  chartTrend = new Chart(document.getElementById('chartTrend'), {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Avg Rating',
          data: avgData,
          borderColor: COLORS.red,
          backgroundColor: 'rgba(181,18,27,0.08)',
          borderWidth: 2,
          tension: 0.4,
          fill: true,
          pointBackgroundColor: COLORS.red,
          pointRadius: 4,
          yAxisID: 'y',
        },
        {
          label: 'Satisfaction %',
          data: satData,
          borderColor: COLORS.green,
          backgroundColor: 'transparent',
          borderWidth: 2,
          borderDash: [4, 3],
          tension: 0.4,
          fill: false,
          pointBackgroundColor: COLORS.green,
          pointRadius: 3,
          yAxisID: 'y2',
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { labels: { font: { size: 11 }, boxWidth: 12 } }
      },
      scales: {
        y:  { min: 0, max: 5, grid: { color: 'rgba(0,0,0,0.05)' },
               title: { display: true, text: 'Avg Rating', font: { size: 10 } } },
        y2: { position: 'right', min: 0, max: 100,
               grid: { drawOnChartArea: false },
               title: { display: true, text: 'Satisfaction %', font: { size: 10 } },
               ticks: { callback: v => v + '%' } },
        x:  { grid: { display: false } }
      }
    }
  });
}

// ── Department Comparison Chart (bar) — real data ──
function renderDeptChart(deptData) {
  const labels  = deptData.map(d => d.dept_code);
  const ratings = deptData.map(d => parseFloat(d.avg_rating) || 0);
  const colors  = deptData.map((_, i) => DEPT_COLORS[i % DEPT_COLORS.length]);

  if (chartDept) chartDept.destroy();
  chartDept = new Chart(document.getElementById('chartDeptBar'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Avg Rating',
        data: ratings,
        backgroundColor: colors,
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            title: (items) => {
              // Show full dept name in tooltip
              return deptData[items[0].dataIndex]?.dept_name || items[0].label;
            }
          }
        }
      },
      scales: {
        y: { min: 0, max: 5, grid: { color: 'rgba(0,0,0,0.05)' } },
        x: { grid: { display: false } }
      }
    }
  });
}

// ── SQD Radar/Bar Chart — real data ──
function renderSQDChart(sqd) {
  const labels = [
    'SQD0\nAwareness', 'SQD1\nSpeed', 'SQD2\nInfo',
    'SQD3\nCourtesy', 'SQD4\nDocs', 'SQD5\nPayment',
    'SQD6\nProcess', 'SQD7\nPromise', 'SQD8\nOverall'
  ];
  const shortLabels = ['SQD0','SQD1','SQD2','SQD3','SQD4','SQD5','SQD6','SQD7','SQD8'];
  const values = shortLabels.map(k => parseFloat(sqd[k.toLowerCase()]) || 0);
  const bgColors = values.map(v =>
    v >= 4 ? 'rgba(46,125,50,0.7)'
    : v >= 3 ? 'rgba(21,101,192,0.7)'
    : 'rgba(181,18,27,0.7)'
  );

  if (chartSQD) chartSQD.destroy();
  chartSQD = new Chart(document.getElementById('chartSQD'), {
    type: 'bar',
    data: {
      labels: shortLabels,
      datasets: [{
        label: 'Avg Score',
        data: values,
        backgroundColor: bgColors,
        borderRadius: 5,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            title: (items) => labels[items[0].dataIndex]?.replace('\n', ' — '),
          }
        }
      },
      scales: {
        y: { min: 0, max: 5, grid: { color: 'rgba(0,0,0,0.05)' } },
        x: { grid: { display: false } }
      }
    }
  });
}

// ── Volume Chart (bar) — real daily data ──
function renderVolumeChart(volume) {
  const labels = volume.map(v => v.day_label);
  const counts = volume.map(v => parseInt(v.total) || 0);

  if (chartVolume) chartVolume.destroy();
  chartVolume = new Chart(document.getElementById('chartVolume'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Submissions',
        data: counts,
        backgroundColor: 'rgba(181,18,27,0.15)',
        borderColor: COLORS.red,
        borderWidth: 1.5,
        borderRadius: 4,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          min: 0,
          ticks: { stepSize: 1, precision: 0 },
          grid: { color: 'rgba(0,0,0,0.05)' }
        },
        x: { grid: { display: false } }
      }
    }
  });
}

// ── Department Table — real data ──
function renderDeptTable(deptData) {
  if (!deptData || deptData.length === 0) {
    $('#deptTableBody').html(`
      <tr><td colspan="6" class="text-center py-3" style="color:#9a9390;">
        No department data found.
      </td></tr>`);
    return;
  }

  let rows = '';
  deptData.forEach((d, i) => {
    const rating  = parseFloat(d.avg_rating) || 0;
    const ratingW = (rating / 5 * 100).toFixed(1);
    const stars   = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
    const sat     = parseFloat(d.satisfaction_rate) || 0;
    const pillCls = sat >= 80 ? 'pill-good' : sat >= 60 ? 'pill-warn' : 'pill-poor';
    const pillLbl = sat >= 80 ? 'Satisfied' : sat >= 60 ? 'Moderate' : 'Needs Work';
    const dotColor = DEPT_COLORS[i % DEPT_COLORS.length];

    rows += `
      <tr>
        <td>
          <div class="dept-name-wrap">
            <div class="dept-dot" style="background:${dotColor}"></div>
            <div>
              <div class="dept-label">${escHtml(d.dept_name)}</div>
              <div class="dept-code">${escHtml(d.dept_code)}</div>
            </div>
          </div>
        </td>
        <td>${Number(d.total).toLocaleString()}</td>
        <td>
          <div class="rating-bar-wrap">
            <div class="rating-bar">
              <div class="rating-bar-fill"
                   style="width:${ratingW}%;background:linear-gradient(to right,#B5121B,#F0C030);">
              </div>
            </div>
            <span style="font-size:0.75rem;font-weight:600;">${rating > 0 ? rating.toFixed(2) : '—'}</span>
          </div>
        </td>
        <td><span class="stars">${stars}</span></td>
        <td>
          ${rating > 0
            ? `<span class="status-pill ${pillCls}">${sat}% ${pillLbl}</span>`
            : '<span style="color:#bbb;font-size:0.75rem">No data</span>'}
        </td>
        <td>
          <a href="admin_allfeedback.php?dept=${encodeURIComponent(d.dept_code)}"
             class="tb-btn" style="font-size:0.72rem;height:30px;padding:0 10px;">
            View
          </a>
        </td>
      </tr>`;
  });

  $('#deptTableBody').html(rows);
}

// ── Recent Feedback ──
function renderRecentFeedback(feedbacks) {
  if (!feedbacks || feedbacks.length === 0) {
    $('#recentFeedbackList').html(`
      <li style="padding:20px;text-align:center;color:#9a9390;">
        No feedback yet.
      </li>`);
    return;
  }

  const stars = r => '★'.repeat(Math.round(r)) + '☆'.repeat(5 - Math.round(r));

  let items = '';
  feedbacks.forEach(f => {
    const initials = (f.department_code || 'XX').substring(0, 2).toUpperCase();
    const comment  = f.comment
      ? escHtml(f.comment).substring(0, 80) + (f.comment.length > 80 ? '…' : '')
      : '<em style="color:#bbb">No comment provided.</em>';
    const type     = (f.respondent_type || '').replace(/_/g, ' ');

    items += `
      <li class="feedback-item">
        <div class="fb-avatar">${initials}</div>
        <div class="fb-body">
          <div class="fb-dept">${escHtml(f.dept_name)}</div>
          <div class="fb-comment">${comment}</div>
          <div class="fb-meta">
            <span style="color:#F0C030;">${stars(f.rating)}</span>
            <span>${escHtml(f.submitted_at)}</span>
            ${type ? `<span style="text-transform:capitalize">${escHtml(type)}</span>` : ''}
          </div>
        </div>
      </li>`;
  });

  $('#recentFeedbackList').html(items);
}

// ── Monthly Mini Stats ──
function renderMonthlyMini(monthly) {
  if (!monthly) return;
  const boxes = document.getElementById('monthlyMiniStats');
  if (!boxes) return;

  const vals = boxes.querySelectorAll('.ms-val');
  if (vals[0]) vals[0].textContent = Number(monthly.responses).toLocaleString();
  if (vals[1]) vals[1].textContent = monthly.avg_score || '—';
  if (vals[2]) {
    vals[2].style.fontSize = '0.78rem';
    vals[2].textContent    = monthly.top_dept || '—';
  }
  if (vals[3]) vals[3].textContent = (monthly.satisfaction_rate || '—') + (monthly.satisfaction_rate ? '%' : '');
}

// ── Helpers ──
function escHtml(s) {
  if (!s) return '';
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Init ──
$(document).ready(function() {
  // Today date
  document.getElementById('todayDate').textContent =
    new Date().toLocaleDateString('en-PH', {
      weekday:'long', year:'numeric', month:'long', day:'numeric'
    });

  loadDashboard();

  // Refresh button
  $('#refreshBtn').on('click', function() {
    loadDashboard();
  });

  // Auto-refresh every 30 seconds
  setInterval(loadDashboard, 30000);

  // Avatar dropdown
  window.toggleAvatarDropdown = function(e) {
    e.stopPropagation();
    document.getElementById('avatarDropdown').classList.toggle('show');
  };
  document.addEventListener('click', () => {
    const dd = document.getElementById('avatarDropdown');
    if (dd) dd.classList.remove('show');
  });
});