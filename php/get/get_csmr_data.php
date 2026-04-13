<?php
require "../auth_check.php";
require "../dbconnect.php";   // provides $conn
requireSuperAdmin();

header('Content-Type: application/json');

// ── Inputs ──
$dept_code = (isset($_POST['dept_id']) && $_POST['dept_id'] !== '') ? $_POST['dept_id'] : null;
$date_from = $_POST['date_from'] ?? date('Y-m-01');
$date_to   = $_POST['date_to']   ?? date('Y-m-t');
$incl_dept = (int)($_POST['incl_dept'] ?? 1);
$incl_raw  = (int)($_POST['incl_raw']  ?? 0);

$date_from_dt = date('Y-m-d', strtotime($date_from));
$date_to_dt   = date('Y-m-d', strtotime($date_to));
$period_label = date('M j, Y', strtotime($date_from_dt)) . ' – ' . date('M j, Y', strtotime($date_to_dt));

// ── WHERE clause ──
$where  = "WHERE DATE(f.submitted_at) BETWEEN :from AND :to";
$params = [':from' => $date_from_dt, ':to' => $date_to_dt];

if ($dept_code) {
    $where              .= " AND f.department_code = :dept_code";
    $params[':dept_code'] = $dept_code;
}

try {

    // ── 1. Overall Summary ──
    $stmt = $conn->prepare("
        SELECT
            COUNT(*)                                                            AS total_responses,
            ROUND(AVG(f.rating), 2)                                            AS avg_rating,
            ROUND(
                SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) * 100.0
                / NULLIF(COUNT(*), 0), 1
            )                                                                  AS satisfaction_rate,
            COUNT(DISTINCT f.department_code)                                  AS dept_count,

            /* Rating distribution */
            SUM(CASE WHEN f.rating = 5 THEN 1 ELSE 0 END)                     AS cnt_5,
            SUM(CASE WHEN f.rating = 4 THEN 1 ELSE 0 END)                     AS cnt_4,
            SUM(CASE WHEN f.rating = 3 THEN 1 ELSE 0 END)                     AS cnt_3,
            SUM(CASE WHEN f.rating = 2 THEN 1 ELSE 0 END)                     AS cnt_2,
            SUM(CASE WHEN f.rating = 1 THEN 1 ELSE 0 END)                     AS cnt_1,

            /* SQD averages */
            ROUND(AVG(f.sqd0), 2)  AS avg_sqd0,
            ROUND(AVG(f.sqd1), 2)  AS avg_sqd1,
            ROUND(AVG(f.sqd2), 2)  AS avg_sqd2,
            ROUND(AVG(f.sqd3), 2)  AS avg_sqd3,
            ROUND(AVG(f.sqd4), 2)  AS avg_sqd4,
            ROUND(AVG(f.sqd5), 2)  AS avg_sqd5,
            ROUND(AVG(f.sqd6), 2)  AS avg_sqd6,
            ROUND(AVG(f.sqd7), 2)  AS avg_sqd7,
            ROUND(AVG(f.sqd8), 2)  AS avg_sqd8,

            /* Respondent type breakdown */
            SUM(CASE WHEN f.respondent_type = 'citizen'        THEN 1 ELSE 0 END) AS cnt_citizen,
            SUM(CASE WHEN f.respondent_type = 'employee'       THEN 1 ELSE 0 END) AS cnt_employee,
            SUM(CASE WHEN f.respondent_type = 'business_owner' THEN 1 ELSE 0 END) AS cnt_business,
            SUM(CASE WHEN f.respondent_type = 'other'          THEN 1 ELSE 0 END) AS cnt_other
        FROM feedback f
        $where
    ");
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    $summary['period_label']      = $period_label;
    $summary['satisfaction_rate'] = $summary['satisfaction_rate'] ?? '0.0';
    $summary['avg_rating']        = $summary['avg_rating']        ?? '0.00';

    // No data — return early
    if ((int)$summary['total_responses'] === 0) {
        echo json_encode([
            'success'     => true,
            'summary'     => [
                'total_responses'   => 0,
                'avg_rating'        => '0.00',
                'satisfaction_rate' => '0.0',
                'dept_count'        => 0,
                'period_label'      => $period_label,
            ],
            'departments' => [],
            'feedbacks'   => [],
        ]);
        exit;
    }

    // ── 2. Per-Department Breakdown ──
    $departments = [];
    if ($incl_dept) {
        $stmt2 = $conn->prepare("
            SELECT
                f.department_code                                               AS dept_id,
                COALESCE(d.name, f.department_code)                            AS dept_name,
                d.head                                                         AS dept_head,
                COUNT(f.id)                                                     AS total_responses,
                ROUND(AVG(f.rating), 2)                                        AS avg_rating,
                ROUND(
                    SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) * 100.0
                    / NULLIF(COUNT(f.id), 0), 1
                )                                                              AS satisfaction_rate,
                ROUND(AVG(f.sqd0), 2)  AS avg_sqd0,
                ROUND(AVG(f.sqd1), 2)  AS avg_sqd1,
                ROUND(AVG(f.sqd2), 2)  AS avg_sqd2,
                ROUND(AVG(f.sqd3), 2)  AS avg_sqd3,
                ROUND(AVG(f.sqd4), 2)  AS avg_sqd4,
                ROUND(AVG(f.sqd5), 2)  AS avg_sqd5,
                ROUND(AVG(f.sqd6), 2)  AS avg_sqd6,
                ROUND(AVG(f.sqd7), 2)  AS avg_sqd7,
                ROUND(AVG(f.sqd8), 2)  AS avg_sqd8
            FROM feedback f
            LEFT JOIN departments d ON d.code = f.department_code
            $where
            GROUP BY f.department_code, d.name, d.head
            ORDER BY total_responses DESC
        ");
        $stmt2->execute($params);
        $departments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── 3. Raw Feedback Records ──
    $feedbacks = [];
    if ($incl_raw) {
        $stmt3 = $conn->prepare("
            SELECT
                f.id,
                COALESCE(d.name, f.department_code)         AS dept_name,
                f.department_code,
                f.rating,
                f.comment,
                f.suggestions,
                f.respondent_type,
                f.sex,
                f.age_group,
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

    echo json_encode([
        'success'     => true,
        'summary'     => $summary,
        'departments' => $departments,
        'feedbacks'   => $feedbacks,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}