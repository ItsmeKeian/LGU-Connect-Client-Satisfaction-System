// ── Init ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
document.getElementById('menuToggle')?.addEventListener('click',()=>
  document.getElementById('sidebar').classList.toggle('sb-open'));
function toggleAvatarDropdown(e){e.stopPropagation();document.getElementById('avatarDropdown').classList.toggle('show');}
document.addEventListener('click',()=>document.getElementById('avatarDropdown')?.classList.remove('show'));

// Sidebar badge
$.get('../php/get/get_feedback.php',{dept:DEPT_CODE,per_page:1,page:1},function(res){
  if(res.success) $('#sbFeedbackCount').text(res.summary.total||0);
});

// Set default to this month
setDateRange('this_month');

// Period chips
document.querySelectorAll('.period-chip').forEach(chip=>{
  chip.addEventListener('click',function(){
    document.querySelectorAll('.period-chip').forEach(c=>c.classList.remove('active'));
    this.classList.add('active');
    const period=this.dataset.period;
    const customGroup=document.getElementById('customDateGroup');
    const displayGroup=document.getElementById('selectedDatesDisplay');
    if(period==='custom'){
      customGroup.style.display='block';
      displayGroup.style.display='none';
    } else {
      customGroup.style.display='none';
      displayGroup.style.display='block';
      setDateRange(period);
    }
  });
});

function setDateRange(period){
  const n=new Date(); let from,to;
  switch(period){
    case 'today':        from=to=new Date(n.getFullYear(),n.getMonth(),n.getDate()); break;
    case 'this_week':    {const d=n.getDay()===0?6:n.getDay()-1;from=new Date(n);from.setDate(n.getDate()-d);to=new Date(n);to.setDate(n.getDate()+(6-d));break;}
    case 'this_month':   from=new Date(n.getFullYear(),n.getMonth(),1);to=new Date(n.getFullYear(),n.getMonth()+1,0);break;
    case 'last_month':   from=new Date(n.getFullYear(),n.getMonth()-1,1);to=new Date(n.getFullYear(),n.getMonth(),0);break;
    case 'this_quarter': {const q=Math.floor(n.getMonth()/3);from=new Date(n.getFullYear(),q*3,1);to=new Date(n.getFullYear(),q*3+3,0);break;}
    case 'this_year':    from=new Date(n.getFullYear(),0,1);to=new Date(n.getFullYear(),11,31);break;
    default: return;
  }
  selectedFrom=from.toISOString().split('T')[0];
  selectedTo=to.toISOString().split('T')[0];
  const fmt=d=>new Date(d).toLocaleDateString('en-PH',{month:'long',day:'numeric',year:'numeric'});
  document.getElementById('datesDisplay').textContent=`${fmt(selectedFrom)} – ${fmt(selectedTo)}`;
}

// ── Generate Report ──
function generateReport(){
  // Get dates
  const customGroup=document.getElementById('customDateGroup');
  if(customGroup.style.display!=='none'){
    selectedFrom=document.querySelector('#customDateGroup #dateFrom')?.value||'';
    selectedTo=document.querySelector('#customDateGroup #dateTo')?.value||'';
  }
  if(!selectedFrom||!selectedTo){alert('Please select a date range.');return;}

  document.getElementById('previewEmpty').style.display='none';
  document.getElementById('previewSpinner').style.display='block';
  document.getElementById('statCards').style.display='none';
  document.getElementById('chartsSection').style.display='none';
  document.getElementById('sqdSection').style.display='none';
  document.getElementById('demoSection').style.display='none';
  document.getElementById('commentsSection').style.display='none';
  document.getElementById('periodBadgeWrap').style.display='none';

  const btn=document.getElementById('generateBtn');
  btn.disabled=true;
  btn.innerHTML='<i class="bi bi-hourglass-split spin-anim"></i> Generating…';

  $.ajax({
    url: '../php/get/get_csmr_data.php',
    method: 'POST',
    dataType: 'json',
    data:{dept_id:DEPT_CODE,date_from:selectedFrom,date_to:selectedTo,incl_raw:0},
    success(res){
      if(!res.success){alert('Error: '+(res.message||'Failed.'));resetPreview();return;}
      lastReportData=res;
      renderPreview(res);
      document.getElementById('printBtn').classList.add('show');
      document.getElementById('topbarPrintBtn').style.display='flex';
    },
    error(xhr){console.error(xhr.responseText);resetPreview();},
    complete(){
      document.getElementById('previewSpinner').style.display='none';
      btn.disabled=false;
      btn.innerHTML='<i class="bi bi-eye"></i> Preview Report';
    }
  });
}

function resetPreview(){
  document.getElementById('previewSpinner').style.display='none';
  document.getElementById('previewEmpty').style.display='block';
}

function renderPreview(res){
  const s=res.summary;

  $('#statTotal').text(s.total_responses||0);
  $('#statSat').text((s.satisfaction_rate||0)+'%');
  $('#statAvg').text(parseFloat(s.avg_rating||0).toFixed(1));

  const sqdKeys=['avg_sqd0','avg_sqd1','avg_sqd2','avg_sqd3','avg_sqd4','avg_sqd5','avg_sqd6','avg_sqd7','avg_sqd8'];
  const sqdVals=sqdKeys.map(k=>parseFloat(s[k]||0)).filter(v=>v>0);
  $('#statSqd').text(sqdVals.length?(sqdVals.reduce((a,b)=>a+b,0)/sqdVals.length).toFixed(2):'—');

  const fmt=d=>new Date(d).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
  $('#periodBadgeText').text(`${fmt(selectedFrom)} – ${fmt(selectedTo)}`);
  $('#statCards').css('display','grid');
  $('#periodBadgeWrap').css('display','flex');

  if(document.getElementById('inclCharts').checked){
    renderRatingBars(s);
    $('#chartsSection').show();
  }

  // SQD is independent — shows even without charts
  if(document.getElementById('inclSQD').checked){
    renderSQDBars(s);
    $('#sqdSection').show();
  }

  if(document.getElementById('inclDemo').checked&&(res.by_type||res.by_age)){
    renderDemo(res);
    $('#demoSection').show();
  }

  if(document.getElementById('inclComments').checked&&res.recent_comments?.length){
    renderComments(res.recent_comments);
    $('#commentsSection').show();
  }
}

function renderRatingBars(s){
  const total=parseInt(s.total_responses)||1;
  const ratings=[
    {label:'Excellent (5★)',count:parseInt(s.cnt_5||0),color:'#1e7c3b'},
    {label:'Good (4★)',     count:parseInt(s.cnt_4||0),color:'#1a6fbf'},
    {label:'Average (3★)', count:parseInt(s.cnt_3||0),color:'#b06c10'},
    {label:'Poor (2★)',     count:parseInt(s.cnt_2||0),color:'#c0392b'},
    {label:'Very Poor (1★)',count:parseInt(s.cnt_1||0),color:'#922b21'},
  ];
  document.getElementById('ratingBars').innerHTML=ratings.map(r=>{
    const pct=Math.round(r.count/total*100);
    return `<div class="rating-bar-row">
      <div class="rbl">${r.label}</div>
      <div class="rbw"><div class="rbf" style="width:${pct}%;background:${r.color}"></div></div>
      <div class="rbc" style="color:${r.color}">${r.count} (${pct}%)</div>
    </div>`;
  }).join('');
}

function renderSQDBars(s){
  const sqds=[0,1,2,3,4,5,6,7,8].map(i=>({key:`avg_sqd${i}`,label:`SQD${i}`}));
  document.getElementById('sqdBars').innerHTML=sqds.map(q=>{
    const val=parseFloat(s[q.key]||0);
    const pct=Math.round(val/5*100);
    const col=val>=4?'#1e7c3b':val>=3?'#1a6fbf':val>=2?'#b06c10':'#c0392b';
    return `<div class="sqd-bar-row">
      <div class="sbl">${q.label}</div>
      <div class="sbw"><div class="sbf" style="width:${pct}%;background:${col}"></div></div>
      <div class="sbc" style="color:${col}">${val>0?val.toFixed(2):'—'}</div>
    </div>`;
  }).join('');
}

function renderDemo(res){
  const typeMap={citizen:'Citizen',employee:'Employee',business_owner:'Business Owner',other:'Other'};
  const ageMap={below_18:'Below 18','18_30':'18–30','31_45':'31–45','46_60':'46–60',above_60:'Above 60'};
  document.getElementById('demoType').innerHTML=(res.by_type||[]).map(t=>`
    <div class="demo-item"><span class="demo-lbl">${typeMap[t.respondent_type]||t.respondent_type}</span><span class="demo-val">${t.total}</span></div>`).join('')||'<div style="color:#bbb;font-size:12px">No data</div>';
  document.getElementById('demoAge').innerHTML=(res.by_age||[]).map(a=>`
    <div class="demo-item"><span class="demo-lbl">${ageMap[a.age_group]||a.age_group}</span><span class="demo-val">${a.total}</span></div>`).join('')||'<div style="color:#bbb;font-size:12px">No data</div>';
}

function renderComments(comments){
  const stars=r=>'★'.repeat(Math.round(r))+'☆'.repeat(5-Math.round(r));
  document.getElementById('commentsList').innerHTML=comments.slice(0,5).map(c=>`
    <div class="comment-item">
      <div class="comment-meta">
        <span style="color:#c8991a">${stars(c.rating)} ${c.rating}/5</span>
        <span style="text-transform:capitalize">${(c.respondent_type||'').replace('_',' ')}</span>
        <span>${c.submitted_at||''}</span>
      </div>
      <div class="comment-text">"${escHtml(c.comment)}"</div>
    </div>`).join('')||'<p style="color:#bbb;font-size:13px">No comments for this period.</p>';
}

function openPrint(){
  if(!lastReportData){alert('Please generate a report first.');return;}
  const params=new URLSearchParams({
    dept_id:       DEPT_CODE,
    dept_name:     DEPT_NAME,
    date_from:     selectedFrom,
    date_to:       selectedTo,
    title:         document.getElementById('reportTitle').value,
    incl_comments: document.getElementById('inclComments').checked?1:0,
    incl_charts:   document.getElementById('inclCharts').checked?1:0,
  });
  window.open('../admin/admin_csmr_generator_print.php?'+params.toString(),'_blank');
}

function escHtml(s){if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}