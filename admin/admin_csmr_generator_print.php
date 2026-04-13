<?php
require "../php/auth_check.php";
require "../php/dbconnect.php";   // provides $conn
requireSuperAdmin();

// ── Inputs ──
$dept_code     = (isset($_GET['dept_id']) && $_GET['dept_id'] !== '') ? $_GET['dept_id'] : null;
$dept_name_q   = $_GET['dept_name']    ?? 'All Departments';
$date_from     = $_GET['date_from']    ?? date('Y-m-01');
$date_to       = $_GET['date_to']      ?? date('Y-m-t');
$custom_title  = trim($_GET['title']   ?? '');
$incl_comments = (int)($_GET['incl_comments'] ?? 1);
$incl_raw      = (int)($_GET['incl_raw']      ?? 0);

$date_from_dt = date('Y-m-d', strtotime($date_from));
$date_to_dt   = date('Y-m-d', strtotime($date_to));
$period_label = date('F j, Y', strtotime($date_from_dt)) . ' to ' . date('F j, Y', strtotime($date_to_dt));
$generated_on = date('F j, Y \a\t h:i A');
$report_title = $custom_title ?: 'Client Satisfaction Measurement Report';

// ── WHERE clause ──
$where  = "WHERE DATE(f.submitted_at) BETWEEN :from AND :to";
$params = [':from' => $date_from_dt, ':to' => $date_to_dt];
if ($dept_code) {
    $where              .= " AND f.department_code = :dept_code";
    $params[':dept_code'] = $dept_code;
}

// ── Summary ──
$stmt = $conn->prepare("
    SELECT
        COUNT(*)                                                                AS total_responses,
        ROUND(AVG(f.rating), 2)                                                AS avg_rating,
        ROUND(SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) * 100.0
              / NULLIF(COUNT(*), 0), 1)                                        AS satisfaction_rate,
        COUNT(DISTINCT f.department_code)                                       AS dept_count,
        SUM(CASE WHEN f.rating = 5 THEN 1 ELSE 0 END)                         AS cnt_5,
        SUM(CASE WHEN f.rating = 4 THEN 1 ELSE 0 END)                         AS cnt_4,
        SUM(CASE WHEN f.rating = 3 THEN 1 ELSE 0 END)                         AS cnt_3,
        SUM(CASE WHEN f.rating = 2 THEN 1 ELSE 0 END)                         AS cnt_2,
        SUM(CASE WHEN f.rating = 1 THEN 1 ELSE 0 END)                         AS cnt_1,
        ROUND(AVG(f.sqd0),2) AS avg_sqd0, ROUND(AVG(f.sqd1),2) AS avg_sqd1,
        ROUND(AVG(f.sqd2),2) AS avg_sqd2, ROUND(AVG(f.sqd3),2) AS avg_sqd3,
        ROUND(AVG(f.sqd4),2) AS avg_sqd4, ROUND(AVG(f.sqd5),2) AS avg_sqd5,
        ROUND(AVG(f.sqd6),2) AS avg_sqd6, ROUND(AVG(f.sqd7),2) AS avg_sqd7,
        ROUND(AVG(f.sqd8),2) AS avg_sqd8,
        SUM(CASE WHEN f.respondent_type='citizen'        THEN 1 ELSE 0 END)   AS cnt_citizen,
        SUM(CASE WHEN f.respondent_type='employee'       THEN 1 ELSE 0 END)   AS cnt_employee,
        SUM(CASE WHEN f.respondent_type='business_owner' THEN 1 ELSE 0 END)   AS cnt_business,
        SUM(CASE WHEN f.respondent_type='other'          THEN 1 ELSE 0 END)   AS cnt_other
    FROM feedback f $where
");
$stmt->execute($params);
$s     = $stmt->fetch(PDO::FETCH_ASSOC);
$total = max((int)$s['total_responses'], 1);

// ── SQD labels (ARTA standard) ──
$sqd_labels = [
    'sqd0' => 'SQD0 — Awareness of Anti-Red Tape Act',
    'sqd1' => 'SQD1 — Service was fast and on time',
    'sqd2' => 'SQD2 — Office had updated service info',
    'sqd3' => 'SQD3 — Staff were courteous and helpful',
    'sqd4' => 'SQD4 — Asked for unnecessary documents',
    'sqd5' => 'SQD5 — Staff did not ask for extra payment',
    'sqd6' => 'SQD6 — Followed simple and fast process',
    'sqd7' => 'SQD7 — Service delivered as promised',
    'sqd8' => 'SQD8 — Overall satisfaction with service',
];
$sqd_keys = ['sqd0','sqd1','sqd2','sqd3','sqd4','sqd5','sqd6','sqd7','sqd8'];

// ── Department breakdown ──
$stmt2 = $conn->prepare("
    SELECT
        f.department_code,
        COALESCE(d.name, f.department_code)                                    AS dept_name,
        d.head                                                                 AS dept_head,
        COUNT(f.id)                                                             AS total,
        ROUND(AVG(f.rating), 2)                                                AS avg_rating,
        ROUND(SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) * 100.0
              / NULLIF(COUNT(f.id), 0), 1)                                     AS sat_rate,
        ROUND(AVG(f.sqd0),2) AS avg_sqd0, ROUND(AVG(f.sqd1),2) AS avg_sqd1,
        ROUND(AVG(f.sqd2),2) AS avg_sqd2, ROUND(AVG(f.sqd3),2) AS avg_sqd3,
        ROUND(AVG(f.sqd4),2) AS avg_sqd4, ROUND(AVG(f.sqd5),2) AS avg_sqd5,
        ROUND(AVG(f.sqd6),2) AS avg_sqd6, ROUND(AVG(f.sqd7),2) AS avg_sqd7,
        ROUND(AVG(f.sqd8),2) AS avg_sqd8
    FROM feedback f
    LEFT JOIN departments d ON d.code = f.department_code
    $where
    GROUP BY f.department_code, d.name, d.head
    ORDER BY total DESC
");
$stmt2->execute($params);
$depts = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ── Raw feedback ──
$feedbacks = [];
if ($incl_raw) {
    $stmt3 = $conn->prepare("
        SELECT
            f.rating, f.comment, f.suggestions,
            f.respondent_type, f.sex, f.age_group,
            COALESCE(d.name, f.department_code)         AS dept_name,
            DATE_FORMAT(f.submitted_at, '%b %d, %Y')    AS submitted_at
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        $where
        ORDER BY f.submitted_at DESC
        LIMIT 500
    ");
    $stmt3->execute($params);
    $feedbacks = $stmt3->fetchAll(PDO::FETCH_ASSOC);
}

// ── Helpers ──
function ratingLabel($r) {
    $r = (float)$r;
    if ($r >= 4.21) return 'Excellent';
    if ($r >= 3.41) return 'Good';
    if ($r >= 2.61) return 'Average';
    if ($r >= 1.81) return 'Poor';
    return 'Very Poor';
}
function stars($r) {
    $f = max(0, min(5, (int)round((float)$r)));
    return str_repeat('★', $f) . str_repeat('☆', 5 - $f);
}
function pct($val, $total) {
    return $total > 0 ? round((int)$val / $total * 100, 1) : 0;
}
function deptSqdAvg($d, $keys) {
    $vals = array_filter(array_map(fn($k) => (float)($d["avg_$k"] ?? 0), $keys));
    return count($vals) > 0 ? array_sum($vals) / count($vals) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>CSMR – <?= htmlspecialchars($report_title) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Times New Roman',Georgia,serif; font-size:11pt; color:#1a1a1a; background:#fff; }

@media print {
  .no-print { display:none !important; }
  .page-break { page-break-before:always; }
  body { font-size:10pt; }
  .report-wrapper { padding:0; }
  .report-paper { box-shadow:none !important; border:none !important; padding:2cm 2.5cm !important; }
}
@media screen {
  body { background:#e0e0e0; }
  .report-wrapper { padding:32px 16px 60px; }
  .report-paper { max-width:860px; margin:0 auto; background:#fff; box-shadow:0 4px 32px rgba(0,0,0,.15); border-radius:4px; padding:2cm 2.5cm; }
  .print-bar { max-width:860px; margin:0 auto 16px; display:flex; gap:10px; align-items:center; }
  .btn-print { background:#8B1A1A; color:#fff; border:none; border-radius:6px; padding:10px 22px; font-size:13px; font-weight:600; cursor:pointer; }
  .btn-print:hover { background:#6e1414; }
  .btn-close-bar { background:#fff; color:#555; border:1px solid #ddd; border-radius:6px; padding:10px 18px; font-size:13px; cursor:pointer; }
}

/* Header */
.rpt-header { text-align:center; border-bottom:3px double #8B1A1A; padding-bottom:18px; margin-bottom:20px; }
.rpt-lgu-logo { width:68px; height:68px; margin:0 auto 8px; display:block; }
.rpt-republic { font-size:9pt; letter-spacing:.07em; color:#555; text-transform:uppercase; margin-bottom:2px; }
.rpt-lgu-name { font-size:14pt; font-weight:bold; color:#8B1A1A; margin-bottom:2px; }
.rpt-lgu-address { font-size:9pt; color:#666; margin-bottom:12px; }
.rpt-doc-title { font-size:15pt; font-weight:bold; margin-bottom:4px; }
.rpt-period { font-size:10pt; color:#555; margin-bottom:6px; }
.rpt-dept-scope { display:inline-block; background:#f0e0e0; color:#8B1A1A; font-size:9pt; font-weight:bold; padding:3px 14px; border-radius:20px; text-transform:uppercase; }

/* Meta */
.rpt-meta { display:flex; justify-content:space-between; font-size:8.5pt; color:#888; margin-bottom:22px; border-bottom:1px solid #ececec; padding-bottom:8px; }

/* Section */
.rpt-section { margin-bottom:26px; }
.rpt-section-title { font-size:11pt; font-weight:bold; color:#8B1A1A; border-bottom:1.5px solid #dba0a0; padding-bottom:5px; margin-bottom:14px; text-transform:uppercase; letter-spacing:.04em; }

/* Summary boxes */
.summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px; }
.summary-box { border:1px solid #ddd; border-radius:6px; padding:11px 8px; text-align:center; }
.summary-box .sb-val { font-size:20pt; font-weight:bold; color:#8B1A1A; line-height:1; }
.summary-box .sb-lbl { font-size:7.5pt; color:#777; margin-top:4px; text-transform:uppercase; letter-spacing:.04em; }

/* Respondent type */
.resp-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:14px; }
.resp-box { background:#f9f9f9; border:1px solid #eee; border-radius:5px; padding:8px; text-align:center; }
.resp-box .rv { font-size:14pt; font-weight:bold; color:#444; }
.resp-box .rl { font-size:7.5pt; color:#888; margin-top:2px; }

/* Narrative */
.narrative { font-size:10pt; line-height:1.8; color:#333; }

/* Rating bars */
.rating-row { display:flex; align-items:center; gap:8px; margin-bottom:6px; font-size:9.5pt; }
.rating-row .r-label { width:90px; color:#444; font-weight:bold; flex-shrink:0; }
.rating-row .r-bar-wrap { flex:1; background:#f0f0f0; border-radius:3px; height:13px; overflow:hidden; }
.rating-row .r-bar { height:100%; border-radius:3px; }
.rating-row .r-count { width:38px; text-align:right; color:#555; }
.rating-row .r-pct   { width:42px; text-align:right; color:#999; font-size:9pt; }

/* Generic table base */
.rpt-table { width:100%; border-collapse:collapse; font-size:9pt; }
.rpt-table th { padding:7px 10px; text-align:left; font-size:8.5pt; text-transform:uppercase; letter-spacing:.03em; }
.rpt-table td { padding:7px 10px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.rpt-table tr:nth-child(even) td { background:#fafafa; }
.rpt-table tr:last-child td { border-bottom:none; }

/* SQD table */
.sqd-table th { background:#8B1A1A; color:#fff; }
.sqd-table td:not(:first-child):not(:nth-child(2)) { text-align:center; }
.sqd-table .overall-row td { font-weight:bold; background:#fff8f0 !important; border-top:1.5px solid #dba0a0; }

/* Dept table */
.dept-table th { background:#5a1010; color:#fff; }
.dept-table td:not(:first-child) { text-align:center; }

/* Sat pill */
.sat-pill { display:inline-block; padding:2px 8px; border-radius:12px; font-size:8.5pt; font-weight:bold; }
.sat-pill.high { background:#d4f0dc; color:#1a5c2e; }
.sat-pill.mid  { background:#d6e8fb; color:#1a4a80; }
.sat-pill.low  { background:#fde8d8; color:#8c3a10; }
.sat-pill.vlow { background:#fcd7d7; color:#7a1818; }

/* Mini bar */
.mini-bar-wrap { background:#f0f0f0; border-radius:2px; height:7px; min-width:50px; }
.mini-bar { height:100%; border-radius:2px; }

/* Feedback table */
.feedback-table th { background:#3a3a3a; color:#fff; font-size:8pt; }
.feedback-table td { font-size:8.5pt; }

/* Signatures */
.sig-block { margin-top:44px; display:grid; grid-template-columns:1fr 1fr; gap:48px; }
.sig-item { text-align:center; }
.sig-line { border-top:1px solid #888; margin-bottom:6px; }
.sig-name { font-weight:bold; font-size:10pt; text-transform:uppercase; }
.sig-title { font-size:9pt; color:#666; }

/* Footer */
.rpt-footer { margin-top:28px; padding-top:10px; border-top:1px solid #ddd; text-align:center; font-size:8pt; color:#aaa; }
</style>
</head>
<body>
<div class="report-wrapper">

  <!-- Print bar (screen only) -->
  <div class="print-bar no-print">
    <button class="btn-print" onclick="window.print()">🖨&nbsp; Print / Save as PDF</button>
    <button class="btn-close-bar" onclick="window.close()">✕ Close</button>
    <span style="font-size:12px;color:#777;margin-left:8px">
      In the print dialog → choose <em>Save as PDF</em> for a digital copy.
    </span>
  </div>

  <div class="report-paper">

    <!-- ══ HEADER ══ -->
    <div class="rpt-header">
      <img src="../assets/img/logo.png" class="rpt-lgu-logo" alt="LGU Logo"
           onerror="this.style.display='none'"/>
      <div class="rpt-republic">Republic of the Philippines</div>
      <div class="rpt-republic">Province of Eastern Samar</div>
      <div class="rpt-lgu-name">Municipality of San Julian</div>
      <div class="rpt-lgu-address">San Julian, Eastern Samar</div>
      <div style="margin:14px 0 8px">
        <div class="rpt-doc-title"><?= htmlspecialchars($report_title) ?></div>
        <div class="rpt-period">Covering Period: <strong><?= htmlspecialchars($period_label) ?></strong></div>
      </div>
      <div class="rpt-dept-scope">
        <?= $dept_code ? htmlspecialchars($dept_name_q) : 'System-Wide — All Departments' ?>
      </div>
    </div>

    <!-- ══ META ══ -->
    <div class="rpt-meta">
      <span>Doc No.: CSMR-<?= date('Y') ?>-<?= str_pad(rand(1,999),3,'0',STR_PAD_LEFT) ?></span>
      <span>Generated: <?= $generated_on ?></span>
      <span>By: <?= htmlspecialchars(CURRENT_USER) ?></span>
    </div>

    <!-- ══ I. EXECUTIVE SUMMARY ══ -->
    <div class="rpt-section">
      <div class="rpt-section-title">I. Executive Summary</div>

      <!-- 4 stat boxes -->
      <div class="summary-grid">
        <div class="summary-box">
          <div class="sb-val"><?= number_format($s['total_responses']) ?></div>
          <div class="sb-lbl">Total Respondents</div>
        </div>
        <div class="summary-box">
          <div class="sb-val"><?= $s['satisfaction_rate'] ?>%</div>
          <div class="sb-lbl">Satisfaction Rate</div>
        </div>
        <div class="summary-box">
          <div class="sb-val"><?= number_format($s['avg_rating'],2) ?></div>
          <div class="sb-lbl">Average Rating / 5.0</div>
        </div>
        <div class="summary-box">
          <div class="sb-val"><?= $s['dept_count'] ?></div>
          <div class="sb-lbl">Departments Covered</div>
        </div>
      </div>

      <!-- Respondent type breakdown -->
      <div class="resp-grid">
        <div class="resp-box">
          <div class="rv"><?= $s['cnt_citizen'] ?></div>
          <div class="rl">Citizens</div>
        </div>
        <div class="resp-box">
          <div class="rv"><?= $s['cnt_employee'] ?></div>
          <div class="rl">Employees</div>
        </div>
        <div class="resp-box">
          <div class="rv"><?= $s['cnt_business'] ?></div>
          <div class="rl">Business Owners</div>
        </div>
        <div class="resp-box">
          <div class="rv"><?= $s['cnt_other'] ?></div>
          <div class="rl">Others</div>
        </div>
      </div>

      <!-- Narrative paragraph -->
      <p class="narrative">
        During the period of <strong><?= htmlspecialchars($period_label) ?></strong>,
        the <?= $dept_code
          ? 'Office of <strong>' . htmlspecialchars($dept_name_q) . '</strong>'
          : 'Local Government Unit of San Julian' ?>
        received a total of
        <strong><?= number_format($s['total_responses']) ?></strong>
        client feedback submission<?= $s['total_responses'] != 1 ? 's' : '' ?>.
        The overall client satisfaction rate was recorded at
        <strong><?= $s['satisfaction_rate'] ?>%</strong>
        with an average service rating of
        <strong><?= number_format($s['avg_rating'],2) ?> out of 5.0</strong>,
        which is categorized as <strong><?= ratingLabel($s['avg_rating']) ?></strong>.
        <?php if ($s['satisfaction_rate'] >= 80): ?>
          This reflects a commendable level of public satisfaction with the services rendered by this office.
        <?php elseif ($s['satisfaction_rate'] >= 60): ?>
          This indicates a satisfactory level of service delivery with identifiable areas for improvement.
        <?php else: ?>
          This indicates that service delivery improvements are necessary to better meet client expectations.
        <?php endif; ?>
      </p>
    </div>

    <!-- ══ II. RATING DISTRIBUTION ══ -->
    <div class="rpt-section">
      <div class="rpt-section-title">II. Rating Distribution</div>
      <?php
      $ratings = [
        5 => ['label' => 'Excellent (5)', 'count' => (int)$s['cnt_5'], 'color' => '#1e7c3b'],
        4 => ['label' => 'Good (4)',      'count' => (int)$s['cnt_4'], 'color' => '#1a6fbf'],
        3 => ['label' => 'Average (3)',   'count' => (int)$s['cnt_3'], 'color' => '#b06c10'],
        2 => ['label' => 'Poor (2)',      'count' => (int)$s['cnt_2'], 'color' => '#c0392b'],
        1 => ['label' => 'Very Poor (1)','count' => (int)$s['cnt_1'], 'color' => '#922b21'],
      ];
      foreach ($ratings as $info):
        $p = pct($info['count'], $total);
      ?>
      <div class="rating-row">
        <div class="r-label"><?= $info['label'] ?></div>
        <div class="r-bar-wrap">
          <div class="r-bar" style="width:<?= $p ?>%;background:<?= $info['color'] ?>"></div>
        </div>
        <div class="r-count"><?= $info['count'] ?></div>
        <div class="r-pct"><?= $p ?>%</div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ══ III. SQD SCORES ══ -->
    <div class="rpt-section">
      <div class="rpt-section-title">III. Service Quality Dimensions (SQD) Scores</div>
      <table class="rpt-table sqd-table">
        <thead>
          <tr>
            <th style="width:28px">#</th>
            <th>Service Quality Dimension</th>
            <th style="text-align:center">Avg Score</th>
            <th style="text-align:center">Rating</th>
            <th style="min-width:90px;text-align:center">Performance</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($sqd_keys as $i => $key):
            $avg   = (float)($s["avg_$key"] ?? 0);
            $pv    = round($avg / 5 * 100);
            $clr   = $avg >= 4 ? '#1e7c3b' : ($avg >= 3 ? '#1a6fbf' : ($avg >= 2 ? '#b06c10' : '#c0392b'));
          ?>
          <tr>
            <td style="color:#aaa;font-size:8pt;text-align:center"><?= $i ?></td>
            <td><?= htmlspecialchars($sqd_labels[$key]) ?></td>
            <td style="text-align:center"><strong><?= number_format($avg,2) ?></strong> / 5.00</td>
            <td style="text-align:center"><?= ratingLabel($avg) ?></td>
            <td style="text-align:center">
              <div class="mini-bar-wrap" style="height:9px">
                <div class="mini-bar" style="width:<?= $pv ?>%;background:<?= $clr ?>"></div>
              </div>
            </td>
          </tr>
          <?php endforeach;
          // Overall SQD average
          $all_avgs = array_filter(array_map(fn($k) => (float)($s["avg_$k"] ?? 0), $sqd_keys));
          $overall  = count($all_avgs) > 0 ? array_sum($all_avgs) / count($all_avgs) : 0;
          $oclr     = $overall >= 4 ? '#1e7c3b' : ($overall >= 3 ? '#1a6fbf' : '#b06c10');
          ?>
          <tr class="overall-row">
            <td colspan="2" style="text-align:right;padding-right:14px;font-size:9pt">
              Overall SQD Average
            </td>
            <td style="text-align:center"><strong><?= number_format($overall,2) ?></strong> / 5.00</td>
            <td style="text-align:center"><strong><?= ratingLabel($overall) ?></strong></td>
            <td style="text-align:center">
              <div class="mini-bar-wrap" style="height:9px">
                <div class="mini-bar" style="width:<?= round($overall/5*100) ?>%;background:<?= $oclr ?>"></div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ══ IV. DEPARTMENT BREAKDOWN ══ -->
    <?php if (!empty($depts)): ?>
    <div class="rpt-section">
      <div class="rpt-section-title">IV. Performance by Department / Office</div>
      <table class="rpt-table dept-table">
        <thead>
          <tr>
            <th>Department / Office</th>
            <th>Head</th>
            <th>Responses</th>
            <th>Avg Rating</th>
            <th>SQD Avg</th>
            <th>Satisfaction</th>
            <th>Performance</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($depts as $d):
            $sat     = (float)$d['sat_rate'];
            $pillCls = $sat >= 80 ? 'high' : ($sat >= 60 ? 'mid' : ($sat >= 40 ? 'low' : 'vlow'));
            $barClr  = $sat >= 80 ? '#1e7c3b' : ($sat >= 60 ? '#1a6fbf' : ($sat >= 40 ? '#b06c10' : '#c0392b'));
            $d_sqd   = deptSqdAvg($d, $sqd_keys);
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($d['dept_name']) ?></strong></td>
            <td style="font-size:8.5pt;color:#555"><?= htmlspecialchars($d['dept_head'] ?? '—') ?></td>
            <td><?= number_format($d['total']) ?></td>
            <td><?= $d['avg_rating'] ?>&nbsp;<?= stars($d['avg_rating']) ?></td>
            <td><?= number_format($d_sqd, 2) ?></td>
            <td><span class="sat-pill <?= $pillCls ?>"><?= $sat ?>%</span></td>
            <td style="min-width:65px">
              <div class="mini-bar-wrap">
                <div class="mini-bar" style="width:<?= $sat ?>%;background:<?= $barClr ?>"></div>
              </div>
            </td>
            <td style="font-size:8.5pt;color:#555"><?= ratingLabel($d['avg_rating']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- ══ V. INDIVIDUAL FEEDBACK (optional) ══ -->
    <?php if ($incl_raw && !empty($feedbacks)): ?>
    <div class="rpt-section page-break">
      <div class="rpt-section-title">V. Individual Feedback Records</div>
      <table class="rpt-table feedback-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Department</th>
            <th>Date</th>
            <th>Respondent Type</th>
            <th>Sex</th>
            <th>Age Group</th>
            <th>Rating</th>
            <?php if ($incl_comments): ?>
            <th>Comment</th>
            <th>Suggestions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($feedbacks as $i => $f): ?>
          <tr>
            <td style="color:#aaa"><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($f['dept_name']) ?></td>
            <td style="white-space:nowrap;color:#666"><?= htmlspecialchars($f['submitted_at']) ?></td>
            <td style="text-transform:capitalize">
              <?= htmlspecialchars(str_replace('_', ' ', $f['respondent_type'] ?? '—')) ?>
            </td>
            <td style="text-transform:capitalize"><?= htmlspecialchars($f['sex'] ?? '—') ?></td>
            <td><?= htmlspecialchars(str_replace('_', ' ', $f['age_group'] ?? '—')) ?></td>
            <td style="white-space:nowrap">
              <?= stars($f['rating']) ?>
              <span style="color:#888;font-size:8pt">(<?= $f['rating'] ?>/5)</span>
            </td>
            <?php if ($incl_comments): ?>
            <td style="max-width:140px;font-size:8pt;color:#444">
              <?= htmlspecialchars($f['comment'] ?? '—') ?>
            </td>
            <td style="max-width:140px;font-size:8pt;color:#444">
              <?= htmlspecialchars($f['suggestions'] ?? '—') ?>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- ══ CERTIFICATION ══ -->
    <div class="rpt-section">
      <?php
      // Dynamic section number
      $sec = 5; // I–IV always present
      if ($incl_raw && !empty($feedbacks)) $sec = 6;
      $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI'];
      ?>
      <div class="rpt-section-title"><?= $roman[$sec] ?>. Certification</div>
      <p class="narrative" style="margin-bottom:32px">
        This Client Satisfaction Measurement Report is hereby certified to have been prepared
        in accordance with Republic Act No. 11032 (<em>Ease of Doing Business and Efficient
        Government Service Delivery Act of 2018</em>) and the guidelines of the
        Anti-Red Tape Authority (ARTA) on the conduct of client satisfaction surveys.
        All data presented herein are based on feedback collected through the
        <strong>LGU-Connect Citizen Feedback System</strong> of the Local Government Unit
        of San Julian, Eastern Samar.
      </p>

      <div class="sig-block">
        <div class="sig-item">
          <div style="height:52px"></div>
          <div class="sig-line"></div>
          <div class="sig-name">Prepared By</div>
          <div class="sig-title">Records Officer / System Administrator</div>
        </div>
        <div class="sig-item">
          <div style="height:52px"></div>
          <div class="sig-line"></div>
          <div class="sig-name">Noted By</div>
          <div class="sig-title">Municipal Mayor</div>
        </div>
      </div>
    </div>

    <div class="rpt-footer">
      LGU-Connect Client Satisfaction Measurement System &nbsp;|&nbsp;
      Municipality of San Julian, Eastern Samar &nbsp;|&nbsp;
      Generated on <?= $generated_on ?>
    </div>

  </div><!-- /report-paper -->
</div>
</body>
</html>