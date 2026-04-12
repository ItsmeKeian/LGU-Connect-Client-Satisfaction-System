<?php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = intval($_POST['id'] ?? 0);

// Prevent self-deletion
if ($id === intval($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'User deleted.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}