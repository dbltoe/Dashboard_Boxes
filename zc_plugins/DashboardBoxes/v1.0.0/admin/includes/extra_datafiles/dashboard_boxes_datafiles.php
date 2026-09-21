<?php
/**
 * Dashboard Boxes -- the one table the plugin owns.
 *
 * Loaded per plugin by admin/includes/application_bootstrap.php on every
 * release from v2.0.0; guarded because the library falls back to the same
 * name when it has to stand alone.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (!defined('TABLE_DASHBOARD_BOXES_LAYOUT')) {
    define('TABLE_DASHBOARD_BOXES_LAYOUT', DB_PREFIX . 'dashboard_boxes_layout');
}
