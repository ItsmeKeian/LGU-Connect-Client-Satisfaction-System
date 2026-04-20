<?php
require "../auth_check.php";
require "../dbconnect.php";

// ── Auth: allow both roles ──
if (!IS_SUPERADMIN && !IS_DEPT_USER) {
    http_response_code(403); echo 'Unauthorized.'; exit;
}

// ── dept_user always locked to their own dept ──
if (IS_DEPT_USER) {
    $dept_code = CURRENT_DEPT; 
} else {
    $dept_code = (isset($_GET['dept_id']) && $_GET['dept_id'] !== '') ? $_GET['dept_id'] : null;
}

$type      = $_GET['type']      ?? 'feedback';
$format    = $_GET['format']    ?? 'csv';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to   = $_GET['date_to']   ?? date('Y-m-t');
$rating    = intval($_GET['rating'] ?? 0);

$date_from_dt = date('Y-m-d', strtotime($date_from));
$date_to_dt   = date('Y-m-d', strtotime($date_to));

// ── WHERE clause ──
$where  = "WHERE DATE(f.submitted_at) BETWEEN :from AND :to";
$params = [':from' => $date_from_dt, ':to' => $date_to_dt];
if ($dept_code) {
    $where               .= " AND f.department_code = :dept_code";
    $params[':dept_code'] = $dept_code;
}
if ($rating >= 1 && $rating <= 5) {
    $where          .= " AND f.rating = :rating";
    $params[':rating'] = $rating;
}

// ── Filename ──
$dept_slug = $dept_code ? '_'.strtolower(preg_replace('/[^a-zA-Z0-9]/','', $dept_code)) : '';
$date_slug = str_replace('-','', $date_from_dt).'_'.str_replace('-','', $date_to_dt);
$filename  = "lgu_connect_{$type}{$dept_slug}_{$date_slug}";

try {
    switch ($type) {

        case 'feedback':
            $stmt = $conn->prepare("
                SELECT f.id AS '#',
                    COALESCE(d.name, f.department_code) AS 'Department',
                    f.department_code AS 'Dept Code',
                    f.respondent_type AS 'Respondent Type',
                    f.sex AS 'Sex', f.age_group AS 'Age Group',
                    f.rating AS 'Overall Rating',
                    f.sqd0 AS 'SQD0', f.sqd1 AS 'SQD1', f.sqd2 AS 'SQD2',
                    f.sqd3 AS 'SQD3', f.sqd4 AS 'SQD4', f.sqd5 AS 'SQD5',
                    f.sqd6 AS 'SQD6', f.sqd7 AS 'SQD7', f.sqd8 AS 'SQD8',
                    f.comment AS 'Comment', f.suggestions AS 'Suggestions',
                    DATE_FORMAT(f.submitted_at,'%Y-%m-%d %H:%i:%s') AS 'Submitted At'
                FROM feedback f
                LEFT JOIN departments d ON d.code = f.department_code
                $where ORDER BY f.submitted_at DESC
            ");
            $stmt->execute($params);
            $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $headers = ['#','Department','Dept Code','Respondent Type','Sex','Age Group',
                        'Overall Rating','SQD0','SQD1','SQD2','SQD3','SQD4',
                        'SQD5','SQD6','SQD7','SQD8','Comment','Suggestions','Submitted At'];
            break;

        case 'summary':
            $stmt = $conn->prepare("
                SELECT COALESCE(d.name, f.department_code) AS 'Department',
                    f.department_code AS 'Code',
                    COUNT(f.id) AS 'Total Responses',
                    ROUND(AVG(f.rating),2) AS 'Avg Rating',
                    ROUND(SUM(CASE WHEN f.rating>=4 THEN 1 ELSE 0 END)*100.0/NULLIF(COUNT(f.id),0),1) AS 'Satisfaction Rate (%)',
                    SUM(CASE WHEN f.rating=5 THEN 1 ELSE 0 END) AS 'Excellent (5)',
                    SUM(CASE WHEN f.rating=4 THEN 1 ELSE 0 END) AS 'Good (4)',
                    SUM(CASE WHEN f.rating=3 THEN 1 ELSE 0 END) AS 'Average (3)',
                    SUM(CASE WHEN f.rating=2 THEN 1 ELSE 0 END) AS 'Poor (2)',
                    SUM(CASE WHEN f.rating=1 THEN 1 ELSE 0 END) AS 'Very Poor (1)',
                    ROUND(AVG(f.sqd0),2) AS 'Avg SQD0', ROUND(AVG(f.sqd1),2) AS 'Avg SQD1',
                    ROUND(AVG(f.sqd2),2) AS 'Avg SQD2', ROUND(AVG(f.sqd3),2) AS 'Avg SQD3',
                    ROUND(AVG(f.sqd4),2) AS 'Avg SQD4', ROUND(AVG(f.sqd5),2) AS 'Avg SQD5',
                    ROUND(AVG(f.sqd6),2) AS 'Avg SQD6', ROUND(AVG(f.sqd7),2) AS 'Avg SQD7',
                    ROUND(AVG(f.sqd8),2) AS 'Avg SQD8'
                FROM feedback f
                LEFT JOIN departments d ON d.code = f.department_code
                $where GROUP BY f.department_code, d.name ORDER BY COUNT(f.id) DESC
            ");
            $stmt->execute($params);
            $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $headers = ['Department','Code','Total Responses','Avg Rating','Satisfaction Rate (%)',
                        'Excellent (5)','Good (4)','Average (3)','Poor (2)','Very Poor (1)',
                        'Avg SQD0','Avg SQD1','Avg SQD2','Avg SQD3','Avg SQD4',
                        'Avg SQD5','Avg SQD6','Avg SQD7','Avg SQD8'];
            break;

        case 'sqd':
            $stmt = $conn->prepare("
                SELECT COALESCE(d.name, f.department_code) AS 'Department',
                    COUNT(f.id) AS 'Responses',
                    ROUND(AVG(f.sqd0),2) AS 'SQD0 - Anti-Red Tape Awareness',
                    ROUND(AVG(f.sqd1),2) AS 'SQD1 - Service Speed',
                    ROUND(AVG(f.sqd2),2) AS 'SQD2 - Updated Service Info',
                    ROUND(AVG(f.sqd3),2) AS 'SQD3 - Staff Courtesy',
                    ROUND(AVG(f.sqd4),2) AS 'SQD4 - No Unnecessary Docs',
                    ROUND(AVG(f.sqd5),2) AS 'SQD5 - No Extra Payment',
                    ROUND(AVG(f.sqd6),2) AS 'SQD6 - Simple Process',
                    ROUND(AVG(f.sqd7),2) AS 'SQD7 - Service as Promised',
                    ROUND(AVG(f.sqd8),2) AS 'SQD8 - Overall Satisfaction',
                    ROUND((AVG(f.sqd0)+AVG(f.sqd1)+AVG(f.sqd2)+AVG(f.sqd3)+
                           AVG(f.sqd4)+AVG(f.sqd5)+AVG(f.sqd6)+AVG(f.sqd7)+
                           AVG(f.sqd8))/9,2) AS 'Overall SQD Average'
                FROM feedback f
                LEFT JOIN departments d ON d.code = f.department_code
                $where GROUP BY f.department_code, d.name ORDER BY COUNT(f.id) DESC
            ");
            $stmt->execute($params);
            $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $headers = ['Department','Responses',
                        'SQD0 - Anti-Red Tape Awareness','SQD1 - Service Speed',
                        'SQD2 - Updated Service Info','SQD3 - Staff Courtesy',
                        'SQD4 - No Unnecessary Docs','SQD5 - No Extra Payment',
                        'SQD6 - Simple Process','SQD7 - Service as Promised',
                        'SQD8 - Overall Satisfaction','Overall SQD Average'];
            break;

        // ── Comments (new for dept_export) ──
        case 'comments':
            $stmt = $conn->prepare("
                SELECT COALESCE(d.name, f.department_code) AS 'Department',
                    f.rating AS 'Overall Rating',
                    f.respondent_type AS 'Respondent Type',
                    f.comment AS 'Comment',
                    f.suggestions AS 'Suggestions',
                    DATE_FORMAT(f.submitted_at,'%Y-%m-%d %H:%i:%s') AS 'Submitted At'
                FROM feedback f
                LEFT JOIN departments d ON d.code = f.department_code
                $where
                AND (f.comment IS NOT NULL AND f.comment != ''
                     OR f.suggestions IS NOT NULL AND f.suggestions != '')
                ORDER BY f.submitted_at DESC
            ");
            $stmt->execute($params);
            $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $headers = ['Department','Overall Rating','Respondent Type',
                        'Comment','Suggestions','Submitted At'];
            break;

        // ── Departments (superadmin only) ──
        case 'departments':
            if (!IS_SUPERADMIN) { http_response_code(403); echo 'Unauthorized.'; exit; }
            $stmt = $conn->prepare("
                SELECT d.id AS 'ID', d.name AS 'Department Name', d.code AS 'Code',
                       d.head AS 'Head / Officer', d.status AS 'Status',
                       COUNT(f.id) AS 'Total Feedback',
                       ROUND(AVG(f.rating),2) AS 'Avg Rating'
                FROM departments d
                LEFT JOIN feedback f ON f.department_code = d.code
                GROUP BY d.id, d.name, d.code, d.head, d.status ORDER BY d.name ASC
            ");
            $stmt->execute([]);
            $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $headers = ['ID','Department Name','Code','Head / Officer','Status','Total Feedback','Avg Rating'];
            break;

        default:
            http_response_code(400); echo 'Invalid export type.'; exit;
    }

    // ── Log export ──
    $dept_name_log = null;
    if ($dept_code) {
        $ds = $conn->prepare("SELECT name FROM departments WHERE code = :c LIMIT 1");
        $ds->execute([':c' => $dept_code]);
        $dept_name_log = $ds->fetchColumn() ?: $dept_code;
    }
    $conn->prepare("
        INSERT INTO export_logs (exported_by, export_type, export_format, dept_code, dept_name, date_from, date_to, record_count)
        VALUES (:by, :type, :fmt, :dept_code, :dept_name, :dfrom, :dto, :cnt)
    ")->execute([
        ':by' => CURRENT_USER, ':type' => $type, ':fmt' => $format,
        ':dept_code' => $dept_code, ':dept_name' => $dept_name_log,
        ':dfrom' => $date_from_dt, ':dto' => $date_to_dt, ':cnt' => count($rows),
    ]);

    // ── Output ──
    if ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$filename.'.xls"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $esc = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
        $numCols = ['#','ID','Overall Rating','Total Responses','Total Feedback',
                    'Excellent (5)','Good (4)','Average (3)','Poor (2)','Very Poor (1)',
                    'Responses','SQD0','SQD1','SQD2','SQD3','SQD4','SQD5','SQD6','SQD7','SQD8'];

        echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                   xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                   xmlns:x="urn:schemas-microsoft-com:office:excel">
        <Styles>
          <Style ss:ID="hdr"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#8B1A1A" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/></Style>
          <Style ss:ID="cel"><Alignment ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#EEEEEE"/></Borders></Style>
          <Style ss:ID="txt"><Alignment ss:Vertical="Center"/><NumberFormat ss:Format="@"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#EEEEEE"/></Borders></Style>
          <Style ss:ID="alt"><Interior ss:Color="#FFF8F8" ss:Pattern="Solid"/><Alignment ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#EEEEEE"/></Borders></Style>
        </Styles>
        <Worksheet ss:Name="'.ucfirst($type).'"><Table>';

        foreach ($headers as $h) { $w=in_array($h,$numCols)?60:(strlen($h)>20?160:110); echo '<Column ss:Width="'.$w.'"/>'; }
        echo '<Row ss:Height="24">';
        foreach ($headers as $h) { echo '<Cell ss:StyleID="hdr"><Data ss:Type="String">'.$esc($h).'</Data></Cell>'; }
        echo '</Row>';
        foreach ($rows as $i => $row) {
            $st = $i%2===1?'alt':'cel';
            echo '<Row ss:Height="18">';
            $vals=array_values($row);
            foreach ($vals as $ci => $val) {
                $h=$headers[$ci]??''; $isN=in_array($h,$numCols)&&is_numeric($val);
                $tp=$isN?'Number':'String'; $s=$isN?$st:'txt';
                echo '<Cell ss:StyleID="'.$s.'"><Data ss:Type="'.$tp.'">'.$esc($val).'</Data></Cell>';
            }
            echo '</Row>';
        }
        echo '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
          <FreezePanes/><FrozenNoSplit/><SplitHorizontal>1</SplitHorizontal><TopRowBottomPane>1</TopRowBottomPane>
        </WorksheetOptions></Worksheet></Workbook>';

    } else {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output','w');
        fputcsv($out, $headers);
        foreach ($rows as $row) { fputcsv($out, array_values($row)); }
        fclose($out);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database error: '.$e->getMessage();
}