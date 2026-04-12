<?php
require "../auth_check.php";
require "../dbconnect.php";
header('Content-Type: application/json');

// ── Auth: superadmin only ──
if ($_SESSION['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id        = intval($_POST['id']         ?? 0);
$full_name = trim($_POST['full_name']    ?? '');
$username  = trim($_POST['username']     ?? '');
$email     = trim($_POST['email']        ?? '');
$password  = trim($_POST['password']     ?? '');
$role      = trim($_POST['role']         ?? 'dept_user');
$dept      = trim($_POST['department']   ?? '') ?: null;

// ── Validation ──
if (!$full_name || !$username || !$email) {
    echo json_encode(['success' => false, 'message' => 'Name, username, and email are required.']);
    exit();
}

if ($role === 'dept_user' && !$dept) {
    echo json_encode(['success' => false, 'message' => 'Please assign a department for this user.']);
    exit();
}

// superadmin has no department
if ($role === 'superadmin') $dept = null;

try {
    if ($id) {
        // ── UPDATE ──
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                UPDATE users 
                SET full_name=?, username=?, email=?, password=?, role=?, department=?
                WHERE id=?
            ");
            $stmt->execute([$full_name, $username, $email, $hashed, $role, $dept, $id]);
        } else {
            // Don't update password if left blank
            $stmt = $conn->prepare("
                UPDATE users 
                SET full_name=?, username=?, email=?, role=?, department=?
                WHERE id=?
            ");
            $stmt->execute([$full_name, $username, $email, $role, $dept, $id]);
        }
    } else {
        // ── INSERT ──
        if (!$password) {
            echo json_encode(['success' => false, 'message' => 'Password is required for new users.']);
            exit();
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO users (full_name, username, email, password, role, department)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$full_name, $username, $email, $hashed, $role, $dept]);
    }

    echo json_encode(['success' => true, 'message' => 'User saved successfully.']);

} catch (Exception $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
    } else {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}