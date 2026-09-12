// ── Local date formatting helper ──
// IMPORTANT: do NOT use `.toISOString().split('T')[0]` for local dates.
// toISOString() always converts to UTC, and in timezones ahead of UTC
// (e.g. Philippines, UTC+8), local midnight shifts back into the
// previous day once converted — silently sending the wrong date to
// the server. Same bug found and fixed in admin_csmr_generator.js,
// admin_exportdata.js, and dept_csmr.js.
function toLocalISODate(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
document.getElementById('menuToggle')?.addEventListener('click',()=>
  document.getElementById('sidebar').classList.toggle('sb-open'));
function toggleAvatarDropdown(e){e.stopPropagation();document.getElementById('avatarDropdown').classList.toggle('show');}
document.addEventListener('click',()=>document.getElementById('avatarDropdown')?.classList.remove('show'));

$.get('../php/get/get_feedback.php',{dept:DEPT_CODE,per_page:1,page:1},function(res){
  if(res.success) $('#sbFeedbackCount').text(res.summary.total||0);
});

// Default dates
const now=new Date();
document.getElementById('filterDateFrom').value=toLocalISODate(new Date(now.getFullYear(),now.getMonth(),1));
document.getElementById('filterDateTo').value=toLocalISODate(new Date(now.getFullYear(),now.getMonth()+1,0));

function applyQuickRange(){
  const val=document.getElementById('quickRange').value;
  const n=new Date(); let from,to;
  switch(val){
    case 'this_month':   from=new Date(n.getFullYear(),n.getMonth(),1);to=new Date(n.getFullYear(),n.getMonth()+1,0);break;
    case 'last_month':   from=new Date(n.getFullYear(),n.getMonth()-1,1);to=new Date(n.getFullYear(),n.getMonth(),0);break;
    case 'this_quarter': {const q=Math.floor(n.getMonth()/3);from=new Date(n.getFullYear(),q*3,1);to=new Date(n.getFullYear(),q*3+3,0);break;}
    case 'this_year':    from=new Date(n.getFullYear(),0,1);to=new Date(n.getFullYear(),11,31);break;
    case 'all_time':     from=new Date('2000-01-01');to=new Date();break;
    case 'custom':       return;
    default: return;
  }
  document.getElementById('filterDateFrom').value=toLocalISODate(from);
  document.getElementById('filterDateTo').value=toLocalISODate(to);
}

// FIXED: previously referenced an undeclared `exportHistory` array and a
// `renderLog()` call that would have wiped out the real, database-backed
// export history (rendered server-side by dept_export.php) and replaced
// it with only the current browser session's exports — on top of throwing
// a ReferenceError since `exportHistory` was never declared anywhere.
//
// Simplest correct fix: trigger the download, then reload the page after
// a short delay so PHP re-queries export_logs and shows the real,
// complete history — no fragile duplicate client-side state needed.
function doExport(type,format){
  const from=document.getElementById('filterDateFrom').value;
  const to=document.getElementById('filterDateTo').value;
  const rating=document.getElementById('filterRating').value;
  if(!from||!to){alert('Please select a date range.');return;}

  const params=new URLSearchParams({
    type,format,
    dept_id:   DEPT_CODE,  // ✅ always locked
    date_from: from,
    date_to:   to,
    rating:    rating,
  });
  window.location.href='../php/get/get_export_data.php?'+params.toString();

  // Give the server a moment to process the download + write the log
  // row, then reload so the History section reflects it.
  setTimeout(() => location.reload(), 1500);
}