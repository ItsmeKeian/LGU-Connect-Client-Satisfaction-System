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
let allDeptsCache  = []; // ✅ Store loaded departments for edit lookup

// Date display
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH', {weekday:'long',year:'numeric',month:'long',day:'numeric'});

// Refresh button
document.getElementById('refreshBtn').addEventListener('click', loadDepartments);

// Search
document.getElementById('deptSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.dept-card-wrapper').forEach(wrap => {
    const card = wrap.querySelector('.dept-card');
    const name = (card.dataset.name || '').toLowerCase();
    const code = (card.dataset.code || '').toLowerCase();
    wrap.style.display = (name.includes(q) || code.includes(q)) ? '' : 'none';
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
    allDeptsCache = res.data; // ✅ Cache for edit lookup
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
    const sat        = parseFloat(d.satisfaction_rate) || 0;
    const statusCls  = d.status === 'active' ? 'active' : 'inactive';
    const statusLbl  = d.status === 'active' ? 'Active' : 'Inactive';
    const fbCount    = parseInt(d.feedback_count) || 0;
    const channel    = d.channel === 'online' ? 'online' : 'onsite';
    const channelLbl = channel === 'online' ? 'Online' : 'Onsite';
    const channelIcon= channel === 'online' ? 'bi-wifi' : 'bi-shop';

    // Satisfaction label & color
    let satLabel, satColor;
    if (fbCount === 0) {
      satLabel = '—'; satColor = '#aaa';
    } else if (sat >= 80) {
      satLabel = 'Satisfied'; satColor = '#2e7d32';
    } else if (sat >= 60) {
      satLabel = 'Moderate'; satColor = '#e65100';
    } else {
      satLabel = 'Needs Work'; satColor = '#B5121B';
    }

    html += `
    <div class="dept-card-wrapper">
      <div class="dept-card" data-name="${escAttr(d.name)}" data-code="${escAttr(d.code)}">
        <div class="dept-card-top" style="background:${color};"></div>
        <div class="dept-card-body">
          <div class="dept-card-head">
            <div class="dept-badge-icon" style="background:${color}18;color:${color};">
              <i class="bi bi-building"></i>
            </div>
            <div class="dept-actions">
              <button class="dept-action-btn edit" title="Edit"
                data-id="${d.id}"
                onclick="openEditById(this.dataset.id)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="dept-action-btn qr" title="QR Code"
                onclick="openQrModal('${escAttr(d.code)}','${escAttr(d.name)}')">
                <i class="bi bi-qr-code"></i>
              </button>
              <button class="dept-action-btn delete" title="Delete"
                onclick="openDeleteModal(${d.id},'${escAttr(d.name)}')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>

          <div class="dept-name">${escHtml(d.name)}</div>
          <div class="dept-code" style="color:${color};font-weight:600;font-size:0.72rem;">${escHtml(d.code)}</div>
          ${d.head ? `<div class="dept-code mt-1"><i class="bi bi-person me-1"></i>${escHtml(d.head)}</div>` : ''}
          <div class="dept-code mt-1" style="color:#888">
            <i class="bi ${channelIcon} me-1"></i>${channelLbl}
          </div>

          <div class="dept-stats">
            <div class="dept-stat">
              <div class="dept-stat-val" style="color:${color};">${fbCount}</div>
              <div class="dept-stat-label">Responses</div>
            </div>
            <div class="dept-stat">
              <div class="dept-stat-val">${rating > 0 ? rating.toFixed(2) : '—'}</div>
              <div class="dept-stat-label">Avg Rating</div>
            </div>
            <div class="dept-stat">
              <div class="dept-stat-val" style="color:${satColor};font-size:${fbCount > 0 ? '0.85rem' : '1rem'};">
                ${fbCount > 0 ? sat + '%' : '—'}
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
          <a href="admin_allfeedback.php?dept=${encodeURIComponent(d.code)}" class="btn-view-dept">
            View Feedback <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>`;
  });
  $('#deptGrid').html(html);
}

// ── Summary cards ──
// ✅ Only averages from departments that actually have feedback
function updateSummary(depts) {
  const total   = depts.length;
  const active  = depts.filter(d => d.status === 'active').length;
  const totalFB = depts.reduce((s, d) => s + (parseInt(d.feedback_count) || 0), 0);

  // Weighted avg — only from depts that have feedback
  const withFB = depts.filter(d => parseFloat(d.avg_rating) > 0);
  const avgR   = withFB.length
    ? (withFB.reduce((s, d) => s + (parseFloat(d.avg_rating) || 0), 0) / withFB.length).toFixed(2)
    : '0.00';

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
  document.getElementById('deptChannel').value = 'onsite'; // NEW — default channel
  deptModal.show();
}

// ── Edit by ID (safe — no inline JSON) ──
function openEditById(id) {
  const d = allDeptsCache.find(dept => String(dept.id) === String(id));
  if (!d) {
    showToast('Department not found. Please refresh.', 'danger');
    return;
  }
  openEditModal(d);
}

// ── Edit modal ──
function openEditModal(d) {
  document.getElementById('deptModalTitle').innerHTML =
    '<i class="bi bi-pencil-square me-2"></i> Edit Department';
  document.getElementById('deptEditId').value  = d.id;
  document.getElementById('deptName').value    = d.name;
  document.getElementById('deptCode').value    = d.code;
  document.getElementById('deptStatus').value  = d.status;
  document.getElementById('deptChannel').value = d.channel === 'online' ? 'online' : 'onsite'; // NEW
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
    channel:     document.getElementById('deptChannel').value, // NEW
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
      // e.g. "Cannot delete — it has N feedback record(s). Set it to
      // Inactive instead..." from the updated delete_department.php
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

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => {
  const dd = document.getElementById('avatarDropdown');
  if (dd) dd.classList.remove('show');
});

// ── Helpers ──
function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function escAttr(s) {
  if (!s) return '';
  return String(s).replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

// ── Init ──
loadDepartments();