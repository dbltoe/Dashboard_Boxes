<?php
/**
 * Dashboard Boxes -- the admin observer.
 *
 * Auto-discovered by init_observers.php from this directory on every release,
 * so it must NOT also be registered by an auto_loader (that fatals every
 * admin page after install). It listens for the one notifier the stock
 * dashboard fires while building its widget list, and hands the list to the
 * library.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require_once dirname(__DIR__, 4) . '/lib/DashboardBoxesLib.php';

class zcObserverDashboardBoxes extends base
{
    public function __construct()
    {
        if (!DashboardBoxesLib::isActive()) {
            return;
        }
        $this->attach($this, ['NOTIFY_ADMIN_DASHBOARD_WIDGETS']);
    }

    /**
     * Fired from admin/index_dashboard.php with the mutable $widgets array
     * (v2.0.0 through v2.3). v3.0.0 also passes $zones, which this plugin
     * never sees because it does not attach there.
     */
    public function notify_admin_dashboard_widgets(&$class, $eventID, $param1, &$widgets)
    {
        if (is_array($widgets)) {
            DashboardBoxesLib::arrange($widgets);
        }
    }

    public function update(&$class, $eventID, $param1, &$widgets)
    {
        if ($eventID === 'NOTIFY_ADMIN_DASHBOARD_WIDGETS') {
            $this->notify_admin_dashboard_widgets($class, $eventID, $param1, $widgets);
        }
    }
}
