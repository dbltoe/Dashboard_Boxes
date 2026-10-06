<?php
/**
 * Dashboard Boxes -- New Customers, the latest accounts.
 *
 * Portions copyright the Zen Cart Development Team.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
require_once dirname(__DIR__, 4) . '/lib/DashboardBoxesLib.php';

if (!zen_is_superuser() && !check_page(FILENAME_CUSTOMERS, '')) {
    return;
}

$dbxCustomers = $db->Execute(
    "SELECT c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address,"
    . " ci.customers_info_date_account_created"
    . " FROM " . TABLE_CUSTOMERS . " c"
    . " INNER JOIN " . TABLE_CUSTOMERS_INFO . " ci ON ci.customers_info_id = c.customers_id"
    . " ORDER BY ci.customers_info_date_account_created DESC",
    10, true, 300
);
?>
<div class="<?php echo DashboardBoxesLib::boxClasses('RecentCustomers'); ?>" data-dbx-widget="RecentCustomers">
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-user-plus"></i> <?php echo DBX_CUSTOMERS_HEADING; ?>
            <div class="pull-right">
                <a href="<?php echo zen_href_link(FILENAME_CUSTOMERS); ?>" class="btn btn-xs btn-default"><?php echo DBX_VIEW_ALL; ?></a>
            </div>
        </div>
        <?php if ($dbxCustomers->RecordCount() === 0) { ?>
        <div class="panel-body dbx-empty"><?php echo DBX_CUSTOMERS_NONE; ?></div>
        <?php } else { ?>
        <table class="table table-striped table-condensed">
            <?php foreach ($dbxCustomers as $customer) {
                $name = zen_output_string_protected(trim($customer['customers_firstname'] . ' ' . $customer['customers_lastname']));
                $link = zen_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$customer['customers_id'] . '&action=edit');
                ?>
                <tr>
                    <td><a href="<?php echo $link; ?>" class="link-text"><?php echo $name; ?></a></td>
                    <td class="text-right"><?php echo zen_date_short($customer['customers_info_date_account_created']); ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php } ?>
    </div>
</div>
