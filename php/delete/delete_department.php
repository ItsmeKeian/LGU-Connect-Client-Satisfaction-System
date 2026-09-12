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
    // Get dept info for confirmation message
    $check = $conn->prepare("SELECT name, code FROM departments WHERE id = ?");
    $check->execute([$id]);
    $dept = $check->fetch(PDO::FETCH_ASSOC);

    if (!$dept) {
        echo json_encode(['success' => false, 'message' => 'Department not found.']);
        exit();
    }

    // ── Block deletion if this department has feedback history ──
    // feedback.department_code is a plain string reference, not a foreign
    // key, so deleting the department row would silently orphan its
    // feedback: the rows stay in the database but permanently lose their
    // department name, head, and channel (needed to resolve correct SQD
    // wording in reports going forward). This breaks the completeness
    // guarantee official CSMR/ARTA reporting relies on.
    // Deactivating (status = 'inactive') achieves the same practical goal
    // — the department stops appearing as selectable for new feedback —
    // without destroying historical context.
    $fbCheck = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE department_code = ?");
    $fbCheck->execute([$dept['code']]);
    $feedbackCount = (int)$fbCheck->fetchColumn();

    if ($feedbackCount > 0) {
        echo json_encode([
            'success' => false,
            'message' => "Cannot delete '{$dept['name']}' — it has {$feedbackCount} feedback record(s). "
                       . "Set it to Inactive instead to preserve historical data and report accuracy."
        ]);
        exit();
    }

    // Safe to delete — no feedback history exists for this department
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => "Department '{$dept['name']}' has been deleted."
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}