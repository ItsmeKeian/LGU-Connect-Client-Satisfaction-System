<?php
require "../auth_check.php";
require "../dbconnect.php";
requireSuperAdmin();

header('Content-Type: application/json');

try {

    // ── 1. Overall KPIs ──
    $kpi = $conn->query("
        SELECT
            COUNT(*)                                                            AS total_feedback,
            ROUND(AVG(rating), 2)                                              AS avg_rating,
            ROUND(SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(*), 0), 1)                                    AS satisfaction_rate
        FROM feedback
    ")->fetch(PDO::FETCH_ASSOC);

    // ── 2. Active departments count ──
    $active_depts = $conn->query("
        SELECT COUNT(*) FROM departments WHERE status = 'active'
    ")->fetchColumn();

    // ── 3. This month vs last month (for change indicator) ──
    $this_month = $conn->query("
        SELECT COUNT(*) FROM feedback
        WHERE MONTH(submitted_at) = MONTH(NOW())
          AND YEAR(submitted_at)  = YEAR(NOW())
    ")->fetchColumn();

    $last_month = $conn->query("
        SELECT COUNT(*) FROM feedback
        WHERE MONTH(submitted_at) = MONTH(NOW() - INTERVAL 1 MONTH)
          AND YEAR(submitted_at)  = YEAR(NOW() - INTERVAL 1 MONTH)
    ")->fetchColumn();

    $change     = $this_month - $last_month;
    $change_pct = $last_month > 0
        ? round(abs($change) / $last_month * 100, 1)
        : ($this_month > 0 ? 100 : 0);

    // ── 4. Monthly trend (last 8 months) ──
    $trendRows = $conn->query("
        SELECT
            DATE_FORMAT(submitted_at, '%b %Y')  AS month_label,
            DATE_FORMAT(submitted_at, '%Y-%m')  AS month_key,
            COUNT(*)                            AS total,
            ROUND(AVG(rating), 2)               AS avg_rating,
            ROUND(SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(*), 0), 1)     AS satisfaction_rate
        FROM feedback
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 8 MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. Daily volume — last 14 days ──
    $volumeRows = $conn->query("
        SELECT
            DATE_FORMAT(submitted_at, '%b %d') AS day_label,
            DATE(submitted_at)                 AS day_key,
            COUNT(*)                           AS total
        FROM feedback
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
        GROUP BY day_key, day_label
        ORDER BY day_key ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ── 6. Department comparison (avg rating per dept) ──
    $deptChart = $conn->query("
        SELECT
            COALESCE(d.name, f.department_code)     AS dept_name,
            f.department_code                        AS dept_code,
            COUNT(f.id)                              AS total,
            ROUND(AVG(f.rating), 2)                 AS avg_rating,
            ROUND(SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(f.id), 0), 1)       AS satisfaction_rate
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        GROUP BY f.department_code, d.name
        ORDER BY avg_rating DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ── 7. SQD system-wide averages ──
    $sqd = $conn->query("
        SELECT
            ROUND(AVG(sqd0), 2) AS sqd0,
            ROUND(AVG(sqd1), 2) AS sqd1,
            ROUND(AVG(sqd2), 2) AS sqd2,
            ROUND(AVG(sqd3), 2) AS sqd3,
            ROUND(AVG(sqd4), 2) AS sqd4,
            ROUND(AVG(sqd5), 2) AS sqd5,
            ROUND(AVG(sqd6), 2) AS sqd6,
            ROUND(AVG(sqd7), 2) AS sqd7,
            ROUND(AVG(sqd8), 2) AS sqd8
        FROM feedback
    ")->fetch(PDO::FETCH_ASSOC);

    // ── 8. This month mini stats ──
    $monthly = $conn->query("
        SELECT
            COUNT(*)                                                            AS responses,
            ROUND(AVG(rating), 2)                                              AS avg_score,
            ROUND(SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) * 100.0
                  / NULLIF(COUNT(*), 0), 1)                                    AS satisfaction_rate
        FROM feedback
        WHERE MONTH(submitted_at) = MONTH(NOW())
          AND YEAR(submitted_at)  = YEAR(NOW())
    ")->fetch(PDO::FETCH_ASSOC);

    // Top dept this month
    $topDept = $conn->query("
        SELECT COALESCE(d.name, f.department_code) AS dept_name,
               COUNT(f.id) AS total
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        WHERE MONTH(f.submitted_at) = MONTH(NOW())
          AND YEAR(f.submitted_at)  = YEAR(NOW())
        GROUP BY f.department_code, d.name
        ORDER BY total DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    // ── 9. Recent feedback (5 latest) ──
    $recent = $conn->query("
        SELECT
            COALESCE(d.name, f.department_code)         AS dept_name,
            f.department_code,
            f.rating,
            f.comment,
            f.respondent_type,
            f.sex,
            DATE_FORMAT(f.submitted_at, '%b %d, %Y')   AS submitted_at
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        ORDER BY f.submitted_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            // KPIs
            'total_feedback'    => (int)$kpi['total_feedback'],
            'avg_rating'        => number_format((float)$kpi['avg_rating'], 2),
            'satisfaction_rate' => $kpi['satisfaction_rate'] . '%',
            'active_depts'      => (int)$active_depts,
            'pending_reports'   => 0, // placeholder

            // Change indicator
            'this_month'        => (int)$this_month,
            'last_month'        => (int)$last_month,
            'change'            => $change,
            'change_pct'        => $change_pct,

            // Charts
            'trend'             => $trendRows,
            'volume'            => $volumeRows,
            'dept_chart'        => $deptChart,
            'sqd'               => $sqd,

            // Monthly mini stats
            'monthly'           => [
                'responses'         => (int)$monthly['responses'],
                'avg_score'         => number_format((float)$monthly['avg_score'], 2),
                'satisfaction_rate' => $monthly['satisfaction_rate'],
                'top_dept'          => $topDept['dept_name'] ?? '—',
            ],

            // Recent feedback
            'recent_feedback'   => $recent,
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}