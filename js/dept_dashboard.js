/**
 * dept_dashboard.js
 * LGU-Connect — Department Dashboard
 * Requires: jQuery 3.7+, Chart.js 4.4+
 * DEPT_ID and DEPT_NAME are injected by PHP inline script tag
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
     * Replace with: $.getJSON('ajax/dept_stats.php', { dept_id: DEPT_ID }, function(d) { ... });
     * ───────────────────────────────────────────────────────────── */
    const DATA = {
        stats: {
            totalFeedback: 218,
            avgScore:      4.6,
            satisfaction:  92,
            thisMonth:     68
        },
        sqd: [
            { label: 'Responsiveness', score: 4.7 },
            { label: 'Reliability',    score: 4.6 },
            { label: 'Access',         score: 4.5 },
            { label: 'Communication',  score: 4.6 },
            { label: 'Costs',          score: 4.3 },
            { label: 'Integrity',      score: 4.8 },
            { label: 'Assurance',      score: 4.7 },
            { label: 'Outcome',        score: 4.6 }
        ],
        ratingDist: { 5: 142, 4: 48, 3: 18, 2: 7, 1: 3 },
        trendLabels:  ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
        trendVolume:  [22, 28, 31, 35, 29, 42, 55, 68],
        trendAvg:     [4.2, 4.3, 4.4, 4.4, 4.5, 4.5, 4.6, 4.6],
        recentFeedback: [
            { comment: 'Very fast and courteous service. Staff were very helpful.',        score: 5, time: '5 min ago' },
            { comment: 'Process was clear but the waiting time is a bit long.',            score: 3, time: '42 min ago' },
            { comment: 'Excellent assistance. The staff went above and beyond.',           score: 5, time: '1 hr ago' },
            { comment: 'Clean and professional environment. No complaints at all.',        score: 5, time: '2 hrs ago' },
            { comment: 'Efficient and straightforward. Would highly recommend.',           score: 4, time: '3 hrs ago' }
        ]
    };

    /* ── Helpers ──────────────────────────────────────────────── */
    function starHTML(score) {
        const full = Math.round(score);
        return '&#9733;'.repeat(full) + '<span style="color:#ddd">&#9733;</span>'.repeat(5 - full);
    }

    function gradePill(score) {
        if (score >= 4.5) return '<span class="status-pill pill-good">Excellent</span>';
        if (score >= 3.5) return '<span class="status-pill pill-warn">Satisfactory</span>';
        return '<span class="status-pill pill-poor">Needs Work</span>';
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
        /* In production: $.getJSON('ajax/dept_stats.php', { dept_id: DEPT_ID }, function(d) { ... }); */
        const s = DATA.stats;
        $('#statTotal').text(s.totalFeedback.toLocaleString());
        $('#statTotalChange').html('&#9650; 14% from last month');

        $('#statAvg').text(s.avgScore.toFixed(1));
        $('#statAvgChange').html('&#9650; Top rated department');

        $('#statSatisfaction').text(s.satisfaction + '%');
        $('#statSatChange').html('&#9650; Citizens rated 4&ndash;5');

        $('#statMonth').text(s.thisMonth);
        $('#statMonthChange').html('Ongoing collection');

        $('#sbFeedbackCount').text(s.totalFeedback);
        $('#overallBadge').text('Overall: ' + s.avgScore.toFixed(1) + ' / 5.0');
    }

 
    function loadSQDTable() {
        const rows = DATA.sqd.map(item => {
            const pct = Math.round((item.score / 5) * 100);
            return `
                <tr>
                    <td style="font-weight:600">${item.label}</td>
                    <td style="font-weight:700;color:${RED}">${item.score.toFixed(1)}</td>
                    <td>
                        <div class="rating-bar-wrap">
                            <div class="rating-bar">
                                <div class="rating-bar-fill" style="width:${pct}%;background:${RED}"></div>
                            </div>
                            <span style="font-size:0.68rem;color:var(--text-muted);min-width:32px">${pct}%</span>
                        </div>
                    </td>
                    <td>${gradePill(item.score)}</td>
                </tr>
            `;
        }).join('');
        $('#sqdTableBody').html(rows);
    }

 
    function loadRecentFeedback() {
        const deptInitials = (typeof DEPT_NAME !== 'undefined')
            ? DEPT_NAME.replace(/[^A-Za-z]/g, '').slice(0, 2).toUpperCase()
            : 'DP';

        const items = DATA.recentFeedback.map(fb => {
            const color = scoreColor(fb.score);
            return `
                <li class="feedback-item fb-row" data-comment="${fb.comment.toLowerCase()}">
                    <div class="fb-avatar">${deptInitials}</div>
                    <div class="fb-body">
                        <div class="fb-comment">${fb.comment}</div>
                        <div class="fb-meta">
                            <span style="color:var(--gold-dark)">${starHTML(fb.score)}</span>
                            <span style="color:${color}">&#9679; ${fb.score}/5</span>
                            <span>${fb.time}</span>
                        </div>
                    </div>
                </li>
            `;
        }).join('');
        $('#recentFeedbackList').html(items);
    }


    function loadRatingBreakdown() {
        const total = DATA.stats.totalFeedback;
        const html  = Object.entries(DATA.ratingDist)
            .sort((a, b) => b[0] - a[0])
            .map(([star, count]) => {
                const pct = Math.round((count / total) * 100);
                return `
                    <div class="mini-bar-row">
                        <span class="mini-star-label">${star}&#9733;</span>
                        <div class="rating-bar" style="flex:1">
                            <div class="rating-bar-fill" style="width:${pct}%;background:${RED}"></div>
                        </div>
                        <span class="mini-count">${count}</span>
                    </div>
                `;
            }).join('');
        $('#ratingBreakdown').html(html);
    }

    /* ── Chart 1: SQD Horizontal Bar ─────────────────────────── */
    new Chart($('#chartSQD')[0], {
        type: 'bar',
        data: {
            labels: DATA.sqd.map(d => d.label),
            datasets: [{
                data: DATA.sqd.map(d => d.score),
                backgroundColor: 'rgba(181,18,27,0.75)',
                borderColor: RED,
                borderWidth: 0,
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { min: 0, max: 5, grid: { color: '#f0ece8' }, ticks: { stepSize: 1 } },
                y: { grid: { display: false } }
            }
        }
    });

    /* ── Chart 2: Rating Doughnut ─────────────────────────────── */
    new Chart($('#chartDist')[0], {
        type: 'doughnut',
        data: {
            labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
            datasets: [{
                data: Object.values(DATA.ratingDist),
                backgroundColor: [
                    'rgba(181,18,27,0.85)',
                    'rgba(181,18,27,0.65)',
                    'rgba(181,18,27,0.40)',
                    'rgba(181,18,27,0.20)',
                    'rgba(181,18,27,0.10)'
                ],
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 12, font: { size: 10 } }
                }
            }
        }
    });

    /* ── Chart 3: Monthly Trend (dual axis) ───────────────────── */
    new Chart($('#chartTrend')[0], {
        type: 'bar',
        data: {
            labels: DATA.trendLabels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Responses',
                    data: DATA.trendVolume,
                    backgroundColor: REDT,
                    borderColor: RED,
                    borderWidth: 1.5,
                    borderRadius: 5,
                    yAxisID: 'y'
                },
                {
                    type: 'line',
                    label: 'Avg Rating',
                    data: DATA.trendAvg,
                    borderColor: GOLD,
                    backgroundColor: 'transparent',
                    borderWidth: 2.5,
                    tension: 0.4,
                    pointBackgroundColor: GOLD,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 16, font: { size: 11 } }
                }
            },
            scales: {
                y: {
                    position: 'left',
                    grid: { color: '#f0ece8' },
                    title: { display: true, text: 'Responses', font: { size: 10 } }
                },
                y1: {
                    position: 'right',
                    min: 0, max: 5,
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Avg Rating', font: { size: 10 } }
                },
                x: { grid: { display: false } }
            }
        }
    });

    /* ── Init all ─────────────────────────────────────────────── */
    loadStats();
    loadSQDTable();
    loadRecentFeedback();
    loadRatingBreakdown();

    /* ── Live dot ─────────────────────────────────────────────── */
    setInterval(function () {
        $('#liveDot').animate({ opacity: 0.2 }, 600).animate({ opacity: 1 }, 600);
    }, 2000);

    /* ── Manual refresh ───────────────────────────────────────── */
    $('#refreshBtn').on('click', function () {
        const $btn = $(this);
        $btn.text('Refreshing...').prop('disabled', true);
        setTimeout(function () {
            /* In production: $.getJSON('ajax/dept_stats.php', { dept_id: DEPT_ID }, function(d) { ... }); */
            loadStats();
            loadRecentFeedback();
            $btn.html('&#8635; Refresh').prop('disabled', false);
            $('#lastUpdated').text('just now');
        }, 700);
    });

    /* ── Auto-refresh every 60s ───────────────────────────────── */
    setInterval(function () {
        loadStats();
        $('#lastUpdated').text('just now');
    }, 60000);

    /* ── Feedback search filter ───────────────────────────────── */
    $('#fbSearch').on('keyup input', function () {
        const q = $(this).val().toLowerCase().trim();
        $('.fb-row').each(function () {
            $(this).toggle(!q || $(this).data('comment').includes(q));
        });
    });

    /* ── Sidebar toggle ───────────────────────────────────────── */
    $('#menuToggle').on('click', function () {
        $('#sidebar').toggleClass('sb-open');
    });

});