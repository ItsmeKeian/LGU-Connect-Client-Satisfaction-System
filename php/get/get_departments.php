<?php
// php/ajax/save_department.php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$id   = trim($_POST['id']   ?? '');
$name = trim($_POST['name'] ?? '');
$code = strtoupper(trim($_POST['code'] ?? ''));
$status = $_POST['status'] ?? 'active';
$description = trim($_POST['description'] ?? '');
$head = trim($_POST['head'] ?? '');

if (empty($name) || empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Name and code are required.']);
    exit();
}

try {
    if (!empty($id)) {
        // UPDATE existing department
        $stmt = $conn->prepare("
            UPDATE departments
            SET name=?, code=?, status=?, description=?, head=?
            WHERE id=?
        ");
        $stmt->execute([$name, $code, $status, $description, $head, $id]);
        echo json_encode(['success' => true, 'message' => "Department '{$name}' updated successfully."]);
    } else {
        // INSERT new department
        // Check for duplicate code first
        $check = $conn->prepare("SELECT id FROM departments WHERE code = ?");
        $check->execute([$code]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => "Department code '{$code}' already exists."]);
            exit();
        }

        $stmt = $conn->prepare("
            INSERT INTO departments (name, code, status, description, head)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $code, $status, $description, $head]);
        echo json_encode(['success' => true, 'message' => "Department '{$name}' added successfully."]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}