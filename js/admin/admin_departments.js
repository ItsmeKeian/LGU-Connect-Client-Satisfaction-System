// ============================================================
// admin_departments.js — Departments page ONLY
// ============================================================

const BASE_URL = window.location.origin + '/lgu-connect/feedback.php?dept=';

const DEPT_COLORS = [
  '#B5121B','#1565c0','#2e7d32','#e65100',
  '#6a1b9a','#00838f','#f9a825','#4e342e',
  '#37474f','#ad1457'
];

// Modals
const deptModal   = new bootstrap.Modal(document.getElementById('deptModal'));
const qrModal     = new bootstrap.Modal(document.getElementById('qrModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
let deleteTargetId = null;

// Refresh button
document.getElementById('refreshBtn').addEventListener('click', loadDepartments);

// Search
document.getElementById('deptSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.dept-card').forEach(card => {
    const name = card.dataset.name.toLowerCase();
    const code = card.dataset.code.toLowerCase();
    card.closest('.dept-card-wrapper').style.display =
      (name.includes(q) || code.includes(q)) ? '' : 'none';
  });
});

// ── Load departments ──
function loadDepartments() {
  $('#deptGrid').html(`
    <div class="empty-state">
      <div class="spinner-border text-danger" role="status"></div>
      <p class="mt-3">Loading departments...</p>
    </div>`);

  $.get('../php/get/get_departments.php', function(res) {
    if (!res.success || !res.data.length) {
      $('#deptGrid').html(`
        <div class="empty-state">
          <i class="bi bi-building-x"></i>
          <p>No departments found.<br>
          Click <strong>Add Department</strong> to get started.</p>
        </div>`);
      updateSummary([]);
      return;
    }
    updateSummary(res.data);
    renderCards(res.data);
  }).fail(() => showToast('Failed to load departments.', 'danger'));
}

// ── Render cards ──
function renderCards(depts) {
  let html = '';
  depts.forEach((d, i) => {
    const color      = DEPT_COLORS[i % DEPT_COLORS.length];
    const rating     = parseFloat(d.avg_rating) || 0;
    const ratingW    = (rating / 5 * 100).toFixed(1);
    const stars      = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
    const statusCls  = d.status === 'active' ? 'active' : 'inactive';
    const statusLbl  = d.status === 'active' ? 'Active' : 'Inactive';
    const satisfaction = rating >= 4 ? 'Satisfied' : rating >= 3 ? 'Moderate' : 'Needs Improvement';
    const satColor   = rating >= 4 ? '#2e7d32' : rating >= 3 ? '#e65100' : '#B5121B';

    html += `
    <div class="dept-card-wrapper">
      <div class="dept-card" data-name="${d.name}" data-code="${d.code}">
        <div class="dept-card-top" style="background:${color};"></div>
        <div class="dept-card-body">
          <div class="dept-card-head">
            <div class="dept-badge-icon" style="background:${color}18;color:${color};">
              <i class="bi bi-building"></i>
            </div>
            <div class="dept-actions">
              <button class="dept-action-btn edit" title="Edit"
                onclick="openEditModal(${JSON.stringify(d).replace(/'/g,'&#39;')})">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="dept-action-btn qr" title="QR Code"
                onclick="openQrModal('${d.code}','${d.name}')">
                <i class="bi bi-qr-code"></i>
              </button>
              <button class="dept-action-btn delete" title="Delete"
                onclick="openDeleteModal(${d.id},'${d.name}')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          <div class="dept-name">${d.name}</div>
          <div class="dept-code" style="color:${color};font-weight:600;font-size:0.72rem;">${d.code}</div>
          ${d.head ? `<div class="dept-code mt-1"><i class="bi bi-person me-1"></i>${d.head}</div>` : ''}
          <div class="dept-stats">
            <div class="dept-stat">
              <div class="dept-stat-val" style="color:${color};">${d.feedback_count ?? 0}</div>
              <div class="dept-stat-label">Responses</div>
            </div>
            <div class="dept-stat">
              <div class="dept-stat-val">${rating > 0 ? rating.toFixed(1) : '—'}</div>
              <div class="dept-stat-label">Avg Rating</div>
            </div>
            <div class="dept-stat">
              <div class="dept-stat-val" style="color:${satColor};font-size:0.7rem;">
                ${rating > 0 ? satisfaction : '—'}
              </div>
              <div class="dept-stat-label">Satisfaction</div>
            </div>
          </div>
          <div class="dept-rating-bar">
            <div class="dept-rating-fill"
                 style="width:${ratingW}%;background:linear-gradient(to right,${color},#F0C030);">
            </div>
          </div>
          <div style="font-size:0.72rem;color:#C8991A;letter-spacing:1px;">${stars}</div>
        </div>
        <div class="dept-card-foot">
          <span class="dept-status ${statusCls}">
            <span class="sdot"></span> ${statusLbl}
          </span>
          <a href="admin_allfeedback.php?dept=${d.code}" class="btn-view-dept">
            View Feedback <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>`;
  });
  $('#deptGrid').html(html);
}

// ── Summary cards ──
function updateSummary(depts) {
  const total   = depts.length;
  const active  = depts.filter(d => d.status === 'active').length;
  const totalFB = depts.reduce((s,d) => s + (parseInt(d.feedback_count)||0), 0);
  const avgR    = depts.length
    ? (depts.reduce((s,d) => s + (parseFloat(d.avg_rating)||0), 0) / depts.length).toFixed(2)
    : '—';

  $('#sumTotal').text(total);
  $('#sumActive').text(active);
  $('#sumAvgRating').text(avgR);
  $('#sumTotalFeedback').text(totalFB);
}

// ── Add modal ──
function openAddModal() {
  document.getElementById('deptModalTitle').innerHTML =
    '<i class="bi bi-building-add me-2"></i> Add New Department';
  ['deptEditId','deptName','deptCode','deptDesc','deptHead'].forEach(id => {
    document.getElementById(id).value = '';
  });
  document.getElementById('deptStatus').value = 'active';
  deptModal.show();
}

// ── Edit modal ──
function openEditModal(d) {
  document.getElementById('deptModalTitle').innerHTML =
    '<i class="bi bi-pencil-square me-2"></i> Edit Department';
  document.getElementById('deptEditId').value  = d.id;
  document.getElementById('deptName').value    = d.name;
  document.getElementById('deptCode').value    = d.code;
  document.getElementById('deptStatus').value  = d.status;
  document.getElementById('deptDesc').value    = d.description ?? '';
  document.getElementById('deptHead').value    = d.head ?? '';
  deptModal.show();
}

// ── Save (add/edit) ──
function saveDepartment() {
  const id   = document.getElementById('deptEditId').value;
  const name = document.getElementById('deptName').value.trim();
  const code = document.getElementById('deptCode').value.trim().toUpperCase();

  if (!name || !code) {
    showToast('Department name and code are required.', 'danger');
    return;
  }

  const payload = {
    id, name, code,
    status:      document.getElementById('deptStatus').value,
    description: document.getElementById('deptDesc').value.trim(),
    head:        document.getElementById('deptHead').value.trim(),
  };

  const btn = document.getElementById('deptSaveBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

  $.post('../php/save/save_department.php', payload, function(res) {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Department';
    if (res.success) {
      deptModal.hide();
      showToast(res.message, 'success');
      loadDepartments();
    } else {
      showToast(res.message || 'Error saving.', 'danger');
    }
  }).fail(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Department';
    showToast('Server error. Try again.', 'danger');
  });
}

// ── Delete ──
function openDeleteModal(id, name) {
  deleteTargetId = id;
  document.getElementById('deleteConfirmName').textContent = name;
  deleteModal.show();
}

function confirmDelete() {
  if (!deleteTargetId) return;
  const btn = document.getElementById('confirmDeleteBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

  $.post('../php/delete/delete_department.php', {id: deleteTargetId}, function(res) {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-trash me-1"></i> Yes, Delete';
    deleteModal.hide();
    if (res.success) {
      showToast(res.message, 'success');
      loadDepartments();
    } else {
      showToast(res.message || 'Error deleting.', 'danger');
    }
  });
}

// ── QR Code ──
function openQrModal(code, name) {
  const link  = BASE_URL + encodeURIComponent(code);
  const qrSrc = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(link)}`;
  document.getElementById('qrDeptName').textContent = name;
  document.getElementById('qrLink').textContent     = link;
  document.getElementById('qrImage').src            = qrSrc;
  document.getElementById('qrDownloadBtn').href     = qrSrc;
  document.getElementById('qrDownloadBtn').download = `QR_${code}.png`;
  qrModal.show();
}

function copyQrLink() {
  const link = document.getElementById('qrLink').textContent;
  navigator.clipboard.writeText(link)
    .then(() => showToast('Link copied!', 'success'));
}

// ── Toast ──
function showToast(msg, type = 'success') {
  const el = document.getElementById('toastMsg');
  const tx = document.getElementById('toastText');
  el.className = `toast align-items-center border-0 text-white bg-${type === 'success' ? 'success' : 'danger'}`;
  tx.textContent = msg;
  new bootstrap.Toast(el, {delay: 3000}).show();
}

// ── Init ──
loadDepartments();