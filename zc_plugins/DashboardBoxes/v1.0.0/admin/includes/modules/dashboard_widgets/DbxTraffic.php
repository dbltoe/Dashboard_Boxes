<?php
/**
 * Dashboard Boxes -- Traffic History, sessions and hits per day.
 *
 * Portions copyright 2026 ZenExpert (https://zenexpert.com), from the
 * Modern Dynamic Dashboard, and the Zen Cart Development Team.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
require_once dirname(__DIR__, 4) . '/lib/DashboardBoxesLib.php';

if (!defined('TABLE_COUNTER_HISTORY') || !is_object($sniffer) || !$sniffer->table_exists(TABLE_COUNTER_HISTORY)) {
    return;
}

$dbxDays = 30;
$dbxRows = [];
$visits = $db->Execute(
    "SELECT startdate, counter, session_counter FROM " . TABLE_COUNTER_HISTORY . " ORDER BY startdate DESC",
    $dbxDays, true, 1800
);
foreach ($visits as $visit) {
    $raw = (string)$visit['startdate'];
    if (strlen($raw) !== 8 || !ctype_digit($raw)) {
        continue;
    }
    $stamp = mktime(0, 0, 0, (int)substr($raw, 4, 2), (int)substr($raw, 6, 2), (int)substr($raw, 0, 4));
    $dbxRows[] = [
        'label' => DashboardBoxesLib::shortDate($stamp),
        'sessions' => (int)$visit['session_counter'],
        'hits' => (int)$visit['counter'],
    ];
}
$dbxRows = array_reverse($dbxRows);
$dbxJs = json_encode([
    'labels' => array_column($dbxRows, 'label'),
    'sessions' => array_column($dbxRows, 'sessions'),
    'hits' => array_column($dbxRows, 'hits'),
    'sessionsLabel' => DBX_TRAFFIC_SESSIONS,
    'hitsLabel' => DBX_TRAFFIC_HITS,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('Traffic'); ?>" data-dbx-widget="Traffic">
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-users"></i> <?php echo DBX_TRAFFIC_HEADING; ?>
            <small><?php echo sprintf(DBX_TRAFFIC_SUBHEADING, (int)$dbxDays); ?></small>
        </div>
        <div class="panel-body">
            <?php if ($dbxRows !== []) { ?>
                <div class="dbx-traffic-chart"><canvas id="dbxTrafficChart"></canvas></div>
            <?php } else { ?>
                <div class="dbx-empty">
                    <i class="fa fa-bar-chart fa-3x"></i><br><br><?php echo DBX_TRAFFIC_NO_DATA; ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php if ($dbxRows !== []) { ?>
<script title="Dashboard Boxes: traffic chart">
document.addEventListener('DOMContentLoaded', function () {
    var canvas = document.getElementById('dbxTrafficChart');
    if (!canvas || typeof Chart === 'undefined') { return; }
    var d = <?php echo $dbxJs; ?>;
    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [
                {label: d.sessionsLabel, data: d.sessions, backgroundColor: 'rgba(54, 162, 235, 0.7)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1, yAxisID: 'y'},
                {label: d.hitsLabel, type: 'line', data: d.hits, borderColor: 'rgba(255, 159, 64, 1)', backgroundColor: 'rgba(255, 159, 64, 0.1)', borderWidth: 2, pointRadius: 2, tension: 0.3, fill: false, yAxisID: 'y1'}
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {mode: 'index', intersect: false},
            plugins: {legend: {position: 'top'}},
            scales: {
                x: {grid: {display: false}, ticks: {maxTicksLimit: 10}},
                y: {type: 'linear', display: true, position: 'left', title: {display: true, text: d.sessionsLabel}, grid: {color: 'rgba(0,0,0,0.05)'}},
                y1: {type: 'linear', display: true, position: 'right', title: {display: true, text: d.hitsLabel}, grid: {display: false}, suggestedMin: 0}
            }
        }
    });
});
</script>
<?php } ?>
