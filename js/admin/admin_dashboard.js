// ============================================================
// admin_dashboard.js — Dashboard charts + stats + table
// ============================================================

// ── Load dashboard stats ──
function loadDashboardStats() {
    $.get('../php/get/get_dashboard_stats.php', function(res) {
      if (!res.success) return;
  
      $('#statTotal').text(res.data.total_feedback ?? '—');
      $('#statAvg').text(res.data.avg_rating ?? '—');
      $('#statDepts').text(res.data.active_depts ?? '—');
      $('#statReports').text(res.data.pending_reports ?? '0');
  
      $('#statTotalChange').html(
        `<i class="bi bi-arrow-up"></i> ${res.data.total_change ?? 'vs last month'}`
      );
    });
  }
  
  // ── Charts ──
  function initCharts() {
    // Satisfaction Trend
    new Chart(document.getElementById('chartTrend'), {
      type: 'line',
      data: {
        labels: ['Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar'],
        datasets: [{
          label: 'Avg Rating',
          data: [3.8, 3.85, 4.0, 4.15, 4.2, 4.2, 4.3, 4.35],
          borderColor: '#B5121B',
          backgroundColor: 'rgba(181,18,27,0.08)',
          borderWidth: 2,
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#B5121B',
          pointRadius: 4,
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { min: 3.0, max: 5.0, grid: { color: 'rgba(0,0,0,0.05)' } },
          x: { grid: { display: false } }
        }
      }
    });
  
    // Department Comparison
    new Chart(document.getElementById('chartDeptBar'), {
      type: 'bar',
      data: {
        labels: ['CRO','BPLO','MSWD','MEO','MTO','MHO','MAO','MADM'],
        datasets: [{
          label: 'Avg Rating',
          data: [4.6, 4.1, 4.5, 3.9, 4.4, 4.7, 4.2, 4.5],
          backgroundColor: [
            '#B5121B','#1565c0','#2e7d32','#e65100',
            '#6a1b9a','#00838f','#f9a825','#4e342e'
          ],
          borderRadius: 6,
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { min: 0, max: 5, grid: { color: 'rgba(0,0,0,0.05)' } },
          x: { grid: { display: false } }
        }
      }
    });
  }
  
  // ── Load dept table ──
  function loadDeptTable() {
    $.get('../php/get/get_departments.php', function(res) {
      if (!res.success || !res.data.length) {
        $('#deptTableBody').html(`
          <tr><td colspan="6" class="text-center py-3" style="color:#9a9390;">
            No department data found.
          </td></tr>`);
        return;
      }
  
      let rows = '';
      res.data.forEach(d => {
        const rating  = parseFloat(d.avg_rating) || 0;
        const ratingW = (rating / 5 * 100).toFixed(1);
        const stars   = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
        const pillCls = rating >= 4 ? 'pill-good' : rating >= 3 ? 'pill-warn' : 'pill-poor';
        const pillLbl = rating >= 4 ? 'Satisfied' : rating >= 3 ? 'Moderate' : 'Needs Improvement';
  
        rows += `
        <tr>
          <td>
            <div class="dept-name-wrap">
              <div class="dept-dot" style="background:#B5121B;"></div>
              <div>
                <div class="dept-label">${d.name}</div>
                <div class="dept-code">${d.code}</div>
              </div>
            </div>
          </td>
          <td>${d.feedback_count ?? 0}</td>
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
          <td><span class="status-pill ${pillCls}">${rating > 0 ? pillLbl : '—'}</span></td>
          <td>
            <a href="admin_allfeedback.php?dept=${d.code}"
               class="tb-btn" style="font-size:0.72rem;height:30px;padding:0 10px;">
              View
            </a>
          </td>
        </tr>`;
      });
  
      $('#deptTableBody').html(rows);
    });
  }
  
  // ── Load recent feedback ──
  function loadRecentFeedback() {
    $.get('../php/get/get_feedback.php', {page:1, per_page:5}, function(res) {
      if (!res.success || !res.data.length) {
        $('#recentFeedbackList').html(`
          <li style="padding:20px;text-align:center;color:#9a9390;">
            No feedback yet.
          </li>`);
        return;
      }
  
      let items = '';
      res.data.forEach(f => {
        const initials = f.department_code.substring(0, 2);
        const date = new Date(f.submitted_at).toLocaleDateString('en-PH', {
          month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });
        const stars = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
  
        items += `
        <li class="feedback-item">
          <div class="fb-avatar">${initials}</div>
          <div class="fb-body">
            <div class="fb-dept">${f.department_code}</div>
            <div class="fb-comment">${f.comment || 'No comment provided.'}</div>
            <div class="fb-meta">
              <span style="color:#F0C030;">${stars}</span>
              <span>${date}</span>
            </div>
          </div>
        </li>`;
      });
  
      $('#recentFeedbackList').html(items);
    });
  }
  
  // ── Init on page load ──
  $(document).ready(function() {
    loadDashboardStats();
    loadDeptTable();
    loadRecentFeedback();
    initCharts();
  
    // Refresh button
    $('#refreshBtn').on('click', function() {
      loadDashboardStats();
      loadDeptTable();
      loadRecentFeedback();
    });
  });