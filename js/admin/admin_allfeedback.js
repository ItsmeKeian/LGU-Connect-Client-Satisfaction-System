const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

// State
let currentPage = 1;
let currentFilters = {};
let allFeedback = [];

// SQD Labels
const SQD_LABELS = {
  sqd0: 'Aware of Citizens Charter',
  sqd1: 'Requirements are reasonable',
  sqd2: 'Steps are simple',
  sqd3: 'Time is reasonable',
  sqd4: 'Cost is reasonable',
  sqd5: 'Office is comfortable/clean',
  sqd6: 'Staff are helpful/courteous',
  sqd7: 'Service is fast',
  sqd8: 'Staff followed rules'
};

const RATING_LABELS = {
  5: 'Strongly Agree',
  4: 'Agree',
  3: 'Neutral',
  2: 'Disagree',
  1: 'Strongly Disagree'
};

// ── Init ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH', {weekday:'long',year:'numeric',month:'long',day:'numeric'});

document.getElementById('menuToggle').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('sb-open');
});

function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => document.getElementById('avatarDropdown').classList.remove('show'));
document.getElementById('avatarDropdown').addEventListener('click', e => e.stopPropagation());

document.getElementById('refreshBtn').addEventListener('click', () => loadFeedback(currentFilters));

// Enter key on search
document.getElementById('filterSearch').addEventListener('keydown', e => {
  if (e.key === 'Enter') applyFilters();
});

// ── Load departments into filter dropdown ──
function loadDeptFilter() {
  $.get('../php/get/get_departments.php', function(res) {
    if (!res.success) return;
    let opts = '<option value="">All Departments</option>';
    res.data.forEach(d => {
      opts += `<option value="${d.code}">${d.code} — ${d.name}</option>`;
    });
    $('#filterDept').html(opts);

    // If URL has dept param, pre-select it
    const urlDept = new URLSearchParams(window.location.search).get('dept');
    if (urlDept) { $('#filterDept').val(urlDept); }
  });
}

// ── Load feedback ──
function loadFeedback(filters = {}, page = 1) {
  currentPage = page;
  currentFilters = filters;

  const params = { page, per_page: 15, ...filters };

  $('#feedbackTableBody').html(`
    <tr><td colspan="9" class="text-center py-4" style="color:#6b6864;">
      <div class="spinner-border spinner-border-sm text-danger me-2"></div>
      Loading...
    </td></tr>`);

  $.get('../php/get/get_feedback.php', params, function(res) {
    if (!res.success) {
      showToast('Failed to load feedback.', 'danger');
      return;
    }

    // Update summary cards
    $('#sumTotal').text(res.summary.total ?? 0);
    $('#sumAvg').text(res.summary.avg_rating ? parseFloat(res.summary.avg_rating).toFixed(2) : '—');
    $('#sumSatisfied').text(res.summary.satisfied ?? 0);
    $('#sumToday').text(res.summary.today ?? 0);


    allFeedback = res.data;

    if (!res.data.length) {
      $('#feedbackTableBody').html(`
        <tr><td colspan="9" class="text-center py-4" style="color:#6b6864;">
          <i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.4;"></i>
          No feedback records found.
        </td></tr>`);
      $('#recordCount').text('0 records');
      $('#paginationInfo').text('No records');
      $('#paginationLinks').html('');
      return;
    }

    // Render rows
    let rows = '';
    res.data.forEach((f, i) => {
      const ratingClass = `rp-${f.rating}`;
      const stars = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
      const typeLabel = (f.respondent_type || 'citizen').replace('_', ' ');
      const ageLabel = formatAge(f.age_group);
      const comment = f.comment ? f.comment.substring(0, 60) + (f.comment.length > 60 ? '...' : '') : '<span style="color:#9a9390;font-style:italic;">No comment</span>';
      const date = new Date(f.submitted_at).toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'});
      const rowNum = (page - 1) * 15 + i + 1;

      rows += `
      <tr onclick="viewFeedback(${JSON.stringify(f).replace(/"/g,'&quot;')})" style="cursor:pointer;">
        <td style="color:#9a9390;font-size:0.72rem;">${rowNum}</td>
        <td><span class="dept-pill">${f.department_code}</span></td>
        <td>
          <span class="rating-pill ${ratingClass}">${f.rating} <span class="star-display small">★</span></span>
        </td>
        <td><span class="type-badge">${typeLabel}</span></td>
        <td style="text-transform:capitalize;">${f.sex ? f.sex.replace('_',' ') : '—'}</td>
        <td>${ageLabel}</td>
        <td style="max-width:200px;">${comment}</td>
        <td style="white-space:nowrap;font-size:0.75rem;color:#6b6864;">${date}</td>
        <td>
          <button class="btn btn-sm" style="background:#fdf0f0;color:#B5121B;border:none;font-size:0.72rem;border-radius:6px;padding:4px 10px;"
            onclick="event.stopPropagation();viewFeedback(${JSON.stringify(f).replace(/"/g,'&quot;')})">
            <i class="bi bi-eye"></i> View
          </button>
        </td>
      </tr>`;
    });

    $('#feedbackTableBody').html(rows);
    $('#recordCount').text(`${res.total} total records`);

    renderPagination(res.total, res.per_page, page);

  }).fail(() => showToast('Server error loading feedback.', 'danger'));
}

// ── Pagination ──
function renderPagination(total, perPage, current) {
  const totalPages = Math.ceil(total / perPage);
  const from = (current - 1) * perPage + 1;
  const to   = Math.min(current * perPage, total);
  $('#paginationInfo').text(`Showing ${from}–${to} of ${total} records`);

  if (totalPages <= 1) { $('#paginationLinks').html(''); return; }

  let links = '';
  links += `<li class="page-item ${current===1?'disabled':''}">
    <a class="page-link" href="#" onclick="loadFeedback(currentFilters,${current-1});return false;">‹</a></li>`;

  for (let p = Math.max(1, current-2); p <= Math.min(totalPages, current+2); p++) {
    links += `<li class="page-item ${p===current?'active':''}">
      <a class="page-link" href="#" onclick="loadFeedback(currentFilters,${p});return false;">${p}</a></li>`;
  }

  links += `<li class="page-item ${current===totalPages?'disabled':''}">
    <a class="page-link" href="#" onclick="loadFeedback(currentFilters,${current+1});return false;">›</a></li>`;

  $('#paginationLinks').html(links);
}

// ── Apply filters ──
function applyFilters() {
  const filters = {
    dept:   $('#filterDept').val(),
    rating: $('#filterRating').val(),
    type:   $('#filterType').val(),
    period: $('#filterPeriod').val(),
    search: $('#filterSearch').val().trim(),
  };
  loadFeedback(filters, 1);
}

function resetFilters() {
  $('#filterDept').val('');
  $('#filterRating').val('');
  $('#filterType').val('');
  $('#filterPeriod').val('');
  $('#filterSearch').val('');
  loadFeedback({}, 1);
}

// ── View feedback modal ──
function viewFeedback(f) {
  const stars = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
  const date  = new Date(f.submitted_at).toLocaleString('en-PH');

  let sqdHtml = '';
  Object.keys(SQD_LABELS).forEach(key => {
    if (f[key] !== null && f[key] !== undefined) {
      sqdHtml += `
      <div class="sqd-item">
        <div class="sqd-label">${SQD_LABELS[key]}</div>
        <div class="sqd-val">${f[key]} — <span style="font-size:0.72rem;font-weight:400;color:#6b6864;">${RATING_LABELS[f[key]] ?? '—'}</span></div>
      </div>`;
    }
  });

  const html = `
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-building me-1"></i> Department</span>
      <span class="detail-val"><strong>${f.department_code}</strong></span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-star me-1"></i> Overall Rating</span>
      <span class="detail-val">
        <span class="rating-pill rp-${f.rating} me-2">${f.rating} ★</span>
        <span class="star-display">${stars}</span>
      </span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-person me-1"></i> Respondent</span>
      <span class="detail-val" style="text-transform:capitalize;">${(f.respondent_type||'citizen').replace('_',' ')}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-gender-ambiguous me-1"></i> Sex</span>
      <span class="detail-val" style="text-transform:capitalize;">${f.sex ? f.sex.replace('_',' ') : '—'}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-calendar3 me-1"></i> Age Group</span>
      <span class="detail-val">${formatAge(f.age_group)}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-chat-left-text me-1"></i> Comment</span>
      <span class="detail-val">${f.comment || '<span style="color:#9a9390;font-style:italic;">No comment provided</span>'}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-lightbulb me-1"></i> Suggestions</span>
      <span class="detail-val">${f.suggestions || '<span style="color:#9a9390;font-style:italic;">None</span>'}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label"><i class="bi bi-clock me-1"></i> Submitted</span>
      <span class="detail-val">${date}</span>
    </div>

    ${sqdHtml ? `
    <div style="margin-top:16px;">
      <div style="font-size:0.8rem;font-weight:700;color:#3a3a3a;margin-bottom:8px;">
        <i class="bi bi-list-check me-1"></i> Service Quality Dimensions (SQD)
      </div>
      <div class="sqd-grid">${sqdHtml}</div>
    </div>` : ''}
  `;

  document.getElementById('viewModalBody').innerHTML = html;
  viewModal.show();
}

// ── CSV Export ──
function exportCSV() {
  const filters = { ...currentFilters, export: 'csv' };
  const params  = new URLSearchParams(filters).toString();
  window.location.href = `../php/get/get_feedback.php?${params}`;
}

// ── Helpers ──
function formatAge(age) {
  const map = {
    below_18: 'Below 18',
    '18_30':  '18–30',
    '31_45':  '31–45',
    '46_60':  '46–60',
    above_60: 'Above 60'
  };
  return map[age] ?? '—';
}

function showToast(msg, type = 'success') {
  const el = document.getElementById('toastMsg');
  const tx = document.getElementById('toastText');
  el.className = `toast align-items-center border-0 text-white bg-${type === 'success' ? 'success' : 'danger'}`;
  tx.textContent = msg;
  new bootstrap.Toast(el, {delay:3000}).show();
}

// ── Initial load ──
loadDeptFilter();
loadFeedback({}, 1);