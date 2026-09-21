<?php
/**
 * Dashboard Boxes -- Sales, the last 30 days as a line chart.
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

if (!zen_is_superuser() && !check_page(FILENAME_STATS_SALES_REPORT_GRAPHS, '')) {
    return;
}

$dbxDays = 30;
$dbxSales = [];
$dbxOrders = [];
$dbxLabels = [];
$dbxNow = time();
for ($i = $dbxDays - 1; $i >= 0; $i--) {
    $stamp = strtotime('-' . $i . ' days', $dbxNow);
    $key = date('Y-m-d', $stamp);
    $dbxSales[$key] = 0;
    $dbxOrders[$key] = 0;
    $dbxLabels[$key] = DashboardBoxesLib::shortDate($stamp);
}

$dbxFrom = date('Y-m-d', strtotime('-' . ($dbxDays - 1) . ' days', $dbxNow)) . ' 00:00:00';
$results = $db->Execute(
    "SELECT DATE(o.date_purchased) AS sale_date, SUM(ot.value) AS total_sales, COUNT(DISTINCT o.orders_id) AS total_orders"
    . " FROM " . TABLE_ORDERS . " o"
    . " INNER JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id AND ot.class = 'ot_total')"
    . " WHERE o.date_purchased >= '" . $db->prepare_input($dbxFrom) . "'"
    . " GROUP BY sale_date",
    false, true, 1800
);
foreach ($results as $row) {
    $day = $row['sale_date'];
    if (isset($dbxSales[$day])) {
        $dbxSales[$day] = (float)$row['total_sales'];
        $dbxOrders[$day] = (int)$row['total_orders'];
    }
}
$dbxJs = json_encode([
    'labels' => array_values($dbxLabels),
    'sales' => array_values($dbxSales),
    'orders' => array_values($dbxOrders),
    'revenueLabel' => DBX_SALES_LABEL_REVENUE,
    'ordersLabel' => DBX_SALES_LABEL_ORDERS,
    'locale' => DBX_SALES_NUMBER_FORMAT,
    'currency' => DEFAULT_CURRENCY,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('SalesReport'); ?>" data-dbx-widget="SalesReport">
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-line-chart"></i> <?php echo DBX_SALES_HEADING; ?>
            <small><?php echo sprintf(DBX_SALES_SUBHEADING, (int)$dbxDays); ?></small>
            <div class="pull-right">
                <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS); ?>" class="btn btn-xs btn-default"><?php echo DBX_VIEW_FULL_REPORT; ?></a>
            </div>
        </div>
        <div class="panel-body">
            <div class="dbx-sales-chart"><canvas id="dbxSalesChart"></canvas></div>
        </div>
    </div>
</div>
<script title="Dashboard Boxes: sales chart">
document.addEventListener('DOMContentLoaded', function () {
    var canvas = document.getElementById('dbxSalesChart');
    if (!canvas || typeof Chart === 'undefined') { return; }
    var d = <?php echo $dbxJs; ?>;
    var ctx = canvas.getContext('2d');
    var gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
    gradient.addColorStop(1, 'rgba(54, 162, 235, 0.0)');
    var money = new Intl.NumberFormat(d.locale, {style: 'currency', currency: d.currency});
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: d.labels,
            datasets: [
                {label: d.revenueLabel, data: d.sales, borderColor: '#337ab7', backgroundColor: gradient, borderWidth: 2,
                 pointBackgroundColor: '#fff', pointBorderColor: '#337ab7', pointRadius: 3, fill: true, tension: 0.3, yAxisID: 'y'},
                {label: d.ordersLabel, data: d.orders, borderColor: '#d9534f', backgroundColor: 'rgba(217, 83, 79, 0.1)', borderWidth: 2,
                 borderDash: [5, 5], pointBackgroundColor: '#d9534f', pointBorderColor: '#fff', pointRadius: 4, fill: false, tension: 0.1, yAxisID: 'y1'}
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {mode: 'index', intersect: false},
            plugins: {
                legend: {display: true, position: 'top'},
                tooltip: {callbacks: {label: function (c) {
                    var label = c.dataset.label || '';
                    return label + ': ' + (label === d.revenueLabel ? money.format(c.parsed.y) : c.parsed.y);
                }}}
            },
            scales: {
                x: {grid: {display: false}},
                y: {type: 'linear', display: true, position: 'left', grid: {color: 'rgba(0,0,0,0.05)'}},
                y1: {type: 'linear', display: true, position: 'right', grid: {display: false}, min: 0, suggestedMax: 10}
            }
        }
    });
});
</script>
