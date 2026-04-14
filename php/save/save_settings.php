<?php

require "../auth_check.php";
require "../dbconnect.php";
requireSuperAdmin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'save_settings';

try {
    switch ($action) {

        // ── Save LGU or System settings ──
        case 'save_settings':
            $group    = $_POST['group'] ?? 'lgu';
            $settings = $_POST['settings'] ?? [];

            if (empty($settings)) {
                echo json_encode(['success' => false, 'message' => 'No settings provided.']);
                exit;
            }

            $stmt = $conn->prepare("
                UPDATE settings
                SET setting_value = :val
                WHERE setting_key = :key AND setting_group = :group
            ");

            $count = 0;
            foreach ($settings as $key => $val) {
                // Sanitize key — only allow alphanumeric + underscore
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) continue;
                $stmt->execute([
                    ':val'   => trim($val),
                    ':key'   => $key,
                    ':group' => $group,
                ]);
                $count += $stmt->rowCount();
            }

            echo json_encode([
                'success' => true,
                'message' => 'Settings saved successfully.',
                'updated' => $count,
            ]);
            break;

        // ── Clear old feedback ──
        case 'clear_old_feedback':
            $months = max(1, (int)($_POST['months'] ?? 12));
            $stmt   = $conn->prepare("
                DELETE FROM feedback
                WHERE submitted_at < DATE_SUB(NOW(), INTERVAL :months MONTH)
            ");
            $stmt->execute([':months' => $months]);
            $deleted = $stmt->rowCount();
            echo json_encode([
                'success' => true,
                'message' => $deleted . ' old feedback record(s) deleted (older than ' . $months . ' months).',
                'deleted' => $deleted,
            ]);
            break;

        // ── Clear export logs ──
        case 'clear_export_logs':
            $conn->exec("DELETE FROM export_logs");
            echo json_encode(['success' => true, 'message' => 'Export history cleared.']);
            break;

        // ── Get DB stats ──
        case 'get_db_stats':
            $stats = [];

            // Row counts
            foreach (['feedback', 'departments', 'users', 'export_logs', 'settings'] as $tbl) {
                $r = $conn->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
                $stats['rows'][$tbl] = (int)$r;
            }

            // DB size
            $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
            $sizeQ  = $conn->prepare("
                SELECT ROUND(SUM(data_length + index_length) / 1024, 2) AS size_kb
                FROM information_schema.tables
                WHERE table_schema = :db
            ");
            $sizeQ->execute([':db' => $dbName]);
            $stats['db_size_kb'] = $sizeQ->fetchColumn() ?: 0;

            // Oldest & newest feedback
            $range = $conn->query("
                SELECT
                    DATE_FORMAT(MIN(submitted_at), '%b %d, %Y') AS oldest,
                    DATE_FORMAT(MAX(submitted_at), '%b %d, %Y') AS newest,
                    COUNT(*) AS total
                FROM feedback
            ")->fetch(PDO::FETCH_ASSOC);
            $stats['feedback_range'] = $range;

            // PHP & MySQL info
            $stats['php_version']   = phpversion();
            $stats['mysql_version'] = $conn->query("SELECT VERSION()")->fetchColumn();
            $stats['db_name']       = $dbName;

            echo json_encode(['success' => true, 'stats' => $stats]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}