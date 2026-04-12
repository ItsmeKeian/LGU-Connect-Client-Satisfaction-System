<?php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');


if ($_SESSION['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit();
}

// Prevent self-deletion
if ($id === intval($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}