<?php
// php/ajax/get_departments.php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

try {
    // Get departments WITH feedback summary joined
    $stmt = $conn->query("
        SELECT
            d.*,
            COUNT(f.id)       AS feedback_count,
            AVG(f.rating)     AS avg_rating
        FROM departments d
        LEFT JOIN feedback f ON f.department_code = d.code
        GROUP BY d.id
        ORDER BY d.name ASC
    ");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Round avg_rating to 2 decimal places
    foreach ($departments as &$dept) {
        $dept['avg_rating'] = $dept['avg_rating']
            ? round((float)$dept['avg_rating'], 2)
            : null;
        $dept['feedback_count'] = (int)$dept['feedback_count'];
    }

    echo json_encode(['success' => true, 'data' => $departments]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}