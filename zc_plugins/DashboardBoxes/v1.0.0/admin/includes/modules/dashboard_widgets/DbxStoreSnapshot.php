<?php
/**
 * Dashboard Boxes -- Store Snapshot: products, customers, reviews and the
 * all-time visit counter.
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

$dbxSeeCustomers = zen_is_superuser() || check_page(FILENAME_CUSTOMERS, '');
$dbxSeeProducts = zen_is_superuser() || check_page(FILENAME_PRODUCT, '');
$dbxSeeReviews = zen_is_superuser() || check_page(FILENAME_REVIEWS, '');

$dbxCount = static function (string $sql) use ($db): int {
    $result = $db->Execute($sql, false, true, 1800);
    return (int)$result->fields['total'];
};
$dbxProductsOn = $dbxSeeProducts ? $dbxCount("SELECT COUNT(*) AS total FROM " . TABLE_PRODUCTS . " WHERE products_status = 1") : 0;
$dbxProductsOff = $dbxSeeProducts ? $dbxCount("SELECT COUNT(*) AS total FROM " . TABLE_PRODUCTS . " WHERE products_status = 0") : 0;
$dbxCustomers = $dbxSeeCustomers ? $dbxCount("SELECT COUNT(*) AS total FROM " . TABLE_CUSTOMERS) : 0;
$dbxNewsletter = $dbxSeeCustomers ? $dbxCount("SELECT COUNT(*) AS total FROM " . TABLE_CUSTOMERS . " WHERE customers_newsletter = 1") : 0;
$dbxReviews = $dbxSeeReviews ? $dbxCount("SELECT COUNT(*) AS total FROM " . TABLE_REVIEWS) : 0;
$dbxReviewsPending = $dbxSeeReviews ? $dbxCount("SELECT COUNT(*) AS total FROM " . TABLE_REVIEWS . " WHERE status = 0") : 0;

$dbxCounter = 0;
$dbxCounterSince = '';
$result = $db->Execute("SELECT startdate, counter FROM " . TABLE_COUNTER, false, true, 7200);
if ($result->RecordCount() > 0) {
    $dbxCounter = (int)$result->fields['counter'];
    $raw = (string)$result->fields['startdate'];
    if (strlen($raw) === 8 && ctype_digit($raw)) {
        $dbxCounterSince = DashboardBoxesLib::shortDate(
            mktime(0, 0, 0, (int)substr($raw, 4, 2), (int)substr($raw, 6, 2), (int)substr($raw, 0, 4)),
            true
        );
    }
}
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('StoreSnapshot'); ?>" data-dbx-widget="StoreSnapshot">
    <div class="panel">
        <div class="panel-heading"><i class="fa fa-hdd-o"></i> <?php echo DBX_SNAPSHOT_HEADING; ?></div>
        <ul class="list-group">
            <?php if ($dbxSeeProducts) { ?>
            <li class="list-group-item">
                <a class="link-text" href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER); ?>"><?php echo DBX_TITLE_PRODUCTS; ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?php echo DBX_LABEL_ACTIVE; ?>" data-toggle="tooltip"><?php echo $dbxProductsOn; ?></span>
                    <span class="label label-default" title="<?php echo DBX_LABEL_INACTIVE; ?>" data-toggle="tooltip"><?php echo $dbxProductsOff; ?></span>
                </div>
            </li>
            <?php } ?>
            <?php if ($dbxSeeCustomers) { ?>
            <li class="list-group-item">
                <a class="link-text" href="<?php echo zen_href_link(FILENAME_CUSTOMERS); ?>"><?php echo DBX_TITLE_CUSTOMERS; ?></a>
                <div class="pull-right">
                    <span class="label label-info" title="<?php echo DBX_LABEL_TOTAL_ACCOUNTS; ?>" data-toggle="tooltip"><?php echo $dbxCustomers; ?></span>
                    <span class="label label-warning" title="<?php echo DBX_LABEL_NEWSLETTER_SUBSCRIBERS; ?>" data-toggle="tooltip"><i class="fa fa-envelope"></i> <?php echo $dbxNewsletter; ?></span>
                </div>
            </li>
            <?php } ?>
            <?php if ($dbxSeeReviews) { ?>
            <li class="list-group-item">
                <a class="link-text" href="<?php echo zen_href_link(FILENAME_REVIEWS); ?>"><?php echo DBX_TITLE_REVIEWS; ?></a>
                <div class="pull-right">
                    <span class="label label-primary" title="<?php echo DBX_LABEL_TOTAL_REVIEWS; ?>" data-toggle="tooltip"><?php echo $dbxReviews; ?></span>
                    <?php if ($dbxReviewsPending > 0) { ?>
                    <span class="label label-danger" title="<?php echo DBX_LABEL_REVIEWS_PENDING; ?>" data-toggle="tooltip"><?php echo $dbxReviewsPending; ?></span>
                    <?php } ?>
                </div>
            </li>
            <?php } ?>
            <li class="list-group-item">
                <span class="link-text"><?php echo DBX_TITLE_TOTAL_VISITS; ?></span>
                <?php if ($dbxCounterSince !== '') { ?><span class="dbx-muted"><?php echo sprintf(DBX_SINCE_DATE, zen_output_string_protected($dbxCounterSince)); ?></span><?php } ?>
                <div class="pull-right" style="margin-top: -15px;">
                    <span class="badge dbx-counter-badge"><?php echo number_format($dbxCounter); ?></span>
                </div>
            </li>
        </ul>
        <div class="panel-footer text-center">
            <span class="dbx-legend">
                <span class="text-success"><i class="fa fa-square"></i> <?php echo DBX_LABEL_ACTIVE; ?></span> &nbsp;
                <span class="label-inactive-text"><i class="fa fa-square"></i> <?php echo DBX_LABEL_INACTIVE; ?></span>
            </span>
        </div>
    </div>
</div>
