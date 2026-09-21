<?php
/**
 * Dashboard Boxes -- Top Sellers, the last 30 days.
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

if (!zen_is_superuser() && !check_page(FILENAME_STATS_PRODUCTS_PURCHASED, '')) {
    return;
}

$dbxFrom = date('Y-m-d', strtotime('-30 days')) . ' 00:00:00';
$dbxTop = $db->Execute(
    "SELECT p.products_id, pd.products_name, p.products_image, p.products_model, SUM(op.products_quantity) AS total_sold"
    . " FROM " . TABLE_ORDERS_PRODUCTS . " op"
    . " INNER JOIN " . TABLE_ORDERS . " o ON op.orders_id = o.orders_id"
    . " INNER JOIN " . TABLE_PRODUCTS . " p ON op.products_id = p.products_id"
    . " INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id AND pd.language_id = " . (int)$_SESSION['languages_id'] . ")"
    . " WHERE o.date_purchased >= '" . $db->prepare_input($dbxFrom) . "'"
    . " GROUP BY p.products_id, pd.products_name, p.products_image, p.products_model"
    . " ORDER BY total_sold DESC",
    5, true, 1800
);
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('MostPopular'); ?>" data-dbx-widget="MostPopular">
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-trophy"></i> <?php echo DBX_TOP_SELLERS; ?> <small><?php echo DBX_TOP_SELLERS_PERIOD; ?></small>
        </div>
        <?php if ($dbxTop->RecordCount() > 0) { ?>
            <ul class="list-group">
                <?php foreach ($dbxTop as $product) {
                    $pID = (int)$product['products_id'];
                    $name = (string)$product['products_name'];
                    $image = (string)$product['products_image'];
                    $model = (string)$product['products_model'];
                    $edit = zen_href_link(FILENAME_PRODUCT, 'action=new_product&pID=' . $pID);
                    if ($image === '' || !is_file(DIR_FS_CATALOG_IMAGES . $image)) {
                        $thumb = '<div class="dbx-top-fallback"><i class="fa fa-image"></i></div>';
                    } else {
                        $thumb = zen_image(DIR_WS_CATALOG_IMAGES . $image, $name, 40, 40, 'class="dbx-top-image"');
                    }
                    ?>
                    <li class="list-group-item dbx-top-item">
                        <div class="media">
                            <div class="media-left media-middle"><a href="<?php echo $edit; ?>"><?php echo $thumb; ?></a></div>
                            <div class="media-body media-middle">
                                <h4 class="media-heading"><a href="<?php echo $edit; ?>"><?php echo zen_trunc_string(zen_output_string_protected($name), 35, true); ?></a></h4>
                                <span class="dbx-muted"><?php echo DBX_PRODUCTS_ID . $pID . ($model !== '' ? ' | ' . DBX_PRODUCTS_MODEL . zen_output_string_protected($model) : ''); ?></span>
                            </div>
                            <div class="media-right media-middle text-right">
                                <span class="badge dbx-bg-green"><?php echo (int)$product['total_sold'] . DBX_SOLD; ?></span>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
            <div class="panel-footer text-center">
                <a href="<?php echo zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED); ?>" class="btn btn-default btn-xs btn-block"><?php echo DBX_VIEW_FULL_REPORT; ?></a>
            </div>
        <?php } else { ?>
            <div class="panel-body dbx-empty">
                <br><i class="fa fa-frown-o fa-2x"></i><br><br><?php echo DBX_NO_SALES; ?>
            </div>
        <?php } ?>
    </div>
</div>
