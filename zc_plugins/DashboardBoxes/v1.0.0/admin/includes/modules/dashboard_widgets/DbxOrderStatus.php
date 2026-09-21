<?php
/**
 * Dashboard Boxes -- Orders by Status, with the store's own status colors
 * where it has them (Zen Cart 2.2.1 and later).
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

if (!zen_is_superuser() && !check_page(FILENAME_ORDERS, '')) {
    return;
}

$dbxHasColors = DashboardBoxesLib::statusColorsSupported();
$dbxStatuses = [];
$results = $db->Execute(
    "SELECT orders_status_id, orders_status_name" . ($dbxHasColors ? ", orders_status_color_code" : "")
    . " FROM " . TABLE_ORDERS_STATUS . " WHERE language_id = " . (int)$_SESSION['languages_id']
    . " ORDER BY sort_order, orders_status_id"
);
foreach ($results as $row) {
    $dbxStatuses[] = [
        'id' => (int)$row['orders_status_id'],
        'name' => (string)$row['orders_status_name'],
        'color' => $dbxHasColors ? DashboardBoxesLib::cssColor($row['orders_status_color_code']) : '',
    ];
}

$dbxCounts = [];
$results = $db->Execute("SELECT orders_status, COUNT(*) AS total FROM " . TABLE_ORDERS . " GROUP BY orders_status", false, true, 300);
foreach ($results as $row) {
    $dbxCounts[(int)$row['orders_status']] = (int)$row['total'];
}
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('OrderStatus'); ?>" data-dbx-widget="OrderStatus">
    <div class="panel">
        <div class="panel-heading"><i class="fa fa-clipboard"></i> <?php echo DBX_ORDER_STATUS_HEADING; ?></div>
        <ul class="list-group">
            <?php foreach ($dbxStatuses as $status) {
                $count = $dbxCounts[$status['id']] ?? 0;
                $icon = $count > 0 ? 'fa-folder-open' : 'fa-folder-o';
                if ($status['color'] !== '') {
                    $badge = '<span class="label dbx-order-status-label" style="background-color:' . $status['color'] . ';color:#fff;">' . $count . '</span>';
                } else {
                    $badge = '<span class="label dbx-order-status-label ' . ($count > 0 ? 'label-primary' : 'label-default') . '">' . $count . '</span>';
                }
                ?>
                <li class="list-group-item">
                    <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'statusFilterSelect=' . $status['id']); ?>" class="<?php echo $count > 0 ? 'link-text' : 'dbx-quiet'; ?>">
                        <i class="fa <?php echo $icon; ?>"></i>
                        <?php echo zen_output_string_protected($status['name']); ?>
                    </a>
                    <div class="pull-right"><?php echo $badge; ?></div>
                </li>
            <?php } ?>
        </ul>
        <div class="panel-footer text-center">
            <a href="<?php echo zen_href_link(FILENAME_ORDERS_STATUS); ?>" class="dbx-footer-link"><i class="fa fa-cog"></i> <?php echo DBX_ORDER_STATUS_MANAGE; ?></a>
        </div>
    </div>
</div>
