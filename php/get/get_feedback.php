<?php
/**
 * LOCATION: php/get/get_feedback.php
 * Returns paginated feedback records with filters
 * Also handles CSV export
 */
require "../auth_check.php";
require "../dbconnect.php";

header('Content-Type: application/json');

// Official ARTA SQD/CC wording, keyed by language + channel
$csm = include __DIR__ . '/../config/csm_questions.php';

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

// ── Helper: resolve question text for one feedback row ──
// NOTE: uses the DEPARTMENT'S CURRENT channel, not necessarily what it
// was at the moment the citizen submitted. If a department's channel is
// changed later (onsite <-> online), older rows will display under the
// new channel's wording. Acceptable for now given the scale of this
// project, but flagging in case exact historical wording ever matters.
function attachQuestionLabels(array $row, array $csm): array {
    $lang    = in_array($row['language'] ?? '', ['en', 'tl']) ? $row['language'] : 'en';
    $channel = in_array($row['dept_channel'] ?? '', ['onsite', 'online']) ? $row['dept_channel'] : 'onsite';

    $sqdSet = $csm['sqd'][$lang][$channel] ?? $csm['sqd']['en']['onsite'];
    $ccSet  = $csm['cc'][$lang] ?? $csm['cc']['en'];

    $row['sqd_labels'] = $sqdSet; // assoc: sqd0 => "question text"

    $row['cc_display'] = [];
    foreach (['cc1', 'cc2', 'cc3'] as $ccKey) {
        $val = $row[$ccKey] ?? null;
        $row['cc_display'][$ccKey] = [
            'question' => $ccSet[$ccKey]['question'] ?? '',
            'answer'   => ($val !== null && isset($ccSet[$ccKey]['options'][$val]))
                ? $ccSet[$ccKey]['options'][$val]
                : null,
        ];
    }

    return $row;
}

try {

    // ── Summary stats (full dataset, respects filters) ──
    // NOTE: this summarizes the overall 1-5 star `rating` field, which is
    // separate from the SQD/CC scores and is never NULL, so no N/A handling
    // needed here.
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
                f.region, f.service_availed, f.email, f.language,
                f.cc1, f.cc2, f.cc3,
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
            'Sex','Age Group','Region','Service Availed','Email','Language',
            'CC1','CC2','CC3',
            'SQD0','SQD1','SQD2','SQD3','SQD4',
            'SQD5','SQD6','SQD7','SQD8','Comment','Suggestions','Submitted At'
        ]);
        foreach ($rows as $r) {
            // NULL SQD/CC (N/A) shows as blank in CSV, not "0"
            fputcsv($out, [
                $r['id'], $r['department_code'], $r['dept_name'], $r['rating'],
                $r['respondent_type'], $r['sex'], $r['age_group'],
                $r['region'], $r['service_availed'], $r['email'], $r['language'],
                $r['cc1'], $r['cc2'], $r['cc3'],
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
    // Added dept.channel so we can resolve the correct SQD wording per row.
    $dataStmt = $conn->prepare("
        SELECT
            f.*,
            COALESCE(d.name, f.department_code) AS dept_name,
            d.channel AS dept_channel
        FROM feedback f
        LEFT JOIN departments d ON d.code = f.department_code
        WHERE {$whereStr}
        ORDER BY f.submitted_at DESC
        LIMIT {$perPage} OFFSET {$offset}
    ");
    $dataStmt->execute($params);
    $feedback = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach resolved SQD/CC question text + CC answer text to each row
    $feedback = array_map(fn($row) => attachQuestionLabels($row, $csm), $feedback);

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