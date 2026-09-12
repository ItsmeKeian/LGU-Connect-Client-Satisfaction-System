<?php

require "../dbconnect.php";

header('Content-Type: application/json');

$dept_code = strtoupper(trim($_GET['dept'] ?? ''));

// ── Language: 'en' (default) or 'tl' ──
$lang = strtolower(trim($_GET['lang'] ?? 'en'));
if (!in_array($lang, ['en', 'tl'])) {
    $lang = 'en';
}

// ── 1. Is feedback open? ──
$is_open = $conn->query(
    "SELECT setting_value FROM settings WHERE setting_key='feedback_open' LIMIT 1"
)->fetchColumn();

// ── 2. LGU settings ──
$rows = $conn->query(
    "SELECT setting_key, setting_value FROM settings WHERE setting_group = 'lgu'"
)->fetchAll(PDO::FETCH_ASSOC);

$lgu_raw = [];
foreach ($rows as $r) { $lgu_raw[$r['setting_key']] = $r['setting_value']; }

$lgu = [
    'name'    => $lgu_raw['lgu_name']    ?? 'Municipality of San Julian',
    'address' => $lgu_raw['lgu_address'] ?? 'San Julian, Eastern Samar',
];

// ── 3. Specific department (from URL ?dept=CODE) ──
// NOTE: now also fetching `channel` so we know whether to load
// onsite or online SQD4/6/7 wording for this department.
$department = null;
$channel = 'onsite'; // default when no department is pre-selected yet
if ($dept_code) {
    $stmt = $conn->prepare(
        "SELECT code, name, head, description, channel FROM departments WHERE code = ? AND status = 'active' LIMIT 1"
    );
    $stmt->execute([$dept_code]);
    $department = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($department && !empty($department['channel'])) {
        $channel = $department['channel'];
    }
}

// ── 4. All active departments (for dropdown) ──
// Also include channel here — needed because if the citizen picks a
// department from the dropdown (no ?dept= in URL), the frontend must
// re-fetch or already know that department's channel before showing
// the correct SQD wording.
$all_depts = $conn->query(
    "SELECT code, name, channel FROM departments WHERE status = 'active' ORDER BY name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── 5. Official ARTA CSM Questions (SQD0-8 + CC1-3) ──
// Loaded from config file, keyed by language and channel.
$csm = include __DIR__ . '/../config/csm_questions.php';

$sqd_raw = $csm['sqd'][$lang][$channel] ?? $csm['sqd']['en']['onsite'];
$cc_raw  = $csm['cc'][$lang] ?? $csm['cc']['en'];

// Convert sqd_raw (assoc: sqd0 => "text") into the ordered list shape
// feedback.js already expects: [ {key, question}, ... ]
$sqd_questions = [];
foreach ($sqd_raw as $key => $question) {
    $sqd_questions[] = ['key' => $key, 'question' => $question];
}

// CC questions shape: [ {key, question, options: {1=>text,...}}, ... ]
$cc_questions = [];
foreach ($cc_raw as $key => $data) {
    $cc_questions[] = [
        'key'      => $key,
        'question' => $data['question'],
        'options'  => $data['options'],
    ];
}

echo json_encode([
    'success'       => true,
    'is_open'       => $is_open !== '0',
    'lgu'           => $lgu,
    'department'    => $department,
    'all_depts'     => $all_depts,
    'lang'          => $lang,
    'channel'       => $channel,
    'sqd_questions' => $sqd_questions,
    'cc_questions'  => $cc_questions,
]);