<?php
/**
 * Dashboard Boxes -- what the dashboard page needs in its <head>.
 *
 * admin/includes/javascript_loader.php includes a plugin's <page>.php from
 * this directory on every release, inside its plugin loop, so $relativeDir is
 * the plugin's web path. The page is "index" for the dashboard, so this file
 * runs there and nowhere else. It emits nothing unless the observer has
 * already arranged the widgets on this request, which is also how it stays
 * silent on v3.0.0.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require_once dirname(__DIR__, 3) . '/lib/DashboardBoxesLib.php';

if (DashboardBoxesLib::isActive() && DashboardBoxesLib::effectiveLayout() !== null) {
    $dbxBase = (isset($relativeDir) && is_string($relativeDir) && $relativeDir !== '')
        ? $relativeDir
        : DashboardBoxesLib::relativePath();
    $dbxConfig = json_encode(
        DashboardBoxesLib::clientConfig(),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
?>
<script title="Dashboard Boxes: layout is active">document.documentElement.className += ' dbx-active';</script>
<script src="<?php echo htmlspecialchars($dbxBase, ENT_QUOTES, 'UTF-8'); ?>admin/includes/javascript/vendor/chart.umd.min.js"></script>
<script title="Dashboard Boxes: page configuration">window.dashboardBoxes = <?php echo $dbxConfig; ?>;</script>
<?php
}
