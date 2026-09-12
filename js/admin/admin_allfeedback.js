// ============================================================
// admin_allfeedback.js
// ============================================================

const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

// ── State ──
let currentPage    = 1;
let currentFilters = {};
let perPage        = 10; // ✅ Default 10 per page

// Holds the currently-loaded page of records, keyed by id, so the
// View button can look a record up by id instead of embedding the
// full JSON inline in an onclick="" attribute (which broke once
// question/answer text containing apostrophes — e.g. "office's CC" —
// got embedded there).
let feedbackById = {};

const RATING_LABELS = {
  5: 'Strongly Agree',
  4: 'Agree',
  3: 'Neutral',
  2: 'Disagree',
  1: 'Strongly Disagree'
};

// ── Init ──

const todayEl = document.getElementById('todayDate');
if (todayEl) {
  todayEl.textContent =
    new Date().toLocaleDateString('en-PH', {
      weekday:'long',
      year:'numeric',
      month:'long',
      day:'numeric'
    });
}


function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  document.getElementById('avatarDropdown')?.classList.remove('show');
});
document.getElementById('avatarDropdown')?.addEventListener('click', e => e.stopPropagation());

document.getElementById('refreshBtn').addEventListener('click', () => loadFeedback(currentFilters, currentPage));

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

    // Pre-select if URL has dept param
    const urlDept = new URLSearchParams(window.location.search).get('dept');
    if (urlDept) {
      $('#filterDept').val(urlDept);
      applyFilters();
    }
  });
}

// ── Load feedback ──
function loadFeedback(filters = {}, page = 1) {
  currentPage    = page;
  currentFilters = filters;

  const params = { page, per_page: perPage, ...filters };

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

    // ── Summary cards ──
    $('#sumTotal').text(Number(res.summary.total ?? 0).toLocaleString());
    $('#sumAvg').text(res.summary.avg_rating ? parseFloat(res.summary.avg_rating).toFixed(2) : '—');
    $('#sumSatisfied').text(Number(res.summary.satisfied ?? 0).toLocaleString());
    $('#sumToday').text(Number(res.summary.today ?? 0).toLocaleString());

    if (!res.data.length) {
      $('#feedbackTableBody').html(`
        <tr><td colspan="9" class="text-center py-4" style="color:#6b6864;">
          <i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.4;"></i>
          No feedback records found.
        </td></tr>`);
      $('#recordCount').text('0 records');
      $('#paginationInfo').text('No records found');
      $('#paginationLinks').html('');
      return;
    }

    // ── Render rows ──
    feedbackById = {}; // reset for this page
    let rows = '';
    res.data.forEach((f, i) => {
      feedbackById[f.id] = f;

      const stars    = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
      const typeLabel = (f.respondent_type || 'citizen').replace('_', ' ');
      const ageLabel  = formatAge(f.age_group);
      const comment   = f.comment
        ? escHtml(f.comment).substring(0, 60) + (f.comment.length > 60 ? '…' : '')
        : '<span style="color:#9a9390;font-style:italic;">No comment</span>';
      const date  = new Date(f.submitted_at).toLocaleDateString('en-PH', {
        month:'short', day:'numeric', year:'numeric',
        hour:'2-digit', minute:'2-digit'
      });
      const rowNum = (page - 1) * perPage + i + 1;

      rows += `
      <tr onclick="viewFeedbackById(${f.id})" style="cursor:pointer;">
        <td style="color:#9a9390;font-size:0.72rem;">${rowNum}</td>
        <td><span class="dept-pill">${escHtml(f.department_code)}</span></td>
        <td>
          <span class="rating-pill rp-${f.rating}">${f.rating} <span class="star-display small">★</span></span>
        </td>
        <td><span class="type-badge" style="text-transform:capitalize">${escHtml(typeLabel)}</span></td>
        <td style="text-transform:capitalize;">${f.sex ? escHtml(f.sex.replace('_',' ')) : '—'}</td>
        <td>${ageLabel}</td>
        <td style="max-width:200px;">${comment}</td>
        <td style="white-space:nowrap;font-size:0.75rem;color:#6b6864;">${date}</td>
        <td>
          <button class="btn btn-sm"
            style="background:#fdf0f0;color:#B5121B;border:none;font-size:0.72rem;border-radius:6px;padding:4px 10px;"
            onclick="event.stopPropagation();viewFeedbackById(${f.id})">
            <i class="bi bi-eye"></i> View
          </button>
        </td>
      </tr>`;
    });

    $('#feedbackTableBody').html(rows);
    $('#recordCount').text(`${Number(res.total).toLocaleString()} total records`);
    renderPagination(res.total, perPage, page);

  }).fail(() => showToast('Server error loading feedback.', 'danger'));
}

// ── Pagination ──
function renderPagination(total, pp, current) {
  const totalPages = Math.ceil(total / pp);
  const from = (current - 1) * pp + 1;
  const to   = Math.min(current * pp, total);

  $('#paginationInfo').text(`Showing ${from}–${to} of ${Number(total).toLocaleString()} records`);

  if (totalPages <= 1) {
    $('#paginationLinks').html('');
    return;
  }

  let links = '';

  // Previous
  links += `
    <li class="page-item ${current === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#"
         onclick="loadFeedback(currentFilters, ${current - 1}); return false;">
        <i class="bi bi-chevron-left" style="font-size:10px"></i>
      </a>
    </li>`;

  // First page
  if (current > 3) {
    links += `<li class="page-item">
      <a class="page-link" href="#" onclick="loadFeedback(currentFilters,1);return false;">1</a>
    </li>`;
    if (current > 4) {
      links += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }
  }

  // Pages around current
  for (let p = Math.max(1, current - 2); p <= Math.min(totalPages, current + 2); p++) {
    links += `
      <li class="page-item ${p === current ? 'active' : ''}">
        <a class="page-link" href="#"
           onclick="loadFeedback(currentFilters, ${p}); return false;">${p}</a>
      </li>`;
  }

  // Last page
  if (current < totalPages - 2) {
    if (current < totalPages - 3) {
      links += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }
    links += `<li class="page-item">
      <a class="page-link" href="#" onclick="loadFeedback(currentFilters,${totalPages});return false;">${totalPages}</a>
    </li>`;
  }

  // Next
  links += `
    <li class="page-item ${current === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#"
         onclick="loadFeedback(currentFilters, ${current + 1}); return false;">
        <i class="bi bi-chevron-right" style="font-size:10px"></i>
      </a>
    </li>`;

  $('#paginationLinks').html(links);
}

// ── Apply / Reset filters ──
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
  $('#filterDept, #filterRating, #filterType, #filterPeriod').val('');
  $('#filterSearch').val('');
  loadFeedback({}, 1);
}

// ── Change per page ──
function changePerPage(val) {
  perPage = parseInt(val);
  loadFeedback(currentFilters, 1);
}

// ── View feedback modal ──
function viewFeedbackById(id) {
  const f = feedbackById[id];
  if (!f) {
    showToast('Could not find that record — try refreshing.', 'danger');
    return;
  }
  viewFeedback(f);
}

function viewFeedback(f) {
  const stars = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
  const date  = new Date(f.submitted_at).toLocaleString('en-PH');

  // ── SQD0-8, using per-row question text resolved server-side ──
  // (correct wording regardless of language/channel the row was submitted under)
  const sqdLabels = f.sqd_labels || {};
  let sqdHtml = '';
  Object.keys(sqdLabels).forEach((key, idx) => {
    const raw = f[key];
    const label = sqdLabels[key];

    if (raw === null || raw === undefined) {
      // N/A — explicitly shown, not treated as 0 or hidden
      sqdHtml += `
        <div class="sqd-item">
          <div class="sqd-label">SQD${idx} — ${escHtml(label)}</div>
          <div style="display:flex;align-items:center;gap:8px;margin-top:3px">
            <div style="flex:1;height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden">
              <div style="width:0%;height:100%;background:#bbb;border-radius:3px"></div>
            </div>
            <div class="sqd-val" style="color:#888;min-width:60px;font-size:0.75rem">N/A</div>
          </div>
        </div>`;
      return;
    }

    const val   = parseInt(raw);
    const pct   = (val / 5 * 100);
    const color = val >= 4 ? '#2e7d32' : val >= 3 ? '#e65100' : '#B5121B';
    sqdHtml += `
      <div class="sqd-item">
        <div class="sqd-label">SQD${idx} — ${escHtml(label)}</div>
        <div style="display:flex;align-items:center;gap:8px;margin-top:3px">
          <div style="flex:1;height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden">
            <div style="width:${pct}%;height:100%;background:${color};border-radius:3px"></div>
          </div>
          <div class="sqd-val" style="color:${color};min-width:60px;font-size:0.75rem">
            ${val}/5 — ${RATING_LABELS[val] ?? '—'}
          </div>
        </div>
      </div>`;
  });

  // ── CC1-3, using resolved question + answer text from the backend ──
  const cc = f.cc_display || {};
  let ccHtml = '';
  ['cc1', 'cc2', 'cc3'].forEach(key => {
    const item = cc[key];
    if (!item) return;
    ccHtml += `
      <div class="detail-row">
        <span class="detail-label">${key.toUpperCase()}</span>
        <span class="detail-val">
          ${item.answer
            ? escHtml(item.answer)
            : '<span style="color:#9a9390;font-style:italic;">Not answered</span>'}
        </span>
      </div>`;
  });

  const html = `
    <div class="detail-grid">
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-building me-1"></i> Department</span>
        <span class="detail-val"><strong>${escHtml(f.department_code)}</strong></span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-star me-1"></i> Overall Rating</span>
        <span class="detail-val">
          <span class="rating-pill rp-${f.rating} me-2">${f.rating} ★</span>
          <span class="star-display">${stars}</span>
        </span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-person me-1"></i> Client Type</span>
        <span class="detail-val" style="text-transform:capitalize;">
          ${escHtml((f.respondent_type || 'citizen').replace('_', ' '))}
        </span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-gender-ambiguous me-1"></i> Sex</span>
        <span class="detail-val" style="text-transform:capitalize;">
          ${f.sex ? escHtml(f.sex.replace('_', ' ')) : '—'}
        </span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-calendar3 me-1"></i> Age Group</span>
        <span class="detail-val">${formatAge(f.age_group)}</span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-geo-alt me-1"></i> Region</span>
        <span class="detail-val">${f.region ? escHtml(f.region) : '—'}</span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-clipboard me-1"></i> Service Availed</span>
        <span class="detail-val">${f.service_availed ? escHtml(f.service_availed) : '—'}</span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-envelope me-1"></i> Email</span>
        <span class="detail-val">${f.email ? escHtml(f.email) : '<span style="color:#9a9390;font-style:italic;">Not provided</span>'}</span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-translate me-1"></i> Language</span>
        <span class="detail-val">${f.language === 'tl' ? 'Tagalog' : 'English'}</span>
      </div>
      <div class="detail-cell">
        <span class="detail-label"><i class="bi bi-clock me-1"></i> Submitted</span>
        <span class="detail-val">${date}</span>
      </div>
      <div class="detail-cell full">
        <span class="detail-label"><i class="bi bi-chat-left-text me-1"></i> Comment</span>
        <span class="detail-val">${f.comment
          ? escHtml(f.comment)
          : '<span style="color:#9a9390;font-style:italic;">No comment provided</span>'}</span>
      </div>
      <div class="detail-cell full">
        <span class="detail-label"><i class="bi bi-lightbulb me-1"></i> Suggestions</span>
        <span class="detail-val">${f.suggestions
          ? escHtml(f.suggestions)
          : '<span style="color:#9a9390;font-style:italic;">None</span>'}</span>
      </div>
    </div>

    ${ccHtml ? `
    <div style="margin-top:16px;">
      <div style="font-size:0.8rem;font-weight:700;color:#3a3a3a;margin-bottom:10px;">
        <i class="bi bi-file-earmark-text me-1"></i> Citizen's Charter (CC1–CC3)
      </div>
      ${ccHtml}
    </div>` : ''}

    ${sqdHtml ? `
    <div style="margin-top:16px;">
      <div style="font-size:0.8rem;font-weight:700;color:#3a3a3a;margin-bottom:10px;">
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
  return map[age] ?? age ?? '—';
}

function escHtml(s) {
  if (!s) return '';
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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