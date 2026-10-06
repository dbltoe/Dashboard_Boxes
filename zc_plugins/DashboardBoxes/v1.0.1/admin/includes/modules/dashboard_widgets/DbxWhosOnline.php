<?php
/**
 * Dashboard Boxes -- Who's Online, the four states at a glance.
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

if (!zen_is_superuser() && !check_page(FILENAME_WHOS_ONLINE, '')) {
    return;
}
if (!class_exists('WhosOnline')) {
    return;
}

$dbxWhosOnline = new WhosOnline();
$dbxStats = $dbxWhosOnline->getStats();
// each array: 0 = active with cart, 1 = idle with cart, 2 = active browsing, 3 = idle
$dbxCounts = static function ($arr): array {
    $arr = is_array($arr) ? $arr : [];
    return [
        'active_cart' => (int)($arr[0] ?? 0),
        'idle_cart' => (int)($arr[1] ?? 0),
        'active_browse' => (int)($arr[2] ?? 0),
        'idle_browse' => (int)($arr[3] ?? 0),
    ];
};
$dbxUsers = $dbxCounts($dbxStats['user_array'] ?? []);
$dbxGuests = $dbxCounts($dbxStats['guest_array'] ?? []);
$dbxSpiders = $dbxCounts($dbxStats['spider_array'] ?? []);
$dbxTotal = (int)$dbxWhosOnline->getTotalSessions();

$dbxRow = static function (array $c): string {
    return '<div class="dbx-users-row">'
        . '<span class="label label-success" title="' . DBX_WO_ACTIVE_WITH_CART . '" data-toggle="tooltip"><i class="fa fa-shopping-cart"></i> ' . $c['active_cart'] . '</span>'
        . '<span class="label label-info" title="' . DBX_WO_ACTIVE_BROWSING . '" data-toggle="tooltip"><i class="fa fa-eye"></i> ' . $c['active_browse'] . '</span>'
        . '<span class="label label-warning" title="' . DBX_WO_IDLE_WITH_CART . '" data-toggle="tooltip"><i class="fa fa-shopping-cart"></i> ' . $c['idle_cart'] . '</span>'
        . '<span class="label label-default" title="' . DBX_WO_IDLE . '" data-toggle="tooltip"><i class="fa fa-clock-o"></i> ' . $c['idle_browse'] . '</span>'
        . '</div>';
};
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('WhosOnline'); ?>" data-dbx-widget="WhosOnline">
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-globe"></i> <?php echo DBX_WO_TITLE; ?>
            <div class="pull-right">
                <a href="<?php echo zen_href_link(FILENAME_WHOS_ONLINE); ?>" class="btn btn-xs btn-default"><?php echo DBX_VIEW_ALL; ?></a>
            </div>
        </div>
        <ul class="list-group dbx-whos-online">
            <li class="list-group-item">
                <h5 class="list-group-item-heading"><i class="fa fa-user text-primary"></i> <?php echo DBX_WO_CUSTOMERS; ?></h5>
                <?php echo $dbxRow($dbxUsers); ?>
            </li>
            <li class="list-group-item">
                <h5 class="list-group-item-heading"><i class="fa fa-users dbx-icon-quiet"></i> <?php echo DBX_WO_GUESTS; ?></h5>
                <?php echo $dbxRow($dbxGuests); ?>
            </li>
            <li class="list-group-item">
                <h5 class="list-group-item-heading"><i class="fa fa-bug text-danger"></i> <?php echo DBX_WO_SPIDERS; ?></h5>
                <div class="text-right">
                    <span class="label label-default" title="<?php echo DBX_WO_ACTIVE_SPIDERS; ?>" data-toggle="tooltip"><?php echo $dbxSpiders['active_browse'] + $dbxSpiders['active_cart']; ?> <?php echo DBX_LABEL_ACTIVE; ?></span>
                </div>
            </li>
        </ul>
        <div class="panel-footer text-center">
            <span class="dbx-legend"><?php echo DBX_WO_TOTAL; ?> <strong><?php echo $dbxTotal; ?></strong></span>
        </div>
    </div>
</div>
