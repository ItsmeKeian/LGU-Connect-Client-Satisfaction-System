<?php
session_start();
require "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$email    = trim($_POST['email']);
$password = trim($_POST['password']);

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Please fill in all fields.";
    header("Location: ../index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../index.php");
    exit();
}

// ✅ Store ALL needed session variables
session_regenerate_id(true); // security best practice
$_SESSION['user_id']    = $user['id'];
$_SESSION['role']       = $user['role'];
$_SESSION['name']       = $user['full_name'];
$_SESSION['email']      = $user['email'];
$_SESSION['department'] = $user['department']; // for multi-tenancy

// Redirect based on role
if ($user['role'] === 'superadmin') {
    header("Location: ../admin/admin_dashboard.php");
} else {
    header("Location: ../department/dept_dashboard.php");
}
exit();