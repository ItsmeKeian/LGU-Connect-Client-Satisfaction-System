<?php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

// Superadmin only
if ($_SESSION['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$page    = max(1, intval($_GET['page']     ?? 1));
$perPage = max(1, intval($_GET['per_page'] ?? 10));
$offset  = ($page - 1) * $perPage;

$search = trim($_GET['search'] ?? '');
$role   = trim($_GET['role']   ?? '');

// ── WHERE ──
$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = '(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($role) {
    $where[]  = 'u.role = ?';
    $params[] = $role;
}

$whereStr = implode(' AND ', $where);

try {
    // ── Summary (always full dataset) ──
    $summaryStmt = $conn->prepare("
        SELECT
            COUNT(*)                                              AS total,
            SUM(CASE WHEN status = 'active'     THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN role = 'superadmin'   THEN 1 ELSE 0 END) AS superadmins,
            SUM(CASE WHEN role = 'dept_user'    THEN 1 ELSE 0 END) AS dept_users
        FROM users u
    ");
    $summaryStmt->execute();
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    // ── Total for pagination ──
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM users u WHERE {$whereStr}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // ── Paginated data ──
    $dataStmt = $conn->prepare("
        SELECT u.id, u.full_name, u.username, u.email, u.role,
               u.department, u.status, u.created_at,
               d.name AS department_name
        FROM users u
        LEFT JOIN departments d ON d.code = u.department
        WHERE {$whereStr}
        ORDER BY u.created_at DESC
        LIMIT {$perPage} OFFSET {$offset}
    ");
    $dataStmt->execute($params);
    $users = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'  => true,
        'data'     => $users,
        'total'    => $total,
        'per_page' => $perPage,
        'page'     => $page,
        'summary'  => $summary,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}