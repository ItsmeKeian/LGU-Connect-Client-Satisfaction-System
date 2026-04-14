<?php

require "../auth_check.php";
require "../dbconnect.php";
requireSuperAdmin();

header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT setting_key, setting_value, setting_group FROM settings ORDER BY id ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert to key => value map
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    echo json_encode(['success' => true, 'settings' => $settings]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}