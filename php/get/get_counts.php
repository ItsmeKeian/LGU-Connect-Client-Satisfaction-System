<?php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
        SELECT
            COUNT(*)                                                        AS feedback_total,
            SUM(CASE WHEN DATE(submitted_at) = CURDATE() THEN 1 ELSE 0 END) AS feedback_today
        FROM feedback
    ");
    $stmt->execute();
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'feedback_total' => (int) $counts['feedback_total'],
        'feedback_today' => (int) $counts['feedback_today'],
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}