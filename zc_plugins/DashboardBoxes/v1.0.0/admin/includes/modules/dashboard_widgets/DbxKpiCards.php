<?php
/**
 * Dashboard Boxes -- Today at a Glance.
 *
 * Four cards: orders, revenue and new customers since midnight, and reviews
 * awaiting approval. "Today" is taken from PHP's clock, not the database
 * server's, because the two are not always in the same time zone and a
 * dashboard that says zero orders at 9 a.m. because the server is still on
 * yesterday is worse than none.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
require_once dirname(__DIR__, 4) . '/lib/DashboardBoxesLib.php';

$dbxSeeOrders = zen_is_superuser() || check_page(FILENAME_ORDERS, '');
$dbxSeeCustomers = zen_is_superuser() || check_page(FILENAME_CUSTOMERS, '');
$dbxSeeReviews = zen_is_superuser() || check_page(FILENAME_REVIEWS, '');
if (!$dbxSeeOrders && !$dbxSeeCustomers && !$dbxSeeReviews) {
    return;
}

if (!isset($currencies) || !is_object($currencies)) {
    if (!class_exists('currencies')) {
        require_once DIR_WS_CLASSES . 'currencies.php';
    }
    $currencies = new currencies();
}

$dbxMidnight = date('Y-m-d') . ' 00:00:00';
$dbxCards = [];

if ($dbxSeeOrders) {
    $result = $db->Execute(
        "SELECT COUNT(*) AS total FROM " . TABLE_ORDERS . " WHERE date_purchased >= '" . $db->prepare_input($dbxMidnight) . "'",
        false, true, 300
    );
    $dbxCards[] = [
        'class' => 'dbx-bg-aqua', 'icon' => 'fa-shopping-cart',
        'value' => number_format((int)$result->fields['total']),
        'label' => DBX_KPI_ORDERS_TODAY,
        'href' => zen_href_link(FILENAME_ORDERS),
    ];
    $result = $db->Execute(
        "SELECT SUM(ot.value) AS total FROM " . TABLE_ORDERS_TOTAL . " ot"
        . " INNER JOIN " . TABLE_ORDERS . " o ON o.orders_id = ot.orders_id"
        . " WHERE ot.class = 'ot_total' AND o.date_purchased >= '" . $db->prepare_input($dbxMidnight) . "'",
        false, true, 300
    );
    $dbxCards[] = [
        'class' => 'dbx-bg-green', 'icon' => 'fa-dollar',
        'value' => $currencies->format((float)$result->fields['total']),
        'label' => DBX_KPI_REVENUE_TODAY,
        'href' => zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS),
    ];
}
if ($dbxSeeCustomers) {
    $result = $db->Execute(
        "SELECT COUNT(*) AS total FROM " . TABLE_CUSTOMERS_INFO
        . " WHERE customers_info_date_account_created >= '" . $db->prepare_input($dbxMidnight) . "'",
        false, true, 300
    );
    $dbxCards[] = [
        'class' => 'dbx-bg-yellow', 'icon' => 'fa-user-plus',
        'value' => number_format((int)$result->fields['total']),
        'label' => DBX_KPI_CUSTOMERS_TODAY,
        'href' => zen_href_link(FILENAME_CUSTOMERS),
    ];
}
if ($dbxSeeReviews) {
    $result = $db->Execute("SELECT COUNT(*) AS total FROM " . TABLE_REVIEWS . " WHERE status = 0", false, true, 300);
    $dbxCards[] = [
        'class' => 'dbx-bg-red', 'icon' => 'fa-comments',
        'value' => number_format((int)$result->fields['total']),
        'label' => DBX_KPI_REVIEWS_PENDING,
        'href' => zen_href_link(FILENAME_REVIEWS, 'status=1'),
    ];
}
$dbxCardWidth = (count($dbxCards) >= 4) ? 'col-xs-6 col-lg-3' : 'col-xs-6 col-md-' . (int)(12 / max(1, count($dbxCards)));
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('KpiCards'); ?> dbx-kpi-widget" data-dbx-widget="KpiCards">
    <div class="panel">
        <div class="panel-heading"><i class="fa fa-tachometer"></i> <?php echo DBX_WIDGET_KPI_CARDS; ?></div>
        <div class="panel-body">
            <div class="row dbx-kpi-row">
                <?php foreach ($dbxCards as $dbxCard) { ?>
                <div class="<?php echo $dbxCardWidth; ?>">
                    <a class="dbx-kpi <?php echo $dbxCard['class']; ?>" href="<?php echo $dbxCard['href']; ?>">
                        <div class="dbx-kpi-inner">
                            <h3><?php echo zen_output_string_protected($dbxCard['value']); ?></h3>
                            <p><?php echo $dbxCard['label']; ?></p>
                        </div>
                        <div class="dbx-kpi-icon"><i class="fa <?php echo $dbxCard['icon']; ?>"></i></div>
                        <span class="dbx-kpi-footer"><?php echo DBX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></span>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
