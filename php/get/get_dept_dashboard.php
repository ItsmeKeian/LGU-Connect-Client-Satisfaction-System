<?php

require "../auth_check.php";
require "../dbconnect.php";
requireDeptUser();

header('Content-Type: application/json');

$dept_code = CURRENT_DEPT;

if (empty($dept_code)) {
    echo json_encode(['success' => false, 'message' => 'No department assigned to this account.']);
    exit;
}

// Official ARTA SQD wording (fixes the 8th mismatched hardcoded label set
// found in this codebase — js/department/dashboard.js's renderSQD).
// Unlike the system-wide admin dashboard, we know exactly which department
// this is, so we resolve ITS actual channel for accurate wording.
$csm = include __DIR__ . '/../config/csm_questions.php';
$channel = 'onsite';
$chStmt = $conn->prepare("SELECT channel FROM departments WHERE code = ? LIMIT 1");
$chStmt->execute([$dept_code]);
$chRow = $chStmt->fetch(PDO::FETCH_ASSOC);
if ($chRow && !empty($chRow['channel'])) {
    $channel = $chRow['channel'];
}
$sqd_labels = $csm['sqd']['en'][$channel] ?? $csm['sqd']['en']['onsite'];

try {

    // ── 1. KPI Summary ──
    $kpi = $conn->prepare("
        SELECT
            COUNT(*)                                                            AS total,
            ROUND(AVG(rating), 2)                                              AS avg_rating,
            ROUND(SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(*), 0), 1)                                    AS satisfaction_rate,
            SUM(CASE WHEN MONTH(submitted_at) = MONTH(NOW())
                      AND YEAR(submitted_at)  = YEAR(NOW())
                 THEN 1 ELSE 0 END)                                            AS this_month,
            SUM(CASE WHEN DATE(submitted_at) = CURDATE()
                 THEN 1 ELSE 0 END)                                            AS today
        FROM feedback
        WHERE department_code = ?
    ");
    $kpi->execute([$dept_code]);
    $summary = $kpi->fetch(PDO::FETCH_ASSOC);

    // ── 2. Rating distribution ──
    $distStmt = $conn->prepare("
        SELECT rating, COUNT(*) AS cnt
        FROM feedback
        WHERE department_code = ?
        GROUP BY rating
    ");
    $distStmt->execute([$dept_code]);
    $rating_dist = ['1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0];
    foreach ($distStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rating_dist[$r['rating']] = (int)$r['cnt'];
    }

    // ── 3. Monthly trend (last 6 months) ──
    $trendStmt = $conn->prepare("
        SELECT
            DATE_FORMAT(submitted_at, '%b %Y') AS month_label,
            DATE_FORMAT(submitted_at, '%Y-%m') AS month_key,
            COUNT(*)                           AS total,
            ROUND(AVG(rating), 2)              AS avg_rating,
            ROUND(SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(*), 0), 1)    AS satisfaction_rate
        FROM feedback
        WHERE department_code = ?
          AND submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    ");
    $trendStmt->execute([$dept_code]);
    $trend = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 4. Daily volume (last 14 days) ──
    $volumeStmt = $conn->prepare("
        SELECT
            DATE_FORMAT(submitted_at, '%b %d') AS day_label,
            DATE(submitted_at)                 AS day_key,
            COUNT(*)                           AS total
        FROM feedback
        WHERE department_code = ?
          AND submitted_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
        GROUP BY day_key, day_label
        ORDER BY day_key ASC
    ");
    $volumeStmt->execute([$dept_code]);
    $volume = $volumeStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. SQD averages ──
    $sqdStmt = $conn->prepare("
        SELECT
            ROUND(AVG(sqd0),2) AS sqd0, ROUND(AVG(sqd1),2) AS sqd1,
            ROUND(AVG(sqd2),2) AS sqd2, ROUND(AVG(sqd3),2) AS sqd3,
            ROUND(AVG(sqd4),2) AS sqd4, ROUND(AVG(sqd5),2) AS sqd5,
            ROUND(AVG(sqd6),2) AS sqd6, ROUND(AVG(sqd7),2) AS sqd7,
            ROUND(AVG(sqd8),2) AS sqd8
        FROM feedback
        WHERE department_code = ?
    ");
    $sqdStmt->execute([$dept_code]);
    $sqd = $sqdStmt->fetch(PDO::FETCH_ASSOC);

    // ── 6. Recent feedback (5 latest) ──
    $recentStmt = $conn->prepare("
        SELECT
            rating, comment, respondent_type, sex, age_group,
            DATE_FORMAT(submitted_at, '%b %d, %Y') AS submitted_at
        FROM feedback
        WHERE department_code = ?
        ORDER BY submitted_at DESC
        LIMIT 5
    ");
    $recentStmt->execute([$dept_code]);
    $recent = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data'    => [
            'total'             => (int)($summary['total'] ?? 0),
            'avg_rating'        => $summary['avg_rating']        ?? '0.00',
            'satisfaction_rate' => $summary['satisfaction_rate'] ?? '0.0',
            'this_month'        => (int)($summary['this_month']  ?? 0),
            'today'             => (int)($summary['today']       ?? 0),
            'rating_dist'       => $rating_dist,
            'trend'             => $trend,
            'volume'            => $volume,
            'sqd'               => $sqd,
            'sqd_labels'        => $sqd_labels, // NEW — official ARTA question text, keyed sqd0..sqd8
            'recent'            => $recent,
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}