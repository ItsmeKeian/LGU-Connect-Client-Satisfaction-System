<?php

require "../dbconnect.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// ── Check if feedback is open ──
$open = $conn->query("SELECT setting_value FROM settings WHERE setting_key='feedback_open' LIMIT 1")->fetchColumn();
if ($open === '0') {
    echo json_encode(['success' => false, 'message' => 'Feedback collection is currently closed.']);
    exit;
}

// ── Sanitize inputs ──
$dept_code       = strtoupper(trim($_POST['dept_code']       ?? ''));
$respondent_type = trim($_POST['respondent_type'] ?? 'citizen');
$sex             = trim($_POST['sex']             ?? '');
$age_group       = trim($_POST['age_group']       ?? '');
$region          = trim($_POST['region']          ?? '');
$service_availed = trim($_POST['service_availed'] ?? '');
$rating          = (int)($_POST['rating']         ?? 0);
$comment         = trim($_POST['comment']         ?? '');
$suggestions     = trim($_POST['suggestions']     ?? '');
$email           = trim($_POST['email']           ?? '');
$language        = trim($_POST['language']        ?? 'en');

// CC1-3 (raw, validated below)
$cc1_raw = trim($_POST['cc1'] ?? '');
$cc2_raw = trim($_POST['cc2'] ?? '');
$cc3_raw = trim($_POST['cc3'] ?? '');

// ── SQD scores — 'na' means Not Applicable → stored as NULL ──
// Each value is either 'na' or an integer string '1'-'5'.
$sqd = [];
$sqd_errors = [];
for ($i = 0; $i <= 8; $i++) {
    $raw = trim($_POST["sqd{$i}"] ?? '');
    if ($raw === 'na') {
        $sqd[$i] = null;
    } elseif (ctype_digit($raw) && (int)$raw >= 1 && (int)$raw <= 5) {
        $sqd[$i] = (int)$raw;
    } else {
        $sqd[$i] = null;
        $sqd_errors[] = "SQD{$i} rating is required.";
    }
}

// ── Validate required fields ──
$errors = [];

if (empty($dept_code)) $errors[] = 'Department is required.';

// Accept official ARTA client types plus legacy values for backward-compat
if (!in_array($respondent_type, ['citizen', 'business', 'government', 'employee', 'business_owner', 'other'])) {
    $errors[] = 'Invalid respondent type.';
}

if (!in_array($sex, ['male', 'female', 'prefer_not_to_say'])) $errors[] = 'Sex is required.';
if (!in_array($age_group, ['below_18', '18_30', '31_45', '46_60', 'above_60'])) $errors[] = 'Age group is required.';
if (empty($region)) $errors[] = 'Region of residence is required.';
if (empty($service_availed)) $errors[] = 'Service availed is required.';
if ($rating < 1 || $rating > 5) $errors[] = 'Overall rating is required.';
if (!in_array($language, ['en', 'tl'])) $language = 'en'; // fallback, not a hard error

// Email is optional, but if given, must look valid
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email address is not valid.';
}

$errors = array_merge($errors, $sqd_errors);

// ── CC1: required, must be 1-4 ──
$cc1 = null;
if (ctype_digit($cc1_raw) && (int)$cc1_raw >= 1 && (int)$cc1_raw <= 4) {
    $cc1 = (int)$cc1_raw;
} else {
    $errors[] = 'Citizen\'s Charter awareness (CC1) is required.';
}

// ── CC2/CC3: required (1-5 / 1-4) only if CC1 indicates awareness (1-3) ──
// If CC1 = 4 (not aware), CC2/CC3 are expected blank or already auto-set
// to their "Not Applicable" option by the frontend — either is accepted.
$cc2 = null;
$cc3 = null;

$ccAware = ($cc1 !== null && $cc1 >= 1 && $cc1 <= 3);

if ($ccAware) {
    if (ctype_digit($cc2_raw) && (int)$cc2_raw >= 1 && (int)$cc2_raw <= 5) {
        $cc2 = (int)$cc2_raw;
    } else {
        $errors[] = 'CC2 answer is required.';
    }
    if (ctype_digit($cc3_raw) && (int)$cc3_raw >= 1 && (int)$cc3_raw <= 4) {
        $cc3 = (int)$cc3_raw;
    } else {
        $errors[] = 'CC3 answer is required.';
    }
} else {
    // Not aware of CC — accept whatever was sent (likely the N/A option),
    // otherwise leave as NULL. Not a validation error either way.
    if (ctype_digit($cc2_raw) && (int)$cc2_raw >= 1 && (int)$cc2_raw <= 5) $cc2 = (int)$cc2_raw;
    if (ctype_digit($cc3_raw) && (int)$cc3_raw >= 1 && (int)$cc3_raw <= 4) $cc3 = (int)$cc3_raw;
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Verify department exists ──
$deptStmt = $conn->prepare("SELECT name FROM departments WHERE code = ? AND status = 'active' LIMIT 1");
$deptStmt->execute([$dept_code]);
$dept = $deptStmt->fetch(PDO::FETCH_ASSOC);

if (!$dept) {
    echo json_encode(['success' => false, 'message' => 'Invalid or inactive department.']);
    exit;
}

// ── Insert feedback ──
try {
    $stmt = $conn->prepare("
        INSERT INTO feedback
            (department_code, respondent_type, sex, age_group,
             region, service_availed, email, language,
             cc1, cc2, cc3,
             rating,
             sqd0, sqd1, sqd2, sqd3, sqd4, sqd5, sqd6, sqd7, sqd8,
             comment, suggestions, submitted_at)
        VALUES
            (?, ?, ?, ?,
             ?, ?, ?, ?,
             ?, ?, ?,
             ?,
             ?, ?, ?, ?, ?, ?, ?, ?, ?,
             ?, ?, NOW())
    ");

    $stmt->execute([
        $dept_code, $respondent_type, $sex, $age_group,
        $region, $service_availed, $email ?: null, $language,
        $cc1, $cc2, $cc3,
        $rating,
        $sqd[0], $sqd[1], $sqd[2], $sqd[3], $sqd[4],
        $sqd[5], $sqd[6], $sqd[7], $sqd[8],
        $comment ?: null,
        $suggestions ?: null,
    ]);

    echo json_encode([
        'success'   => true,
        'message'   => 'Feedback submitted successfully.',
        'dept_name' => $dept['name'],
        'id'        => $conn->lastInsertId(),
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}