<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

define('IS_SUPERADMIN', $_SESSION['role'] === 'superadmin');
define('IS_DEPT_USER',  $_SESSION['role'] === 'dept_user');
define('CURRENT_DEPT',  $_SESSION['department'] ?? '');
define('CURRENT_USER',  $_SESSION['name'] ?? 'User');

// Call this at top of admin-only pages
function requireSuperAdmin() {
    if ($_SESSION['role'] !== 'superadmin') {
        header('Location: ../dept/dept_dashboard.php');
        exit();
    }
}

// Call this at top of dept-only pages
function requireDeptUser() {
    if ($_SESSION['role'] !== 'dept_user') {
        header('Location: ../admin/admin_dashboard.php');
        exit();
    }
}