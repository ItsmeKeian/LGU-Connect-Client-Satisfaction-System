<?php
/**
 * LOCATION: php/get/get_feedback.php
 * Returns paginated feedback records with filters
 * Also handles CSV export
 */
require "../auth_check.php";
require "../dbconnect.php";

header('Content-Type: application/json');

$page    = max(1, intval($_GET['page']     ?? 1));
$perPage = max(1, intval($_GET['per_page'] ?? 10)); // ✅ default 10
$offset  = ($page - 1) * $perPage;

$dept   = trim($_GET['dept']   ?? '');
$rating = intval($_GET['rating'] ?? 0);
$type   = trim($_GET['type']   ?? '');
$period = trim($_GET['period'] ?? '');
$search = trim($_GET['search'] ?? '');
$export = trim($_GET['export'] ?? '');

// ── Build WHERE clause ──
$where  = ['1=1'];
$params = [];

if ($dept) {
    $where[]  = 'f.department_code = ?';
    $params[] = $dept;
}
if ($rating >= 1 && $rating <= 5) {
    $where[]  = 'f.rating = ?';
    $params[] = $rating;
}
if ($type) {
    $where[]  = 'f.respondent_type = ?';
    $params[] = $type;
}
if ($period) {
    switch ($period) {
        case 'today':
            $where[] = 'DATE(f.submitted_at) = CURDATE()'; break;
        case 'week':
            $where[] = 'YEARWEEK(f.submitted_at, 1) = YEARWEEK(CURDATE(), 1)'; break;
        case 'month':
            $where[] = 'MONTH(f.submitted_at) = MONTH(CURDATE()) AND YEAR(f.submitted_at) = YEAR(CURDATE())'; break;
        case 'quarter':
            $where[] = 'QUARTER(f.submitted_at) = QUARTER(CURDATE()) AND YEAR(f.submitted_at) = YEAR(CURDATE())'; break;
    }
}
if ($search) {
    $where[]  = '(f.comment LIKE ? OR f.suggestions LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereStr = implode(' AND ', $where);

try {

    // ── Summary stats (full dataset, respects filters) ──
    $summaryStmt = $conn->prepare("
        SELECT
            COUNT(*)                                                        AS total,
            ROUND(AVG(f.rating), 2)                                        AS avg_rating,
            SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END)                AS satisfied,
            SUM(CASE WHEN DATE(f.submitted_at) = CURDATE() THEN 1 ELSE 0 END) AS today
        FROM feedback f
        WHERE {$whereStr}
    ");
    $summaryStmt->execute($params);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    // ── CSV Export ──
    if ($export === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="feedback_export_' . date('Y-m-d') . '.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $stmt = $conn->prepare("
            SELECT
                f.id, f.department_code,
                COALESCE(d.name, f.department_code) AS dept_name,
                f.rating, f.respondent_type, f.sex, f.age_group,
                f.sqd0, f.sqd1, f.sqd2, f.sqd3, f.sqd4,
                f.sqd5, f.sqd6, f.sqd7, f.sqd8,
                f.comment, f.suggestions, f.submitted_at
            FROM feedback f
            LEFT JOIN departments d ON d.code = f.department_code
            WHERE {$whereStr}
            ORDER BY f.submitted_at DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'ID','Dept Code','Department Name','Rating','Respondent Type',
            'Sex','Age Group','SQD0','SQD1','SQD2','SQD3','SQD4',
            'SQD5','SQD6','SQD7','SQD8','Comment','Suggestions','Submitted At'
        ]);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'], $r['department_code'], $r['dept_name'], $r['rating'],
                $r['respondent_type'], $r['sex'], $r['age_group'],
                $r['sqd0'], $r['sqd1'], $r['sqd2'], $r['sqd3'], $r['sqd4'],
                $r['sqd5'], $r['sqd6'], $r['sqd7'], $r['sqd8'],
                $r['comment'], $r['suggestions'], $r['submitted_at']
            ]);
        }
        fclose($out);
        exit();
    }

    // ── Total count for pagination ──
    $countStmt = $conn->prepare("
        SELECT COUNT(*) FROM feedback f WHERE {$whereStr}
    ");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // ── Paginated data ──
    // ✅ Added LEFT JOIN to get dept_name in each row
    $dataStmt = $conn->prepare("
        SELECT
            f.*,
            COALESCE(d.name, f.department_code) AS dept_name
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        WHERE {$whereStr}
        ORDER BY f.submitted_at DESC
        LIMIT {$perPage} OFFSET {$offset}
    ");
    $dataStmt->execute($params);
    $feedback = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'  => true,
        'data'     => $feedback,
        'total'    => $total,
        'per_page' => $perPage,
        'page'     => $page,
        'summary'  => $summary,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}