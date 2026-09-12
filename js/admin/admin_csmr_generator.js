// ── Local date formatting helper ──
// IMPORTANT: do NOT use `.toISOString().split('T')[0]` for local dates.
// toISOString() always converts to UTC, and in timezones ahead of UTC
// (e.g. Philippines, UTC+8), local midnight shifts back into the
// previous day once converted — silently sending the wrong date to
// the server (e.g. "Today" would actually query yesterday).
function toLocalISODate(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

// ── Date display ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ── Default date range: this month ──
const now      = new Date();
const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
const lastDay  = new Date(now.getFullYear(), now.getMonth() + 1, 0);
document.getElementById('filterDateFrom').value = toLocalISODate(firstDay);
document.getElementById('filterDateTo').value   = toLocalISODate(lastDay);

// ══════════════════════════════════════════════════════════
// FIX 1 — Load departments
// URL:  ../php/get/get_departments.php  ✅
// The success handler normalizes ALL possible response shapes:
//   • Plain array       → [ {id, name}, … ]
//   • {data:[…]}        → common wrapper
//   • {departments:[…]} → alternate wrapper
//   • {success, data}   → success-flag wrapper
// ══════════════════════════════════════════════════════════
$.ajax({
  url: '../php/get/get_departments.php',
  method: 'GET',
  success(res) {
    // get_departments.php returns: { success: true, data: [...] }
    // Each dept has: id, name, code, description, head, status
    // We must use d.code as option value (NOT d.id)
    // because feedback.department_code stores the code string (e.g. "BPLO")
    let depts = [];
    if (Array.isArray(res)) {
      depts = res;                             // plain array fallback
    } else if (res && Array.isArray(res.data)) {
      depts = res.data;                        // ✅ {success:true, data:[…]}
    } else if (res && Array.isArray(res.departments)) {
      depts = res.departments;                 // alternate wrapper fallback
    } else {
      console.warn('[CSMR] Unexpected get_departments response:', res);
    }

    const sel = document.getElementById('filterDept');
    depts.forEach(d => {
      const opt       = document.createElement('option');
      opt.value       = d.code;   // ✅ must be code, NOT id
      opt.textContent = d.name;
      sel.appendChild(opt);
    });
  },
  error(xhr) {
    // Log the raw response so you can see the PHP error immediately
    console.error('[CSMR] get_departments failed:', xhr.responseText);
  }
});

// ── Period chips ──
document.querySelectorAll('.chip[data-period]').forEach(chip => {
  chip.addEventListener('click', () => {
    document.querySelectorAll('.chip[data-period]').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    const period = chip.dataset.period;
    document.getElementById('customDateGroup').style.display = period === 'custom' ? 'grid' : 'none';
    if (period !== 'custom') setDateRange(period);
  });
});

function setDateRange(period) {
  const n = new Date();
  let from, to;
  switch (period) {
    case 'today':
      from = to = new Date(n.getFullYear(), n.getMonth(), n.getDate()); break;
    case 'this_week': {
      const dow = n.getDay() === 0 ? 6 : n.getDay() - 1;
      from = new Date(n); from.setDate(n.getDate() - dow);
      to   = new Date(n); to.setDate(n.getDate() + (6 - dow)); break;
    }
    case 'this_month':
      from = new Date(n.getFullYear(), n.getMonth(), 1);
      to   = new Date(n.getFullYear(), n.getMonth() + 1, 0); break;
    case 'last_month':
      from = new Date(n.getFullYear(), n.getMonth() - 1, 1);
      to   = new Date(n.getFullYear(), n.getMonth(), 0); break;
    case 'this_quarter': {
      const q = Math.floor(n.getMonth() / 3);
      from = new Date(n.getFullYear(), q * 3, 1);
      to   = new Date(n.getFullYear(), q * 3 + 3, 0); break;
    }
    case 'this_year':
      from = new Date(n.getFullYear(), 0, 1);
      to   = new Date(n.getFullYear(), 11, 31); break;
    default: return;
  }
  document.getElementById('filterDateFrom').value = toLocalISODate(from);
  document.getElementById('filterDateTo').value   = toLocalISODate(to);
}

// ══════════════════════════════════════════════════════════
// FIX 2 — Generate Report
// URL: ../php/get/get_csmr_data.php  ✅
// (was wrongly pointing to admin_csmr_generator.php which
//  is the HTML page, NOT a JSON endpoint)
// ══════════════════════════════════════════════════════════
let lastReportData = null;

function generateReport() {
  const dept     = document.getElementById('filterDept').value;
  const lang     = document.getElementById('filterLang').value; // NEW — report language
  const dateFrom = document.getElementById('filterDateFrom').value;
  const dateTo   = document.getElementById('filterDateTo').value;
  const inclDept = document.getElementById('inclDeptBreakdown').checked ? 1 : 0;
  const inclComm = document.getElementById('inclComments').checked ? 1 : 0;
  const inclCharts = document.getElementById('inclCharts').checked ? 1 : 0;
  const inclRaw  = document.getElementById('inclRawFeedback').checked ? 1 : 0;

  // Loading state
  document.getElementById('emptyState').style.display          = 'none';
  document.getElementById('spinnerWrap').style.display         = 'block';
  document.getElementById('statCards').style.display           = 'none';
  document.getElementById('deptSummaryGrid').style.display     = 'none';
  document.getElementById('resultsTableWrapper').style.display = 'none';
  document.getElementById('headerActions').style.display       = 'none';
  document.getElementById('generateBtn').classList.add('loading');

  $.ajax({
    url: '../php/get/get_csmr_data.php',   // ✅ dedicated JSON handler
    method: 'POST',
    dataType: 'json',
    data: {
      dept_id: dept, date_from: dateFrom, date_to: dateTo,
      incl_dept: inclDept, incl_raw: inclRaw,
      lang: lang // NEW — passed through in case the preview ever needs question text
    },
    success(res) {
      if (!res.success) {
        alert('Error: ' + (res.message || 'Unknown error'));
        resetPreview(); return;
      }
      lastReportData = res;
      renderPreview(res, { inclDept, inclComm, inclRaw, inclCharts });
      document.getElementById('printBtn').style.display = 'flex';
    },
    error(xhr) {
      // Print the raw PHP error to console — very helpful for debugging
      console.error('[CSMR] get_csmr_data failed:', xhr.responseText);
      alert('Server error — open browser console (F12) for the full error message.');
      resetPreview();
    },
    complete() {
      document.getElementById('spinnerWrap').style.display = 'none';
      document.getElementById('generateBtn').classList.remove('loading');
    }
  });
}

// ── Toggle Raw Feedback sub-options ──
function toggleRawSubOptions() {
  const checked = document.getElementById('inclRawFeedback').checked;
  const sub     = document.getElementById('rawSubOptions');
  sub.style.opacity       = checked ? '1'    : '0.35';
  sub.style.pointerEvents = checked ? 'auto' : 'none';
}

function resetPreview() {
  document.getElementById('spinnerWrap').style.display    = 'none';
  document.getElementById('emptyState').style.display     = 'flex';
  document.getElementById('chartsSection').style.display  = 'none';
  document.getElementById('commentsSection').style.display= 'none';
}

function renderPreview(res, opts) {
  const { summary, departments, feedbacks } = res;

  // ── Stat cards ──
  document.getElementById('statTotal').textContent       = summary.total_responses;
  document.getElementById('statSat').textContent         = summary.satisfaction_rate + '%';
  document.getElementById('statAvgRating').textContent   = parseFloat(summary.avg_rating).toFixed(1);
  document.getElementById('statDepts').textContent       = summary.dept_count;
  document.getElementById('statPeriodLabel').textContent = summary.period_label;
  document.getElementById('statCards').style.display     = 'grid';
  document.getElementById('periodBadgeText').textContent = summary.period_label;
  document.getElementById('headerActions').style.display = 'flex';

  // ── Department breakdown ──
  if (opts.inclDept && departments && departments.length > 0) {
    const grid = document.getElementById('deptSummaryGrid');
    grid.innerHTML = '';
    departments.forEach(d => {
      const sat   = parseFloat(d.satisfaction_rate);
      const color = sat >= 80 ? '#1e7c3b' : sat >= 60 ? '#1a6fbf' : sat >= 40 ? '#b06c10' : '#c0392b';
      grid.innerHTML += `
        <div class="dept-summary-item">
          <div class="dept-name"><i class="bi bi-building"></i> ${escHtml(d.dept_name)}</div>
          <div class="dept-mini-stats">
            <div class="dept-mini-stat"><div class="val">${d.total_responses}</div><div class="lbl">Responses</div></div>
            <div class="dept-mini-stat"><div class="val" style="color:${color}">${sat}%</div><div class="lbl">Satisfied</div></div>
            <div class="dept-mini-stat"><div class="val">${parseFloat(d.avg_rating).toFixed(1)}</div><div class="lbl">Avg Rating</div></div>
          </div>
          <div style="margin-top:10px">
            <div class="sat-bar-wrap">
              <div class="sat-bar"><div class="sat-bar-fill" style="width:${sat}%;background:${color}"></div></div>
              <span style="font-size:11px;color:#888;white-space:nowrap">${sat}% sat.</span>
            </div>
          </div>
        </div>`;
    });
    grid.style.display = 'grid';
  }

  // ── Charts & Graphs ──
  if (opts.inclCharts) {
    renderCharts(summary, departments);
    document.getElementById('chartsSection').style.display = 'block';
  } else {
    document.getElementById('chartsSection').style.display = 'none';
  }

  // ── Raw Feedback Table ──
  if (opts.inclRaw && feedbacks && feedbacks.length > 0) {
    const inclComm = document.getElementById('inclComments').checked;
    const tbody    = document.getElementById('resultsTableBody');
    tbody.innerHTML = '';
    feedbacks.forEach((f, idx) => {
      const ri  = getRatingInfo(f.rating);
      const pct = (f.rating / 5 * 100).toFixed(0);
      const cmt = inclComm
        ? escHtml(f.comment || '—')
        : '<span style="color:#ccc;font-size:11px">Hidden</span>';
      tbody.innerHTML += `
        <tr>
          <td style="color:#aaa;font-size:12px">${idx+1}</td>
          <td><strong>${escHtml(f.dept_name)}</strong></td>
          <td style="white-space:nowrap;color:#888;font-size:12px">${escHtml(f.submitted_at)}</td>
          <td style="text-transform:capitalize">${escHtml((f.respondent_type||'—').replace(/_/g,' '))}</td>
          <td><span class="rating-badge ${ri.cls}">${ri.stars} ${ri.label}</span></td>
          <td style="min-width:120px">
            <div class="sat-bar-wrap">
              <div class="sat-bar"><div class="sat-bar-fill" style="width:${pct}%;background:${ri.color}"></div></div>
              <span style="font-size:11px;color:#888">${f.rating}/5</span>
            </div>
          </td>
          <td style="max-width:220px;font-size:12px;color:#555">${cmt}</td>
        </tr>`;
    });
    document.getElementById('resultsTableWrapper').style.display = 'block';
  }
}

// ── Chart rendering (pure CSS/HTML — no external library needed) ──
function renderCharts(summary, departments) {
  const total = parseInt(summary.total_responses) || 1;

  // Chart 1: Rating Distribution horizontal bars
  const ratings = [
    { label:'Excellent (5)', count: parseInt(summary.cnt_5||0), color:'#1e7c3b' },
    { label:'Good (4)',      count: parseInt(summary.cnt_4||0), color:'#1a6fbf' },
    { label:'Average (3)',   count: parseInt(summary.cnt_3||0), color:'#b06c10' },
    { label:'Poor (2)',      count: parseInt(summary.cnt_2||0), color:'#c0392b' },
    { label:'Very Poor (1)',count: parseInt(summary.cnt_1||0), color:'#922b21' },
  ];
  let ratingHTML = '';
  ratings.forEach(r => {
    const pct = Math.round(r.count / total * 100);
    ratingHTML += `
      <div style="margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#555;margin-bottom:3px">
          <span>${r.label}</span>
          <span style="font-weight:600;color:${r.color}">${r.count} <span style="color:#aaa;font-weight:400">(${pct}%)</span></span>
        </div>
        <div style="height:10px;background:#f0f0f0;border-radius:5px;overflow:hidden">
          <div style="height:100%;width:${pct}%;background:${r.color};border-radius:5px;transition:width .6s ease"></div>
        </div>
      </div>`;
  });
  document.getElementById('chartRatingBars').innerHTML = ratingHTML;

  // Chart 2: Department satisfaction horizontal bars
  if (departments && departments.length > 0) {
    let deptHTML = '';
    departments.forEach(d => {
      const sat   = parseFloat(d.satisfaction_rate) || 0;
      const color = sat >= 80 ? '#1e7c3b' : sat >= 60 ? '#1a6fbf' : sat >= 40 ? '#b06c10' : '#c0392b';
      // Truncate long dept names
      const name  = d.dept_name.length > 28 ? d.dept_name.substring(0,26)+'…' : d.dept_name;
      deptHTML += `
        <div style="margin-bottom:10px">
          <div style="display:flex;justify-content:space-between;font-size:11px;color:#555;margin-bottom:3px">
            <span title="${escHtml(d.dept_name)}">${escHtml(name)}</span>
            <span style="font-weight:600;color:${color}">${sat}%</span>
          </div>
          <div style="height:10px;background:#f0f0f0;border-radius:5px;overflow:hidden">
            <div style="height:100%;width:${sat}%;background:${color};border-radius:5px;transition:width .6s ease"></div>
          </div>
        </div>`;
    });
    document.getElementById('chartDeptSat').innerHTML = deptHTML;
  } else {
    document.getElementById('chartDeptSat').innerHTML =
      '<p style="font-size:12px;color:#aaa;text-align:center;padding:20px 0">Check "Department Breakdown" to see this chart</p>';
  }
}

function getRatingInfo(r) {
  r = parseFloat(r);
  if (r >= 4.5) return {cls:'excellent',label:'Excellent',stars:'★★★★★',color:'#1e7c3b'};
  if (r >= 3.5) return {cls:'good',     label:'Good',     stars:'★★★★☆',color:'#1a6fbf'};
  if (r >= 2.5) return {cls:'average',  label:'Average',  stars:'★★★☆☆',color:'#b06c10'};
  return              {cls:'poor',      label:'Poor',     stars:'★★☆☆☆',color:'#c0392b'};
}

function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function openPrintView() {
  if (!lastReportData) return;
  const params = new URLSearchParams({
    dept_id:       document.getElementById('filterDept').value,
    dept_name:     document.getElementById('filterDept').selectedOptions[0].text,
    date_from:     document.getElementById('filterDateFrom').value,
    date_to:       document.getElementById('filterDateTo').value,
    title:         document.getElementById('filterTitle').value,
    lang:          document.getElementById('filterLang').value, // NEW — report language
    incl_comments: document.getElementById('inclComments').checked ? 1 : 0,
    incl_raw:      document.getElementById('inclRawFeedback').checked ? 1 : 0,
    incl_charts:   document.getElementById('inclCharts').checked ? 1 : 0
  });
  // ✅ Both files are in admin/ folder — no path prefix needed
  window.open('admin_csmr_generator_print.php?' + params.toString(), '_blank');
}

function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown').classList.remove('show');
});