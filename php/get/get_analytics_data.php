<?php

require "../auth_check.php";
require "../dbconnect.php";
requireSuperAdmin();

header('Content-Type: application/json');

// Official ARTA SQD wording
$csm = include __DIR__ . '/../config/csm_questions.php';

$period    = $_POST['period']    ?? 'this_month';
$dept_code = (isset($_POST['dept_id']) && $_POST['dept_id'] !== '') ? $_POST['dept_id'] : null;

// ── Compute date range from period ──
$now = new DateTime();
switch ($period) {
    case 'today':
        $from = $now->format('Y-m-d');
        $to   = $now->format('Y-m-d');
        break;
    case 'this_week':
        // Explicit ISO day-of-week arithmetic instead of 'monday this week' /
        // 'sunday this week' relative-date strings, which behave inconsistently
        // specifically when TODAY itself is a Sunday (the same class of bug
        // found in the CSMR Generator's JS date-range calculation).
        $dow    = (int)$now->format('N'); // 1 (Monday) ... 7 (Sunday)
        $monday = (clone $now)->modify('-' . ($dow - 1) . ' days');
        $sunday = (clone $now)->modify('+' . (7 - $dow) . ' days');
        $from   = $monday->format('Y-m-d');
        $to     = $sunday->format('Y-m-d');
        break;
    case 'last_month':
        $from = (clone $now)->modify('first day of last month')->format('Y-m-d');
        $to   = (new DateTime('last day of last month'))->format('Y-m-d');
        break;
    case 'this_quarter':
        $q    = ceil((int)date('n') / 3);
        $from = date('Y-m-d', mktime(0,0,0,($q-1)*3+1,1));
        $to   = date('Y-m-d', mktime(0,0,0,$q*3+1,0));
        break;
    case 'this_year':
        $from = date('Y-01-01');
        $to   = date('Y-12-31');
        break;
    case 'this_month':
    default:
        $from = date('Y-m-01');
        $to   = date('Y-m-t');
        break;
}

$where  = "WHERE DATE(f.submitted_at) BETWEEN :from AND :to";
$params = [':from' => $from, ':to' => $to];
if ($dept_code) {
    $where             .= " AND f.department_code = :dept_code";
    $params[':dept_code'] = $dept_code;
}

// ── Resolve SQD labels: use the filtered department's channel if one is
// selected, otherwise default to 'onsite'. Report/dashboard is always
// shown in English since this is an internal admin tool, not the
// citizen-facing form or the printed CSMR (which has its own language toggle).
$channel = 'onsite';
if ($dept_code) {
    $chStmt = $conn->prepare("SELECT channel FROM departments WHERE code = ? LIMIT 1");
    $chStmt->execute([$dept_code]);
    $chRow = $chStmt->fetch(PDO::FETCH_ASSOC);
    if ($chRow && !empty($chRow['channel'])) {
        $channel = $chRow['channel'];
    }
}
$sqd_text = $csm['sqd']['en'][$channel] ?? $csm['sqd']['en']['onsite'];
$sqd_keys = ['sqd0','sqd1','sqd2','sqd3','sqd4','sqd5','sqd6','sqd7','sqd8'];
$sqd_labels = [];
foreach ($sqd_keys as $i => $key) {
    $sqd_labels[$key] = 'SQD' . $i . ' — ' . $sqd_text[$key];
}

try {

    // ── 1. KPI Summary ──
    // NOTE: AVG() ignores NULL automatically, so N/A SQD answers are
    // already correctly excluded here without extra handling.
    $stmt = $conn->prepare("
        SELECT
            COUNT(*)                                                            AS total_responses,
            ROUND(AVG(f.rating), 2)                                            AS avg_rating,
            ROUND(SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(*), 0), 1)                                    AS satisfaction_rate,
            COUNT(DISTINCT f.department_code)                                  AS dept_count,
            SUM(CASE WHEN f.rating = 5 THEN 1 ELSE 0 END)                     AS cnt_5,
            SUM(CASE WHEN f.rating = 4 THEN 1 ELSE 0 END)                     AS cnt_4,
            SUM(CASE WHEN f.rating = 3 THEN 1 ELSE 0 END)                     AS cnt_3,
            SUM(CASE WHEN f.rating = 2 THEN 1 ELSE 0 END)                     AS cnt_2,
            SUM(CASE WHEN f.rating = 1 THEN 1 ELSE 0 END)                     AS cnt_1,
            ROUND(AVG(f.sqd0),2) AS avg_sqd0, ROUND(AVG(f.sqd1),2) AS avg_sqd1,
            ROUND(AVG(f.sqd2),2) AS avg_sqd2, ROUND(AVG(f.sqd3),2) AS avg_sqd3,
            ROUND(AVG(f.sqd4),2) AS avg_sqd4, ROUND(AVG(f.sqd5),2) AS avg_sqd5,
            ROUND(AVG(f.sqd6),2) AS avg_sqd6, ROUND(AVG(f.sqd7),2) AS avg_sqd7,
            ROUND(AVG(f.sqd8),2) AS avg_sqd8
        FROM feedback f $where
    ");
    $stmt->execute($params);
    $kpi = $stmt->fetch(PDO::FETCH_ASSOC);

    // ── 2. Feedback trend (daily counts) ──
    $stmt2 = $conn->prepare("
        SELECT
            DATE(f.submitted_at)                AS day,
            COUNT(*)                            AS total,
            ROUND(AVG(f.rating), 2)             AS avg_rating,
            SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) AS satisfied
        FROM feedback f $where
        GROUP BY DATE(f.submitted_at)
        ORDER BY day ASC
    ");
    $stmt2->execute($params);
    $trend = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // ── 3. Per-department stats ──
    $stmt3 = $conn->prepare("
        SELECT
            COALESCE(d.name, f.department_code) AS dept_name,
            f.department_code,
            COUNT(f.id)                         AS total,
            ROUND(AVG(f.rating), 2)             AS avg_rating,
            ROUND(SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(f.id), 0), 1)  AS satisfaction_rate
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        $where
        GROUP BY f.department_code, d.name
        ORDER BY total DESC
    ");
    $stmt3->execute($params);
    $by_dept = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // ── 4. Respondent type breakdown ──
    $stmt4 = $conn->prepare("
        SELECT
            respondent_type,
            COUNT(*) AS total
        FROM feedback f $where
        GROUP BY respondent_type
        ORDER BY total DESC
    ");
    $stmt4->execute($params);
    $by_type = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. Sex breakdown ──
    $stmt5 = $conn->prepare("
        SELECT sex, COUNT(*) AS total
        FROM feedback f $where
        GROUP BY sex
    ");
    $stmt5->execute($params);
    $by_sex = $stmt5->fetchAll(PDO::FETCH_ASSOC);

    // ── 6. Age group breakdown ──
    $stmt6 = $conn->prepare("
        SELECT age_group, COUNT(*) AS total
        FROM feedback f $where
        GROUP BY age_group
        ORDER BY FIELD(age_group,'below_18','18_30','31_45','46_60','above_60')
    ");
    $stmt6->execute($params);
    $by_age = $stmt6->fetchAll(PDO::FETCH_ASSOC);

    // ── 7. Recent comments ──
    // FIXED: removed a leftover duplicate/invalid prepare() call that
    // appended a second "WHERE" on top of $where (which already starts
    // with WHERE), producing invalid SQL. Only the corrected version
    // (using AND, via str_replace on $where) remains below.
    $stmt7 = $conn->prepare("
        SELECT
            COALESCE(d.name, f.department_code) AS dept_name,
            f.rating,
            f.comment,
            f.respondent_type,
            DATE_FORMAT(f.submitted_at, '%b %d, %Y') AS submitted_at
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        " . str_replace('WHERE', 'WHERE (f.comment IS NOT NULL AND f.comment != \'\') AND', $where) . "
        ORDER BY f.submitted_at DESC
        LIMIT 8
    ");
    $stmt7->execute($params);
    $recent_comments = $stmt7->fetchAll(PDO::FETCH_ASSOC);

    // ── 8. Citizen's Charter (CC1-3) distribution (NEW) ──
    $ccStmt = $conn->prepare("
        SELECT
            SUM(CASE WHEN f.cc1 = 1 THEN 1 ELSE 0 END) AS cc1_1,
            SUM(CASE WHEN f.cc1 = 2 THEN 1 ELSE 0 END) AS cc1_2,
            SUM(CASE WHEN f.cc1 = 3 THEN 1 ELSE 0 END) AS cc1_3,
            SUM(CASE WHEN f.cc1 = 4 THEN 1 ELSE 0 END) AS cc1_4,
            SUM(CASE WHEN f.cc1 IN (1,2,3) THEN 1 ELSE 0 END) AS cc1_aware_total,
            SUM(CASE WHEN f.cc2 = 1 THEN 1 ELSE 0 END) AS cc2_1,
            SUM(CASE WHEN f.cc2 = 2 THEN 1 ELSE 0 END) AS cc2_2,
            SUM(CASE WHEN f.cc2 = 3 THEN 1 ELSE 0 END) AS cc2_3,
            SUM(CASE WHEN f.cc2 = 4 THEN 1 ELSE 0 END) AS cc2_4,
            SUM(CASE WHEN f.cc2 = 5 THEN 1 ELSE 0 END) AS cc2_5,
            SUM(CASE WHEN f.cc3 = 1 THEN 1 ELSE 0 END) AS cc3_1,
            SUM(CASE WHEN f.cc3 = 2 THEN 1 ELSE 0 END) AS cc3_2,
            SUM(CASE WHEN f.cc3 = 3 THEN 1 ELSE 0 END) AS cc3_3,
            SUM(CASE WHEN f.cc3 = 4 THEN 1 ELSE 0 END) AS cc3_4
        FROM feedback f $where
    ");
    $ccStmt->execute($params);
    $ccRow = $ccStmt->fetch(PDO::FETCH_ASSOC);

    $cc_text  = $csm['cc']['en'];
    $totalAll = max((int)$kpi['total_responses'], 1);
    $totalAware = max((int)($ccRow['cc1_aware_total'] ?? 0), 1);

    function buildCCOptionData($ccKey, $ccText, $row, $base) {
        $out = [];
        foreach ($ccText[$ccKey]['options'] as $num => $label) {
            $count = (int)($row["{$ccKey}_{$num}"] ?? 0);
            $out[] = [
                'num'   => $num,
                'label' => $label,
                'count' => $count,
                'pct'   => round($count / $base * 100, 1),
            ];
        }
        return $out;
    }

    $cc_data = [
        'cc1' => [
            'question' => $cc_text['cc1']['question'],
            'options'  => buildCCOptionData('cc1', $cc_text, $ccRow, $totalAll),
        ],
        'cc2' => [
            'question' => $cc_text['cc2']['question'],
            'options'  => buildCCOptionData('cc2', $cc_text, $ccRow, $totalAware),
        ],
        'cc3' => [
            'question' => $cc_text['cc3']['question'],
            'options'  => buildCCOptionData('cc3', $cc_text, $ccRow, $totalAware),
        ],
        'aware_pct' => round($totalAware / $totalAll * 100, 1),
    ];

    echo json_encode([
        'success'         => true,
        'period'          => ['from' => $from, 'to' => $to],
        'kpi'             => $kpi,
        'sqd_labels'      => $sqd_labels,
        'cc_data'         => $cc_data,
        'trend'           => $trend,
        'by_dept'         => $by_dept,
        'by_type'         => $by_type,
        'by_sex'          => $by_sex,
        'by_age'          => $by_age,
        'recent_comments' => $recent_comments,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}