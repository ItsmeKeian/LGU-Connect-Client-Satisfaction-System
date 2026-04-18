
// Date
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// Avatar dropdown
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}
document.addEventListener('click', () => document.getElementById('avatarDropdown')?.classList.remove('show'));

// Sidebar feedback badge
$.get('../php/get/get_feedback.php', {dept: DEPT_CODE, per_page:1, page:1}, function(res) {
  if (res.success) $('#sbFeedbackCount').text(res.summary.total || 0);
});

// ── Main loader ──
function loadDashboard() {
  $.ajax({
    url: '../php/get/get_dept_dashboard.php',
    method: 'GET',
    data: { dept: DEPT_CODE },
    dataType: 'json',
    success(res) {
      if (!res.success) return;
      const d = res.data;

      // KPIs
      $('#statTotal').text(Number(d.total).toLocaleString());
      $('#statAvg').text(parseFloat(d.avg_rating || 0).toFixed(2));
      $('#statSat').text((d.satisfaction_rate || '0') + '%');
      $('#statMonth').text(Number(d.this_month || 0).toLocaleString());

      $('#statTotalChange').html(`<i class="bi bi-calendar-check" style="font-size:10px"></i> ${d.this_month||0} this month`);
      $('#statAvgChange').html(`<i class="bi bi-star-fill" style="font-size:10px"></i> Out of 5.0`);
      $('#statSatChange').html(`<i class="bi bi-people" style="font-size:10px"></i> ${d.total||0} total responses`);
      $('#statMonthChange').html(`<i class="bi bi-calendar-day" style="font-size:10px"></i> ${d.today||0} today`);

      // Mini stats
      $('#miniResponses').text(Number(d.this_month||0).toLocaleString());
      $('#miniAvg').text(parseFloat(d.avg_rating||0).toFixed(2));
      $('#miniSat').text((d.satisfaction_rate||'0')+'%');
      $('#miniToday').text(Number(d.today||0).toLocaleString());

      renderRatingChart(d.rating_dist);
      renderTrendChart(d.trend);
      renderVolumeChart(d.volume);
      renderSQD(d.sqd);
      renderRecentFeedback(d.recent);

      document.getElementById('lastUpdated').textContent =
        new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'});
    }
  });
}

function renderRatingChart(dist) {
  if (!dist) return;
  const labels = ['Excellent (5)','Good (4)','Average (3)','Poor (2)','Very Poor (1)'];
  const data   = [dist['5']||0,dist['4']||0,dist['3']||0,dist['2']||0,dist['1']||0];
  const colors = ['#1e7c3b','#1565c0','#b06c10','#c0392b','#922b21'];
  if (chartRating) chartRating.destroy();
  chartRating = new Chart(document.getElementById('chartRating'),{
    type:'bar',
    data:{labels,datasets:[{data,backgroundColor:colors,borderRadius:6,borderSkipped:false}]},
    options:{responsive:true,plugins:{legend:{display:false}},
      scales:{y:{min:0,ticks:{stepSize:1,precision:0},grid:{color:'rgba(0,0,0,.05)'}},x:{grid:{display:false}}}}
  });
}

function renderTrendChart(trend) {
  if (!trend||!trend.length) return;
  const labels = trend.map(t=>t.month_label);
  const ratings= trend.map(t=>parseFloat(t.avg_rating)||0);
  const counts = trend.map(t=>parseInt(t.total)||0);
  if (chartTrend) chartTrend.destroy();
  chartTrend = new Chart(document.getElementById('chartTrend'),{
    type:'line',
    data:{labels,datasets:[
      {label:'Avg Rating',data:ratings,borderColor:'#B5121B',backgroundColor:'rgba(181,18,27,.08)',
       borderWidth:2,tension:.4,fill:true,pointBackgroundColor:'#B5121B',pointRadius:4,yAxisID:'y'},
      {label:'Responses',data:counts,borderColor:'#1565c0',backgroundColor:'transparent',
       borderWidth:1.5,borderDash:[4,3],tension:.4,pointRadius:3,pointBackgroundColor:'#1565c0',yAxisID:'y2'}
    ]},
    options:{responsive:true,interaction:{mode:'index',intersect:false},
      plugins:{legend:{labels:{font:{size:11},boxWidth:12}}},
      scales:{y:{min:0,max:5,grid:{color:'rgba(0,0,0,.05)'},title:{display:true,text:'Avg Rating',font:{size:10}}},
              y2:{position:'right',grid:{drawOnChartArea:false},title:{display:true,text:'Responses',font:{size:10}},ticks:{stepSize:1,precision:0}},
              x:{grid:{display:false}}}}
  });
}

function renderVolumeChart(volume) {
  if (!volume) return;
  if (chartVolume) chartVolume.destroy();
  chartVolume = new Chart(document.getElementById('chartVolume'),{
    type:'bar',
    data:{labels:volume.map(v=>v.day_label),datasets:[{
      label:'Submissions',data:volume.map(v=>parseInt(v.total)||0),
      backgroundColor:'rgba(181,18,27,.15)',borderColor:'#B5121B',
      borderWidth:1.5,borderRadius:4,borderSkipped:false}]},
    options:{responsive:true,plugins:{legend:{display:false}},
      scales:{y:{min:0,ticks:{stepSize:1,precision:0},grid:{color:'rgba(0,0,0,.05)'}},x:{grid:{display:false}}}}
  });
}

function renderSQD(sqd) {
  if (!sqd) return;
  const items = [
    {key:'sqd0',label:'SQD0 — Citizens Charter'},
    {key:'sqd1',label:'SQD1 — Service Speed'},
    {key:'sqd2',label:'SQD2 — Transaction Time'},
    {key:'sqd3',label:'SQD3 — Staff Courtesy'},
    {key:'sqd4',label:'SQD4 — No Extra Fees'},
    {key:'sqd5',label:'SQD5 — Process Compliance'},
    {key:'sqd6',label:'SQD6 — Service Quality'},
    {key:'sqd7',label:'SQD7 — Timely Delivery'},
    {key:'sqd8',label:'SQD8 — Overall Satisfaction'},
  ];
  let html = '<div style="display:flex;flex-direction:column;gap:8px">';
  items.forEach(item => {
    const val = parseFloat(sqd[item.key]||0);
    const pct = (val/5*100).toFixed(1);
    const col = val>=4?'#1e7c3b':val>=3?'#1565c0':'#c0392b';
    html += `<div style="display:flex;align-items:center;gap:8px">
      <div style="font-size:11px;color:#666;width:160px;flex-shrink:0">${item.label}</div>
      <div style="flex:1;height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden">
        <div style="width:${pct}%;height:100%;background:${col};border-radius:4px;transition:width .8s ease"></div>
      </div>
      <div style="font-size:11.5px;font-weight:600;color:${col};width:36px;text-align:right;flex-shrink:0">
        ${val>0?val.toFixed(2):'—'}
      </div></div>`;
  });
  html += '</div>';
  document.getElementById('sqdBody').innerHTML = html;
}

function renderRecentFeedback(feedbacks) {
  if (!feedbacks||!feedbacks.length) {
    $('#recentFeedbackList').html('<li style="padding:20px;text-align:center;color:#9a9390">No feedback yet.</li>');
    return;
  }
  const stars = r => '★'.repeat(Math.round(r))+'☆'.repeat(5-Math.round(r));
  let items = '';
  feedbacks.forEach(f => {
    const type    = (f.respondent_type||'citizen').replace('_',' ');
    const comment = f.comment
      ? escHtml(f.comment).substring(0,80)+(f.comment.length>80?'…':'')
      : '<em style="color:#bbb">No comment provided.</em>';
    items += `
      <li class="feedback-item">
        <div class="fb-avatar" style="background:linear-gradient(135deg,#B5121B,#8B0000)">
          ${(f.respondent_type||'C')[0].toUpperCase()}
        </div>
        <div class="fb-body">
          <div class="fb-dept" style="color:#B5121B;text-transform:capitalize">${escHtml(type)}</div>
          <div class="fb-comment">${comment}</div>
          <div class="fb-meta">
            <span style="color:#F0C030">${stars(f.rating)}</span>
            <span>${f.rating}/5</span>
            <span>${escHtml(f.submitted_at)}</span>
          </div>
        </div>
      </li>`;
  });
  $('#recentFeedbackList').html(items);
}

function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

$(document).ready(function() {
  loadDashboard();
  $('#refreshBtn').on('click', loadDashboard);
  setInterval(loadDashboard, 30000);
});