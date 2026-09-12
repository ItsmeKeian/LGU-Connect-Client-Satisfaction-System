const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
let currentPage = 1, perPage = 10, currentFilters = {};

// Holds the currently-loaded page of records, keyed by id, so the
// View button can look a record up by id instead of embedding the
// full JSON inline in an onclick="" attribute (which breaks if any
// text field — comment, suggestion, CC answer — contains an apostrophe,
// since encodeURIComponent doesn't escape ' and it terminates the
// single-quoted attribute early). Same fix already applied to
// admin_allfeedback.js.
let feedbackById = {};

const RATING_LABELS = {5:'Strongly Agree',4:'Agree',3:'Neutral',2:'Disagree',1:'Strongly Disagree'};

document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
document.getElementById('menuToggle')?.addEventListener('click',()=>
  document.getElementById('sidebar').classList.toggle('sb-open'));
document.getElementById('refreshBtn').addEventListener('click',()=>loadFeedback(currentFilters,currentPage));
document.getElementById('filterSearch').addEventListener('keydown',e=>{if(e.key==='Enter')applyFilters();});

function toggleAvatarDropdown(e){e.stopPropagation();document.getElementById('avatarDropdown').classList.toggle('show');}
document.addEventListener('click',()=>document.getElementById('avatarDropdown')?.classList.remove('show'));

function loadFeedback(filters={},page=1){
  currentPage=page; currentFilters=filters;
  $('#feedbackTableBody').html('<tr><td colspan="8" class="text-center py-4" style="color:#6b6864"><div class="spinner-border spinner-border-sm text-danger me-2"></div>Loading...</td></tr>');

  $.get('../php/get/get_feedback.php',{page,per_page:perPage,dept:DEPT_CODE,...filters},function(res){
    if(!res.success){showToast('Failed to load feedback.','danger');return;}

    $('#sumTotal').text(Number(res.summary.total??0).toLocaleString());
    $('#sumAvg').text(parseFloat(res.summary.avg_rating||0).toFixed(2));
    $('#sumSatisfied').text(Number(res.summary.satisfied??0).toLocaleString());
    $('#sumToday').text(Number(res.summary.today??0).toLocaleString());
    $('#sbFeedbackCount').text(res.summary.total??0);

    if(!res.data.length){
      $('#feedbackTableBody').html('<tr><td colspan="8" class="text-center py-4" style="color:#6b6864"><i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4"></i>No feedback records found.</td></tr>');
      $('#recordCount').text('0 records');
      $('#paginationInfo').text('No records');
      $('#paginationLinks').html('');
      return;
    }

    feedbackById = {}; // reset for this page
    let rows='';
    res.data.forEach((f,i)=>{
      feedbackById[f.id] = f;

      const stars='★'.repeat(f.rating)+'☆'.repeat(5-f.rating);
      const type=(f.respondent_type||'citizen').replace('_',' ');
      const comment=f.comment?escHtml(f.comment).substring(0,60)+(f.comment.length>60?'…':''):'<span style="color:#9a9390;font-style:italic">No comment</span>';
      const date=new Date(f.submitted_at).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'});
      const rowNum=(page-1)*perPage+i+1;
      const rCol=f.rating>=4?'#1e7c3b':f.rating>=3?'#b06c10':'#c0392b';

      rows+=`<tr onclick="viewFeedbackById(${f.id})" style="cursor:pointer">
        <td style="color:#9a9390;font-size:.72rem">${rowNum}</td>
        <td><span style="color:${rCol};font-size:13px">${stars}</span> <span style="font-size:.75rem;font-weight:600">${f.rating}/5</span></td>
        <td><span class="type-badge" style="text-transform:capitalize">${escHtml(type)}</span></td>
        <td style="text-transform:capitalize">${f.sex?escHtml(f.sex.replace('_',' ')):'—'}</td>
        <td>${formatAge(f.age_group)}</td>
        <td style="max-width:200px">${comment}</td>
        <td style="white-space:nowrap;font-size:.75rem;color:#6b6864">${date}</td>
        <td><button class="btn btn-sm" style="background:#fdf0f0;color:#8B1A1A;border:none;font-size:.72rem;border-radius:6px;padding:4px 10px"
          onclick="event.stopPropagation();viewFeedbackById(${f.id})">
          <i class="bi bi-eye"></i> View</button></td>
      </tr>`;
    });

    $('#feedbackTableBody').html(rows);
    $('#recordCount').text(`${Number(res.total).toLocaleString()} total records`);
    renderPagination(res.total,perPage,page);
  }).fail(()=>showToast('Server error.','danger'));
}

function renderPagination(total,pp,current){
  const totalPages=Math.ceil(total/pp);
  $('#paginationInfo').text(`Showing ${(current-1)*pp+1}–${Math.min(current*pp,total)} of ${Number(total).toLocaleString()} records`);
  if(totalPages<=1){$('#paginationLinks').html('');return;}

  let links=`<li class="page-item ${current===1?'disabled':''}"><a class="page-link" href="#" onclick="loadFeedback(currentFilters,${current-1});return false"><i class="bi bi-chevron-left" style="font-size:10px"></i></a></li>`;
  if(current>3){
    links+=`<li class="page-item"><a class="page-link" href="#" onclick="loadFeedback(currentFilters,1);return false">1</a></li>`;
    if(current>4) links+=`<li class="page-item disabled"><span class="page-link">…</span></li>`;
  }
  for(let p=Math.max(1,current-2);p<=Math.min(totalPages,current+2);p++){
    links+=`<li class="page-item ${p===current?'active':''}"><a class="page-link" href="#" onclick="loadFeedback(currentFilters,${p});return false">${p}</a></li>`;
  }
  if(current<totalPages-2){
    if(current<totalPages-3) links+=`<li class="page-item disabled"><span class="page-link">…</span></li>`;
    links+=`<li class="page-item"><a class="page-link" href="#" onclick="loadFeedback(currentFilters,${totalPages});return false">${totalPages}</a></li>`;
  }
  links+=`<li class="page-item ${current===totalPages?'disabled':''}"><a class="page-link" href="#" onclick="loadFeedback(currentFilters,${current+1});return false"><i class="bi bi-chevron-right" style="font-size:10px"></i></a></li>`;
  $('#paginationLinks').html(links);
}

function applyFilters(){loadFeedback({rating:$('#filterRating').val(),type:$('#filterType').val(),period:$('#filterPeriod').val(),search:$('#filterSearch').val().trim()},1);}
function resetFilters(){$('#filterRating,#filterType,#filterPeriod').val('');$('#filterSearch').val('');loadFeedback({},1);}
function changePerPage(val){perPage=parseInt(val);loadFeedback(currentFilters,1);}

// ── View feedback modal — now looks up by id instead of parsing JSON
// embedded in the HTML attribute (see feedbackById comment above) ──
function viewFeedbackById(id){
  const f = feedbackById[id];
  if (!f) { showToast('Could not find that record — try refreshing.', 'danger'); return; }

  const stars='★'.repeat(f.rating)+'☆'.repeat(5-f.rating);
  const date=new Date(f.submitted_at).toLocaleString('en-PH');
  const rCol=f.rating>=4?'#1e7c3b':f.rating>=3?'#b06c10':'#c0392b';

  // ── SQD0-8, using per-row question text resolved server-side
  // (correct wording regardless of language/channel the row was
  // submitted under) instead of a hardcoded label list. ──
  const sqdLabels = f.sqd_labels || {};
  let sqdHtml='';
  Object.keys(sqdLabels).forEach((key, idx)=>{
    const raw = f[key];
    const label = sqdLabels[key];

    if (raw === null || raw === undefined) {
      sqdHtml += `<div style="margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#555;margin-bottom:3px">
          <span>SQD${idx} — ${escHtml(label)}</span>
          <span style="color:#888;font-weight:600">N/A</span>
        </div>
        <div style="height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden">
          <div style="width:0%;height:100%;background:#bbb;border-radius:3px"></div>
        </div></div>`;
      return;
    }

    const val=parseInt(raw), pct=val/5*100;
    const col=val>=4?'#1e7c3b':val>=3?'#e65100':'#8B1A1A';
    sqdHtml+=`<div style="margin-bottom:10px">
      <div style="display:flex;justify-content:space-between;font-size:11px;color:#555;margin-bottom:3px">
        <span>SQD${idx} — ${escHtml(label)}</span>
        <span style="color:${col};font-weight:600">${val}/5 — ${RATING_LABELS[val]??'—'}</span>
      </div>
      <div style="height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden">
        <div style="width:${pct}%;height:100%;background:${col};border-radius:3px"></div>
      </div></div>`;
  });

  // ── CC1-3, using resolved question + answer text from the backend ──
  const cc = f.cc_display || {};
  let ccHtml = '';
  ['cc1','cc2','cc3'].forEach(key=>{
    const item = cc[key];
    if (!item) return;
    ccHtml += `<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f5f5f5;font-size:12.5px">
      <span style="color:#888;font-weight:500">${key.toUpperCase()}</span>
      <span style="text-align:right;max-width:65%">${item.answer ? escHtml(item.answer) : '<em style="color:#bbb">Not answered</em>'}</span>
    </div>`;
  });

  document.getElementById('viewModalBody').innerHTML=`
    <div style="display:grid;gap:0">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Overall Rating</span>
        <div><span style="color:#c8991a;font-size:16px;letter-spacing:2px">${stars}</span>
          <strong style="color:${rCol};margin-left:8px">${f.rating}/5</strong></div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Respondent Type</span>
        <span style="text-transform:capitalize;font-weight:500">${escHtml((f.respondent_type||'citizen').replace('_',' '))}</span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Sex / Age Group</span>
        <span style="text-transform:capitalize;font-weight:500">${escHtml(f.sex||'—')} · ${formatAge(f.age_group)}</span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Region</span>
        <span style="font-weight:500">${f.region?escHtml(f.region):'—'}</span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Service Availed</span>
        <span style="font-weight:500">${f.service_availed?escHtml(f.service_availed):'—'}</span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Email</span>
        <span style="font-weight:500">${f.email?escHtml(f.email):'<em style="color:#bbb">Not provided</em>'}</span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Language</span>
        <span style="font-weight:500">${f.language==='tl'?'Tagalog':'English'}</span>
      </div>
      <div style="padding:12px 0;border-bottom:1px solid #f5f5f5">
        <div style="font-size:12px;color:#888;font-weight:500;margin-bottom:6px">Comment</div>
        <div style="font-size:13.5px;color:#333;line-height:1.6">${f.comment?escHtml(f.comment):'<em style="color:#bbb">No comment provided</em>'}</div>
      </div>
      <div style="padding:12px 0;border-bottom:1px solid #f5f5f5">
        <div style="font-size:12px;color:#888;font-weight:500;margin-bottom:6px">Suggestions</div>
        <div style="font-size:13.5px;color:#333;line-height:1.6">${f.suggestions?escHtml(f.suggestions):'<em style="color:#bbb">None</em>'}</div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5">
        <span style="font-size:12px;color:#888;font-weight:500">Submitted</span>
        <span style="font-size:12px;color:#555">${date}</span>
      </div>
      ${ccHtml?`<div style="padding:14px 0">
        <div style="font-size:12px;font-weight:700;color:#333;margin-bottom:6px">
          <i class="bi bi-file-earmark-text me-1"></i>Citizen's Charter (CC1–CC3)
        </div>${ccHtml}</div>`:''}
      ${sqdHtml?`<div style="padding:14px 0">
        <div style="font-size:12px;font-weight:700;color:#333;margin-bottom:12px">
          <i class="bi bi-list-check me-1"></i>Service Quality Dimensions (SQD)
        </div>${sqdHtml}</div>`:''}
    </div>`;
  viewModal.show();
}

function exportCSV(){
  const params=new URLSearchParams({export:'csv',dept:DEPT_CODE,...currentFilters});
  window.location.href=`../php/get/get_feedback.php?${params.toString()}`;
}

function formatAge(age){return{below_18:'Below 18','18_30':'18–30','31_45':'31–45','46_60':'46–60',above_60:'Above 60'}[age]??age??'—';}
function escHtml(s){if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function showToast(msg,type='success'){
  const el=document.getElementById('toastMsg');
  el.className=`toast align-items-center border-0 text-white bg-${type==='success'?'success':'danger'}`;
  document.getElementById('toastText').textContent=msg;
  new bootstrap.Toast(el,{delay:3000}).show();
}

loadFeedback({},1);