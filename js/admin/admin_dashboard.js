/**
 * admin_dashboard.js
 * LGU-Connect — Super Admin Dashboard
 * Requires: jQuery 3.7+, Chart.js 4.4+
 */

$(function () {

    /* ── Chart global defaults ────────────────────────────────── */
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#6b6864';

    const RED   = '#B5121B';
    const REDT  = 'rgba(181,18,27,0.12)';
    const GOLD  = '#F0C030';
    const GOLDD = '#C8991A';
    const GREEN = '#2e7d32';

    /* ── Placeholder data ─────────────────────────────────────────
     * Replace each section with a $.get('ajax/...') call
     * when your backend is ready.
     * ───────────────────────────────────────────────────────────── */
    const DATA = {
        stats: {
            totalFeedback:  1284,
            avgScore:       4.3,
            activeDepts:    8,
            pendingReports: 2
        },
        monthStats: {
            responses: 342,
            avgScore:  4.4,
            topDept:   'MHO',
            dueReports: 2
        },
        departments: [
            { name: 'Civil Registry',     code: 'CRO',  responses: 218, avg: 4.6, color: '#B5121B' },
            { name: 'Business Permits',   code: 'BPLO', responses: 195, avg: 4.1, color: '#8B0000' },
            { name: 'Social Welfare',     code: 'MSWD', responses: 174, avg: 4.7, color: '#c0392b' },
            { name: 'Engineering Office', code: 'MEO',  responses: 156, avg: 3.9, color: '#e74c3c' },
            { name: "Treasurer's Office", code: 'MTO',  responses: 189, avg: 4.4, color: '#922b21' },
            { name: 'Health Office',      code: 'MHO',  responses: 143, avg: 4.8, color: '#641e16' },
            { name: 'Agriculture Office', code: 'MAO',  responses: 112, avg: 4.2, color: '#a93226' },
            { name: "Mayor's Office",     code: 'MO',   responses: 97,  avg: 4.5, color: '#7b241c' }
        ],
        trendLabels:  ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
        trendData:    [3.8, 3.9, 4.0, 4.1, 4.1, 4.2, 4.3, 4.3],
        sqd: {
            labels: ['Responsiveness','Reliability','Access','Communication','Costs','Integrity','Assurance','Outcome'],
            data:   [4.3, 4.4, 4.1, 4.2, 4.0, 4.6, 4.3, 4.5]
        },
        volumeData: [42, 55, 38, 61, 49, 72, 58, 44, 67, 53, 81, 76, 63, 90],
        recentFeedback: [
            { dept: 'Civil Registry',     comment: 'Very fast and courteous service. Staff were very helpful.',        score: 5, time: '5 min ago' },
            { dept: 'Business Permits',   comment: 'Process was clear but the waiting time is a bit long.',            score: 3, time: '18 min ago' },
            { dept: 'Social Welfare',     comment: 'Excellent assistance. The staff went above and beyond.',           score: 5, time: '34 min ago' },
            { dept: 'Health Office',      comment: 'Clean facility and professional staff. No complaints.',            score: 5, time: '1 hr ago' },
            { dept: "Treasurer's Office", comment: 'Efficient and straightforward. Thank you for quick response.',     score: 4, time: '2 hrs ago' }
        ]
    };

    /* ── Helpers ──────────────────────────────────────────────── */
    function starHTML(score) {
        const full  = Math.round(score);
        const empty = 5 - full;
        return '&#9733;'.repeat(full) + '<span style="color:#ddd">&#9733;</span>'.repeat(empty);
    }

    function statusPill(avg) {
        if (avg >= 4.5) return '<span class="status-pill pill-good">Excellent</span>';
        if (avg >= 3.5) return '<span class="status-pill pill-warn">Good</span>';
        return '<span class="status-pill pill-poor">Needs Attention</span>';
    }

    function scoreColor(score) {
        if (score >= 4) return GREEN;
        if (score >= 3) return '#e65100';
        return RED;
    }

    /* ── Set today's date ─────────────────────────────────────── */
    const now = new Date();
    $('#todayDate').text(now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));

    /* ── Populate stat cards ──────────────────────────────────── */
    function loadStats() {
        /* In production: $.getJSON('ajax/admin_stats.php', function(d) { ... }); */
        const s = DATA.stats;
        $('#statTotal').text(s.totalFeedback.toLocaleString());
        $('#statTotalChange').html('&#9650; 12% from last month');

        $('#statAvg').text(s.avgScore.toFixed(1));
        $('#statAvgChange').html('&#9650; 0.3 vs last quarter');

        $('#statDepts').text(s.activeDepts);
        $('#statDeptsChange').html('All departments reporting');

        $('#statReports').text(s.pendingReports);
        $('#statReportsChange').html('&#9660; Due this week');

        $('#sbFeedbackCount').text(s.totalFeedback.toLocaleString());
    }

    /* ── Populate month mini-stats ────────────────────────────── */
    function loadMonthStats() {
        const m = DATA.monthStats;
        const html = `
            <div class="mini-stat"><div class="ms-val" style="color:var(--red)">${m.responses}</div><div class="ms-label">Responses</div></div>
            <div class="mini-stat"><div class="ms-val" style="color:var(--gold-dark)">${m.avgScore.toFixed(1)}</div><div class="ms-label">Avg Score</div></div>
            <div class="mini-stat"><div class="ms-val" style="color:var(--green)">${m.topDept}</div><div class="ms-label">Top Dept</div></div>
            <div class="mini-stat"><div class="ms-val" style="color:var(--blue)">${m.dueReports}</div><div class="ms-label">Due Reports</div></div>
        `;
        $('#monthlyMiniStats').html(html);
    }

    /* ── Populate department table ────────────────────────────── */
    function loadDeptTable() {
        const rows = DATA.departments.map(dept => {
            const pct = Math.round((dept.avg / 5) * 100);
            return `
                <tr class="dept-row" data-name="${dept.name.toLowerCase()} ${dept.code.toLowerCase()}">
                    <td>
                        <div class="dept-name-wrap">
                            <div class="dept-dot" style="background:${dept.color}"></div>
                            <div>
                                <div class="dept-label">${dept.name}</div>
                                <div class="dept-code">${dept.code}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:600">${dept.responses.toLocaleString()}</td>
                    <td>
                        <div style="font-weight:700;margin-bottom:3px">${dept.avg.toFixed(1)}</div>
                        <div class="stars">${starHTML(dept.avg)}</div>
                    </td>
                    <td>
                        <div class="rating-bar-wrap">
                            <div class="rating-bar">
                                <div class="rating-bar-fill" style="width:${pct}%;background:${dept.color}"></div>
                            </div>
                            <span style="font-size:0.7rem;color:var(--text-muted);min-width:34px">${pct}%</span>
                        </div>
                    </td>
                    <td>${statusPill(dept.avg)}</td>
                    <td>
                        <button class="tb-btn" style="height:30px;padding:0 10px;font-size:0.72rem"
                                onclick="location.href='dept_detail.php?code=${dept.code}'">
                            View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        $('#deptTableBody').html(rows);
    }

    /* ── Populate recent feedback ─────────────────────────────── */
    function loadRecentFeedback() {
        const items = DATA.recentFeedback.map(fb => {
            const initials = fb.dept.replace(/[^A-Za-z]/g, '').slice(0, 2).toUpperCase();
            const color    = scoreColor(fb.score);
            return `
                <li class="feedback-item">
                    <div class="fb-avatar">${initials}</div>
                    <div class="fb-body">
                        <div class="fb-dept">${fb.dept}</div>
                        <div class="fb-comment">${fb.comment}</div>
                        <div class="fb-meta">
                            <span style="color:${color}">&#9733; ${fb.score}/5</span>
                            <span>${fb.time}</span>
                        </div>
                    </div>
                </li>
            `;
        }).join('');
        $('#recentFeedbackList').html(items);
    }

    /* ── Chart 1: Satisfaction Trend (Line) ───────────────────── */
    new Chart($('#chartTrend')[0], {
        type: 'line',
        data: {
            labels: DATA.trendLabels,
            datasets: [{
                label: 'Avg Rating',
                data: DATA.trendData,
                borderColor: RED,
                backgroundColor: REDT,
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: RED,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 3.0, max: 5.0, grid: { color: '#f0ece8' }, ticks: { stepSize: 0.5 } },
                x: { grid: { display: false } }
            }
        }
    });

    /* ── Chart 2: Department Bar ──────────────────────────────── */
    new Chart($('#chartDeptBar')[0], {
        type: 'bar',
        data: {
            labels: DATA.departments.map(d => d.code),
            datasets: [{
                label: 'Avg Rating',
                data: DATA.departments.map(d => d.avg),
                backgroundColor: DATA.departments.map(d => d.color + 'cc'),
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 5, grid: { color: '#f0ece8' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    /* ── Chart 3: SQD Radar ───────────────────────────────────── */
    new Chart($('#chartSQD')[0], {
        type: 'radar',
        data: {
            labels: DATA.sqd.labels,
            datasets: [{
                label: 'System Average',
                data: DATA.sqd.data,
                borderColor: RED,
                backgroundColor: REDT,
                borderWidth: 2,
                pointBackgroundColor: RED,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                r: {
                    min: 0, max: 5,
                    ticks: { stepSize: 1, backdropColor: 'transparent' },
                    grid: { color: '#ede8e0' },
                    pointLabels: { font: { size: 10 } }
                }
            }
        }
    });

    /* ── Chart 4: Volume Bar ──────────────────────────────────── */
    const volumeLabels = [];
    for (let i = 13; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        volumeLabels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    }

    new Chart($('#chartVolume')[0], {
        type: 'bar',
        data: {
            labels: volumeLabels,
            datasets: [{
                label: 'Submissions',
                data: DATA.volumeData,
                backgroundColor: REDT,
                borderColor: RED,
                borderWidth: 1.5,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#f0ece8' } },
                x: { grid: { display: false }, ticks: { maxRotation: 45 } }
            }
        }
    });

    /* ── Init all data ────────────────────────────────────────── */
    loadStats();
    loadMonthStats();
    loadDeptTable();
    loadRecentFeedback();

    /* ── Live dot pulse ───────────────────────────────────────── */
    setInterval(function () {
        $('#liveDot').animate({ opacity: 0.2 }, 600).animate({ opacity: 1 }, 600);
    }, 2000);

    /* ── Manual refresh ───────────────────────────────────────── */
    $('#refreshBtn').on('click', function () {
        const $btn = $(this);
        $btn.text('Refreshing...').prop('disabled', true);
        setTimeout(function () {
            loadStats();
            loadDeptTable();
            loadRecentFeedback();
            $btn.html('&#8635; Refresh').prop('disabled', false);
            $('#lastUpdated').text('just now');
        }, 700);
    });

    /* ── Auto-refresh every 60 seconds ───────────────────────── */
    setInterval(function () {
        /* In production: replace loadStats() with $.getJSON('ajax/admin_stats.php', ...) */
        loadStats();
        $('#lastUpdated').text('just now');
    }, 60000);

    /* ── Global search — filters dept table ───────────────────── */
    $('#globalSearch').on('keyup input', function () {
        const q = $(this).val().toLowerCase().trim();
        $('.dept-row').each(function () {
            $(this).toggle(!q || $(this).data('name').includes(q));
        });
    });

    /* ── Sidebar toggle (mobile) ──────────────────────────────── */
    $('#menuToggle').on('click', function () {
        $('#sidebar').toggleClass('sb-open');
    });

});