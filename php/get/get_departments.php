<?php
// php/get/get_departments.php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

try {
    $stmt = $conn->query("
        SELECT
            d.*,
            0 AS feedback_count,
            NULL AS avg_rating
        FROM departments d
        ORDER BY d.name ASC
    ");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($departments as &$dept) {
        $dept['avg_rating']     = null;
        $dept['feedback_count'] = 0;
    }

    echo json_encode(['success' => true, 'data' => $departments]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}