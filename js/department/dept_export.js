
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
document.getElementById('filterDateFrom').value=new Date(now.getFullYear(),now.getMonth(),1).toISOString().split('T')[0];
document.getElementById('filterDateTo').value=new Date(now.getFullYear(),now.getMonth()+1,0).toISOString().split('T')[0];

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
  document.getElementById('filterDateFrom').value=from.toISOString().split('T')[0];
  document.getElementById('filterDateTo').value=to.toISOString().split('T')[0];
}

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

  const labels={feedback:'Raw Feedback Records',sqd:'SQD Scores Report',summary:'Summary Report',comments:'Comments & Suggestions'};
  const icons={feedback:'bi-clipboard-data-fill',sqd:'bi-bar-chart-steps',summary:'bi-clipboard2-data-fill',comments:'bi-chat-left-quote-fill'};

  exportHistory.unshift({
    type,format,
    label: labels[type]||type,
    icon:  icons[type]||'bi-download',
    from,to,
    time:  new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'}),
  });
  renderLog();
}

function renderLog(){
  if(!exportHistory.length) return;
  const fmt=d=>new Date(d).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
  document.getElementById('exportLog').innerHTML=exportHistory.map(e=>`
    <div class="log-item">
      <div class="log-icon ${e.format}"><i class="bi ${e.icon}"></i></div>
      <div class="log-name">${e.label}
        <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:6px">${fmt(e.from)} – ${fmt(e.to)}</span>
      </div>
      <span class="log-badge ${e.format}">.${e.format==='excel'?'xlsx':'csv'}</span>
      <div class="log-meta">${e.time}</div>
    </div>`).join('');
}