<?php
/**
 * LOCATION: php/get/get_predictive_data.php
 * Predictive Analytics Engine for LGU-Connect
 *
 * Methods used:
 *   1. Weighted Moving Average (WMA)   — Satisfaction Trend Forecast
 *   2. Consecutive Decline Detection   — Department Risk Alerts
 *   3. SQD Threshold Analysis          — Weak Point Detector
 *
 * Accessible by both superadmin and dept_user
 * dept_user is always locked to CURRENT_DEPT
 */
require "../auth_check.php";
require "../dbconnect.php";

header('Content-Type: application/json');

if (!IS_SUPERADMIN && !IS_DEPT_USER) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

// dept_user locked to own dept
$dept_filter = IS_DEPT_USER ? CURRENT_DEPT : (trim($_GET['dept'] ?? '') ?: null);

try {

    // ══════════════════════════════════════════════════════
    // 1. SATISFACTION TREND FORECAST
    //    Uses Weighted Moving Average (WMA) on last 6 months
    //    Formula: WMA = Σ(weight × value) / Σ(weights)
    //    Weights: oldest=1, newest=6 (more recent = more weight)
    // ══════════════════════════════════════════════════════

    $whereClause = $dept_filter ? "AND f.department_code = :dept" : "";
    $params      = $dept_filter ? [':dept' => $dept_filter] : [];

    $trendStmt = $conn->prepare("
        SELECT
            DATE_FORMAT(submitted_at, '%Y-%m')       AS month_key,
            DATE_FORMAT(submitted_at, '%b %Y')       AS month_label,
            COUNT(*)                                  AS total,
            ROUND(AVG(rating), 4)                    AS avg_rating,
            ROUND(
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) * 100.0
                / NULLIF(COUNT(*), 0), 2
            )                                        AS satisfaction_rate
        FROM feedback f
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        $whereClause
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    ");
    $trendStmt->execute($params);
    $monthlyData = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

    // WMA Calculation
    $forecast = null;
    $trend_direction = 'insufficient';
    $trend_pct = 0;
    $wma_rating = null;
    $wma_sat = null;

    if (count($monthlyData) >= 2) {
        $n = count($monthlyData);
        $weights = range(1, $n); // weight 1=oldest, n=newest
        $totalWeight = array_sum($weights);

        $wma_rating = 0;
        $wma_sat    = 0;
        foreach ($monthlyData as $i => $m) {
            $wma_rating += $weights[$i] * floatval($m['avg_rating']);
            $wma_sat    += $weights[$i] * floatval($m['satisfaction_rate']);
        }
        $wma_rating = round($wma_rating / $totalWeight, 2);
        $wma_sat    = round($wma_sat    / $totalWeight, 1);

        // Trend: compare last 2 months
        $last  = floatval($monthlyData[$n-1]['satisfaction_rate']);
        $prev  = floatval($monthlyData[$n-2]['satisfaction_rate']);
        $diff  = $last - $prev;
        $trend_pct = round($diff, 1);

        // Simple linear extrapolation for next month
        if ($n >= 3) {
            // Average change over last 3 months
            $changes = [];
            for ($i = $n-3; $i < $n-1; $i++) {
                $changes[] = floatval($monthlyData[$i+1]['satisfaction_rate'])
                           - floatval($monthlyData[$i]['satisfaction_rate']);
            }
            $avg_change = array_sum($changes) / count($changes);
            $forecast   = round(min(100, max(0, $last + $avg_change)), 1);
        } else {
            $forecast = round(min(100, max(0, $last + $diff)), 1);
        }

        if ($diff > 1)       $trend_direction = 'improving';
        elseif ($diff < -1)  $trend_direction = 'declining';
        else                 $trend_direction = 'stable';
    }

    // ══════════════════════════════════════════════════════
    // 2. DEPARTMENT RISK ALERTS
    //    Consecutive Decline Detection:
    //    A dept is "at risk" if its avg rating declined
    //    for 2+ consecutive months
    //    Only for superadmin (dept_user sees only own dept)
    // ══════════════════════════════════════════════════════

    $risk_alerts = [];

    if (IS_SUPERADMIN && !$dept_filter) {
        $riskStmt = $conn->prepare("
            SELECT
                f.department_code,
                COALESCE(d.name, f.department_code) AS dept_name,
                DATE_FORMAT(f.submitted_at, '%Y-%m') AS month_key,
                DATE_FORMAT(f.submitted_at, '%b %Y') AS month_label,
                ROUND(AVG(f.rating), 2)              AS avg_rating,
                COUNT(f.id)                          AS total
            FROM feedback f
            LEFT JOIN departments d ON d.code = f.department_code
            WHERE f.submitted_at >= DATE_SUB(NOW(), INTERVAL 4 MONTH)
            GROUP BY f.department_code, d.name, month_key, month_label
            ORDER BY f.department_code, month_key ASC
        ");
        $riskStmt->execute([]);
        $riskData = $riskStmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by department
        $byDept = [];
        foreach ($riskData as $r) {
            $byDept[$r['department_code']][] = $r;
        }

        foreach ($byDept as $code => $months) {
            if (count($months) < 2) continue;

            $declines    = 0;
            $last_rating = null;
            $trend_vals  = [];

            foreach ($months as $m) {
                $trend_vals[] = floatval($m['avg_rating']);
                if ($last_rating !== null) {
                    if (floatval($m['avg_rating']) < $last_rating) {
                        $declines++;
                    } else {
                        $declines = 0; // reset if not consecutive
                    }
                }
                $last_rating = floatval($m['avg_rating']);
            }

            $latest = end($months);
            $first  = reset($months);
            $change = round(floatval($latest['avg_rating']) - floatval($first['avg_rating']), 2);

            // Determine risk level
            $risk_level = 'none';
            if ($declines >= 2)       $risk_level = 'high';
            elseif ($declines === 1)  $risk_level = 'moderate';
            elseif ($change < -0.3)   $risk_level = 'moderate';

            if ($risk_level !== 'none') {
                $risk_alerts[] = [
                    'dept_code'    => $code,
                    'dept_name'    => $latest['dept_name'],
                    'risk_level'   => $risk_level,
                    'current_avg'  => floatval($latest['avg_rating']),
                    'change'       => $change,
                    'declines'     => $declines,
                    'trend'        => $trend_vals,
                    'months'       => array_column($months, 'month_label'),
                    'total_recent' => (int)$latest['total'],
                ];
            }
        }

        // Sort: high risk first
        usort($risk_alerts, fn($a, $b) =>
            ($b['risk_level'] === 'high' ? 1 : 0) - ($a['risk_level'] === 'high' ? 1 : 0)
        );

    } elseif (IS_DEPT_USER) {
        // For dept_user: show their own dept's risk status
        $myRiskStmt = $conn->prepare("
            SELECT
                DATE_FORMAT(submitted_at, '%Y-%m') AS month_key,
                DATE_FORMAT(submitted_at, '%b %Y') AS month_label,
                ROUND(AVG(rating), 2)              AS avg_rating,
                COUNT(*)                           AS total
            FROM feedback
            WHERE department_code = :dept
              AND submitted_at >= DATE_SUB(NOW(), INTERVAL 4 MONTH)
            GROUP BY month_key, month_label
            ORDER BY month_key ASC
        ");
        $myRiskStmt->execute([':dept' => $dept_filter]);
        $myMonths = $myRiskStmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($myMonths) >= 2) {
            $declines = 0; $last_r = null; $trend_vals = [];
            foreach ($myMonths as $m) {
                $trend_vals[] = floatval($m['avg_rating']);
                if ($last_r !== null && floatval($m['avg_rating']) < $last_r) $declines++;
                elseif ($last_r !== null) $declines = 0;
                $last_r = floatval($m['avg_rating']);
            }
            $latest = end($myMonths);
            $first  = reset($myMonths);
            $change = round(floatval($latest['avg_rating']) - floatval($first['avg_rating']), 2);
            $risk_level = $declines >= 2 ? 'high' : ($declines === 1 || $change < -0.3 ? 'moderate' : 'none');

            $risk_alerts[] = [
                'dept_code'   => $dept_filter,
                'dept_name'   => CURRENT_DEPT,
                'risk_level'  => $risk_level,
                'current_avg' => floatval($latest['avg_rating']),
                'change'      => $change,
                'declines'    => $declines,
                'trend'       => $trend_vals,
                'months'      => array_column($myMonths, 'month_label'),
                'total_recent'=> (int)$latest['total'],
            ];
        }
    }

    // ══════════════════════════════════════════════════════
    // 3. SQD WEAK POINT DETECTOR
    //    Identifies SQD dimensions consistently below threshold
    //    Threshold: avg < 3.5 = Weak, 3.5–4.0 = Needs Improvement
    //    Also tracks monthly trend per SQD dimension
    // ══════════════════════════════════════════════════════

    $sqdStmt = $conn->prepare("
        SELECT
            ROUND(AVG(sqd0), 2) AS sqd0, ROUND(AVG(sqd1), 2) AS sqd1,
            ROUND(AVG(sqd2), 2) AS sqd2, ROUND(AVG(sqd3), 2) AS sqd3,
            ROUND(AVG(sqd4), 2) AS sqd4, ROUND(AVG(sqd5), 2) AS sqd5,
            ROUND(AVG(sqd6), 2) AS sqd6, ROUND(AVG(sqd7), 2) AS sqd7,
            ROUND(AVG(sqd8), 2) AS sqd8,
            COUNT(*)            AS total
        FROM feedback f
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        $whereClause
    ");
    $sqdStmt->execute($params);
    $sqdAvg = $sqdStmt->fetch(PDO::FETCH_ASSOC);

    $sqd_labels = [
        'sqd0' => 'Citizens Charter Awareness',
        'sqd1' => 'Service Speed',
        'sqd2' => 'Transaction Time Compliance',
        'sqd3' => 'Staff Courtesy & Helpfulness',
        'sqd4' => 'No Extra Fees',
        'sqd5' => 'Process Compliance',
        'sqd6' => 'Service Quality Standard',
        'sqd7' => 'Timely Service Delivery',
        'sqd8' => 'Overall Satisfaction',
    ];

    $sqd_analysis = [];
    $overall_sqd_avg = 0;
    $sqd_count = 0;

    foreach ($sqd_labels as $key => $label) {
        $val = floatval($sqdAvg[$key] ?? 0);
        if ($val === 0.0) continue;

        $overall_sqd_avg += $val;
        $sqd_count++;

        $status = 'good';
        $recommendation = '';

        if ($val < 3.0) {
            $status = 'critical';
            $recommendation = "Immediate action needed. Consider reviewing procedures for {$label}.";
        } elseif ($val < 3.5) {
            $status = 'weak';
            $recommendation = "Below acceptable threshold. Focus improvement efforts on {$label}.";
        } elseif ($val < 4.0) {
            $status = 'needs_improvement';
            $recommendation = "Slightly below target. Monitor and improve {$label}.";
        } else {
            $recommendation = "Performing well. Maintain current standards.";
        }

        $sqd_analysis[] = [
            'key'            => $key,
            'label'          => $label,
            'avg'            => $val,
            'status'         => $status,
            'recommendation' => $recommendation,
            'pct'            => round($val / 5 * 100, 1),
        ];
    }

    // Sort: worst first
    usort($sqd_analysis, fn($a, $b) => $a['avg'] <=> $b['avg']);

    $overall_sqd_avg = $sqd_count > 0 ? round($overall_sqd_avg / $sqd_count, 2) : 0;

    // Count weak dimensions
    $weak_count     = count(array_filter($sqd_analysis, fn($s) => in_array($s['status'], ['weak','critical'])));
    $improve_count  = count(array_filter($sqd_analysis, fn($s) => $s['status'] === 'needs_improvement'));
    $good_count     = count(array_filter($sqd_analysis, fn($s) => $s['status'] === 'good'));

    // ══════════════════════════════════════════════════════
    // 4. OVERALL PREDICTION SUMMARY
    // ══════════════════════════════════════════════════════

    // Overall health score (0-100)
    $health_score = 0;
    $health_components = 0;

    if ($wma_sat !== null) {
        $health_score += $wma_sat;
        $health_components++;
    }
    if ($overall_sqd_avg > 0) {
        $health_score += ($overall_sqd_avg / 5 * 100);
        $health_components++;
    }
    $health_score = $health_components > 0
        ? round($health_score / $health_components, 1)
        : 0;

    $health_label = $health_score >= 80 ? 'Excellent'
        : ($health_score >= 65 ? 'Good'
        : ($health_score >= 50 ? 'Fair'
        : 'Needs Attention'));

    // General recommendation
    $general_recommendation = '';
    if ($trend_direction === 'declining')   $general_recommendation = 'Satisfaction is declining. Review recent feedback for patterns and address recurring complaints.';
    elseif ($trend_direction === 'improving') $general_recommendation = 'Performance is improving. Keep up current service standards and continue monitoring.';
    elseif ($trend_direction === 'stable')  $general_recommendation = 'Performance is stable. Consider targeted improvements in lower-performing SQD areas.';
    else $general_recommendation = 'Insufficient data for full prediction. Collect more feedback for accurate forecasting.';

    echo json_encode([
        'success' => true,
        'data'    => [

            // Trend forecast
            'trend' => [
                'monthly_data'    => $monthlyData,
                'direction'       => $trend_direction,
                'change_pct'      => $trend_pct,
                'wma_rating'      => $wma_rating,
                'wma_sat'         => $wma_sat,
                'forecast_sat'    => $forecast,
                'forecast_label'  => $forecast !== null
                    ? ($forecast >= 80 ? 'Strong' : ($forecast >= 65 ? 'Moderate' : 'Low'))
                    : 'N/A',
            ],

            // Risk alerts
            'risk' => [
                'alerts'          => $risk_alerts,
                'high_risk_count' => count(array_filter($risk_alerts, fn($a) => $a['risk_level'] === 'high')),
                'mod_risk_count'  => count(array_filter($risk_alerts, fn($a) => $a['risk_level'] === 'moderate')),
            ],

            // SQD analysis
            'sqd' => [
                'analysis'        => $sqd_analysis,
                'overall_avg'     => $overall_sqd_avg,
                'weak_count'      => $weak_count,
                'improve_count'   => $improve_count,
                'good_count'      => $good_count,
            ],

            // Overall summary
            'summary' => [
                'health_score'    => $health_score,
                'health_label'    => $health_label,
                'recommendation'  => $general_recommendation,
            ],
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}