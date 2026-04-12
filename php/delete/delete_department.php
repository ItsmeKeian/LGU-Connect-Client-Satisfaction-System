<?php

require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$id = intval($_POST['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid department ID.']);
    exit();
}

try {
    // Get dept name for confirmation message
    $check = $conn->prepare("SELECT name FROM departments WHERE id = ?");
    $check->execute([$id]);
    $dept = $check->fetch(PDO::FETCH_ASSOC);

    if (!$dept) {
        echo json_encode(['success' => false, 'message' => 'Department not found.']);
        exit();
    }

    // Delete the department
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => "Department '{$dept['name']}' has been deleted."
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}