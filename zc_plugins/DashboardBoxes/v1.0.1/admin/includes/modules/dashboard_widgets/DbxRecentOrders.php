<?php
/**
 * Dashboard Boxes -- Recent Orders, with status badges and quick filters.
 *
 * The same three site-specific overrides the stock widget honors still
 * work from admin/includes/extra_datafiles/site_specific_admin_overrides.php:
 * $recentOrdersMaxRows, $recentOrdersWidgetOrderStatusIDs and
 * $includeAttributesInPopoverRows. $show_status_pills (true by default) hides
 * the filter pills in the heading when false.
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

if (!isset($currencies) || !is_object($currencies)) {
    if (!class_exists('currencies')) {
        require_once DIR_WS_CLASSES . 'currencies.php';
    }
    $currencies = new currencies();
}

$dbxMaxRows = isset($recentOrdersMaxRows) ? max(1, (int)$recentOrdersMaxRows) : 10;
$dbxWithAttributes = isset($includeAttributesInPopoverRows) ? (bool)$includeAttributesInPopoverRows : true;
$dbxShowPills = isset($show_status_pills) ? (bool)$show_status_pills : true;
$dbxPillIds = (isset($recentOrdersWidgetOrderStatusIDs) && is_array($recentOrdersWidgetOrderStatusIDs))
    ? $recentOrdersWidgetOrderStatusIDs
    : [1, 2];
$dbxPillIds = array_values(array_unique(array_map('intval', array_filter($dbxPillIds, 'is_numeric'))));
$dbxHasColors = DashboardBoxesLib::statusColorsSupported();
$dbxLanguageId = (int)$_SESSION['languages_id'];

$dbxFallbackClass = static function (int $statusId): string {
    switch ($statusId) {
        case 1: return 'label-warning';
        case 2: return 'label-info';
        case 3: return 'label-success';
        default: return 'label-default';
    }
};
$dbxBadge = static function (string $name, int $statusId, string $color) use ($dbxFallbackClass): string {
    $color = DashboardBoxesLib::cssColor($color);
    if ($color !== '') {
        return '<span class="label" style="background-color:' . $color . ';color:#fff;">' . zen_output_string_protected($name) . '</span>';
    }
    return '<span class="label ' . $dbxFallbackClass($statusId) . '">' . zen_output_string_protected($name) . '</span>';
};

// status names (and colors, where the store has them)
$dbxStatusMeta = [];
$results = $db->Execute(
    "SELECT orders_status_id, orders_status_name" . ($dbxHasColors ? ", orders_status_color_code" : "")
    . " FROM " . TABLE_ORDERS_STATUS . " WHERE language_id = " . (int)$dbxLanguageId
);
foreach ($results as $row) {
    $dbxStatusMeta[(int)$row['orders_status_id']] = [
        'name' => (string)$row['orders_status_name'],
        'color' => $dbxHasColors ? (string)$row['orders_status_color_code'] : '',
    ];
}

// the pills' counts
$dbxPillCounts = [];
if ($dbxShowPills && $dbxPillIds !== []) {
    foreach ($dbxPillIds as $id) {
        $dbxPillCounts[$id] = 0;
    }
    $results = $db->Execute(
        "SELECT orders_status, COUNT(*) AS total FROM " . TABLE_ORDERS
        . " WHERE orders_status IN (" . implode(',', $dbxPillIds) . ") GROUP BY orders_status",
        false, true, 300
    );
    foreach ($results as $row) {
        $dbxPillCounts[(int)$row['orders_status']] = (int)$row['total'];
    }
}

$orders = $db->Execute(
    "SELECT o.orders_id, o.customers_name, o.customers_id, o.date_purchased, o.currency, o.currency_value, o.orders_status,"
    . " ot.text AS order_total, ot.value AS order_value"
    . " FROM " . TABLE_ORDERS . " o"
    . " LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id AND ot.class = 'ot_total')"
    . " ORDER BY o.orders_id DESC",
    $dbxMaxRows, true, 300
);
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('RecentOrders'); ?>" data-dbx-widget="RecentOrders">
    <div class="panel">
        <div class="panel-heading">
            <div class="row">
                <div class="col-xs-6 col-md-3">
                    <i class="fa fa-list-alt"></i> <?php echo DBX_ORDERS_HEADING; ?>
                </div>
                <div class="col-xs-12 col-md-6 text-center dbx-status-pills">
                    <?php
                    if ($dbxShowPills) {
                        foreach ($dbxPillIds as $sID) {
                            $meta = $dbxStatusMeta[$sID] ?? ['name' => zen_get_orders_status_name($sID), 'color' => ''];
                            $count = $dbxPillCounts[$sID] ?? 0;
                            $color = DashboardBoxesLib::cssColor($meta['color']);
                            $style = ($color !== '' ? 'background-color:' . $color . ';color:#fff;' : '') . ($count === 0 ? 'opacity:0.5;' : '');
                            $class = $color !== '' ? 'label' : 'label ' . $dbxFallbackClass($sID);
                            ?>
                            <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'statusFilterSelect=' . $sID); ?>">
                                <span class="<?php echo $class; ?>" style="<?php echo $style; ?>"><?php echo zen_output_string_protected($meta['name']); ?>: <strong><?php echo (int)$count; ?></strong></span>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>
                <div class="col-xs-6 col-md-3 text-right pull-right">
                    <a href="<?php echo zen_href_link(FILENAME_ORDERS); ?>" class="btn btn-xs btn-default"><?php echo DBX_VIEW_ALL; ?> <i class="fa fa-angle-double-right"></i></a>
                </div>
            </div>
        </div>
        <?php if ($orders->RecordCount() === 0) { ?>
        <div class="panel-body dbx-empty"><?php echo DBX_ORDERS_NONE; ?></div>
        <?php } else { ?>
        <div class="table-responsive dbx-recent-orders">
            <table class="table table-hover table-striped">
                <thead>
                <tr>
                    <th><?php echo DBX_ORDERS_ID; ?></th>
                    <th><?php echo DBX_ORDERS_CUSTOMER; ?></th>
                    <th><?php echo DBX_ORDERS_STATUS; ?></th>
                    <th class="text-right"><?php echo DBX_ORDERS_DATE; ?></th>
                    <th class="text-right"><?php echo DBX_ORDERS_TOTAL; ?></th>
                    <th class="text-right"><?php echo DBX_ORDERS_ACTIONS; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($orders as $order) {
                    $oID = (int)$order['orders_id'];
                    $name = zen_output_string_protected(str_replace('N/A', '', (string)$order['customers_name']));
                    $statusId = (int)$order['orders_status'];
                    $meta = $dbxStatusMeta[$statusId] ?? ['name' => (string)zen_get_orders_status_name($statusId), 'color' => ''];
                    $amount = $currencies->format((float)$order['order_value'], false);
                    if ($order['currency'] !== DEFAULT_CURRENCY && !empty($order['order_total'])) {
                        $amount .= '<br><small class="dbx-note">(' . zen_output_string_protected($order['order_total']) . ')</small>';
                    }

                    $lines = [];
                    $products = $db->Execute(
                        "SELECT orders_products_id, products_quantity, products_name, products_model"
                        . " FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = " . (int)$oID,
                        false, true, 300
                    );
                    foreach ($products as $product) {
                        $line = (int)$product['products_quantity'] . ' x ' . zen_output_string_protected($product['products_name']);
                        if (!empty($product['products_model'])) {
                            $line .= ' (' . zen_output_string_protected($product['products_model']) . ')';
                        }
                        if ($dbxWithAttributes) {
                            $attributes = $db->Execute(
                                "SELECT products_options, products_options_values FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES
                                . " WHERE orders_products_id = " . (int)$product['orders_products_id']
                                . " ORDER BY orders_products_attributes_id",
                                false, true, 300
                            );
                            foreach ($attributes as $attribute) {
                                if (!empty($attribute['products_options'])) {
                                    $line .= '<br>&nbsp;&nbsp;- ' . zen_output_string_protected($attribute['products_options'])
                                        . ': ' . zen_output_string_protected($attribute['products_options_values']);
                                }
                            }
                        }
                        $lines[] = $line;
                    }
                    $popover = htmlspecialchars(implode('<hr>', $lines), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr>
                        <td><strong>#<?php echo $oID; ?></strong></td>
                        <td><a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit'); ?>" class="link-text"><?php echo $name; ?></a></td>
                        <td><?php echo $dbxBadge($meta['name'], $statusId, $meta['color']); ?></td>
                        <td class="text-right"><?php echo zen_date_short($order['date_purchased']); ?></td>
                        <td class="text-right"><strong><?php echo $amount; ?></strong></td>
                        <td class="text-right">
                            <button type="button" tabindex="0" class="btn btn-xs btn-info" data-toggle="popover" data-trigger="focus" data-placement="left" data-html="true" title="<?php echo DBX_ORDERS_PRODUCTS; ?>" data-content="<?php echo $popover; ?>"><i class="fa fa-eye"></i></button>
                            <a href="<?php echo zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oID); ?>" target="_blank" rel="noopener" class="btn btn-xs btn-default" title="<?php echo DBX_ORDERS_PRINT_INVOICE; ?>"><i class="fa fa-print"></i></a>
                            <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit'); ?>" class="btn btn-xs btn-primary" title="<?php echo DBX_ORDERS_VIEW_ORDER; ?>"><i class="fa fa-pencil"></i></a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </div>
</div>
