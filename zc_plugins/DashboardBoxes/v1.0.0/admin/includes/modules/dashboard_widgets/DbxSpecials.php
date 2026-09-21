<?php
/**
 * Dashboard Boxes -- Sales and Specials: specials, featured products and
 * Salemaker sales, active and expired.
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

if (!zen_is_superuser() && !check_page(FILENAME_SALEMAKER, '')) {
    return;
}

$dbxCount = static function (string $table, string $column, int $status) use ($db): int {
    $result = $db->Execute("SELECT COUNT(*) AS total FROM " . $table . " WHERE " . $column . " = " . $status, false, true, 1800);
    return (int)$result->fields['total'];
};
$dbxRows = [
    [DBX_SPECIALS_SPECIALS, zen_href_link(FILENAME_SPECIALS), $dbxCount(TABLE_SPECIALS, 'status', 1), $dbxCount(TABLE_SPECIALS, 'status', 0)],
    [DBX_SPECIALS_FEATURED, zen_href_link(FILENAME_FEATURED), $dbxCount(TABLE_FEATURED, 'status', 1), $dbxCount(TABLE_FEATURED, 'status', 0)],
    [DBX_SPECIALS_SALEMAKER, zen_href_link(FILENAME_SALEMAKER), $dbxCount(TABLE_SALEMAKER_SALES, 'sale_status', 1), $dbxCount(TABLE_SALEMAKER_SALES, 'sale_status', 0)],
];
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('Specials'); ?>" data-dbx-widget="Specials">
    <div class="panel">
        <div class="panel-heading"><i class="fa fa-tags"></i> <?php echo DBX_SPECIALS_HEADING; ?></div>
        <ul class="list-group">
            <?php foreach ($dbxRows as $row) { ?>
            <li class="list-group-item">
                <a class="link-text" href="<?php echo $row[1]; ?>"><?php echo $row[0]; ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?php echo DBX_LABEL_ACTIVE; ?>" data-toggle="tooltip"><?php echo $row[2]; ?></span>
                    <span class="label label-default" title="<?php echo DBX_LABEL_EXPIRED; ?>" data-toggle="tooltip"><?php echo $row[3]; ?></span>
                </div>
            </li>
            <?php } ?>
        </ul>
        <div class="panel-footer text-center">
            <span class="dbx-legend">
                <span class="text-success"><i class="fa fa-square"></i> <?php echo DBX_LABEL_ACTIVE; ?></span> &nbsp;
                <span class="label-inactive-text"><i class="fa fa-square"></i> <?php echo DBX_LABEL_EXPIRED; ?></span>
            </span>
        </div>
    </div>
</div>
