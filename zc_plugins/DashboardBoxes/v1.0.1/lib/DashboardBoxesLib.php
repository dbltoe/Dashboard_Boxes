<?php
/**
 * Dashboard Boxes -- the one place the plugin's logic lives.
 *
 * Every entry point (the admin observer, the AJAX class, the dashboard head
 * file) pulls this in with require_once by its own __DIR__, because Zen Cart
 * 3.0.0 no longer auto-loads a plugin's admin extra_functions and nothing
 * here may depend on a loader that one release lacks.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (!class_exists('DashboardBoxesLib', false)) {

final class DashboardBoxesLib
{
    public const PLUGIN_KEY = 'DashboardBoxes';

    /**
     * The three zones and the core dashboard column each one is rendered
     * into. Zen Cart 2.x renders its dashboard as three equal Bootstrap
     * columns (#colone, #coltwo, #colthree) in that DOM order. The plugin's
     * stylesheet makes the first one full width and the other two a 2:1
     * pair, so the first column is the strip across the top, the second the
     * wide main area and the third the sidebar -- with no core file touched
     * and nothing moved by script.
     */
    public const ZONES = ['top', 'main', 'side'];
    public const ZONE_COLUMNS = ['top' => 1, 'main' => 2, 'side' => 3];
    public const COLUMN_IDS = ['top' => 'colone', 'main' => 'coltwo', 'side' => 'colthree'];

    /**
     * Box widths, as Bootstrap column counts of the PAGE: 12 is full, 8 is
     * two thirds (the main area's width), 4 is one third (the sidebar's).
     * A box keeps the same physical size whichever zone it lands in, and a
     * box wider than its zone simply renders full there. Free carries the
     * model and renders it; a layout controller sets it.
     */
    public const WIDTHS = [12, 8, 4];
    public const FULL_WIDTH = 12;
    public const ZONE_WIDTHS = ['top' => 12, 'main' => 8, 'side' => 4];

    /** The core widgets this plugin replaces; they are hidden, not removed. */
    public const CORE_WIDGETS = [
        'BaseStatisticsDashboardWidget.php',
        'SpecialsDashboardWidget.php',
        'OrderStatusDashboardWidget.php',
        'RecentCustomersDashboardWidget.php',
        'WhosOnlineDashboardWidget.php',
        'TrafficDashboardWidget.php',
        'RecentOrdersDashboardWidget.php',
        'SalesReportDashboardWidget.php',
        'MostPopularProductsDashboardWidget.php',
    ];

    /** A widget name as it travels between the page, the AJAX call and the table. */
    public const NAME_PATTERN = '/^[A-Za-z][A-Za-z0-9_]{0,39}$/';

    /** The table's own name; the datafiles file defines the constant when it is loaded. */
    public const TABLE_SUFFIX = 'dashboard_boxes_layout';

    /** @var array|null the layout as it was rendered, for the page script */
    private static $effective = null;

    /** @var array the CSS width class of every box arrange() placed, by name */
    private static $boxClasses = [];

    /** @var array|null the registry, once built (and offered to other plugins) */
    private static $registry = null;

    /** @var bool|null whether orders_status carries a color column (v2.2.1+) */
    private static $statusColors = null;

    public static function pluginDir(): string
    {
        return str_replace('\\', '/', dirname(__DIR__));
    }

    public static function version(): string
    {
        return basename(self::pluginDir());
    }

    /** The plugin's web path, for the head file's script and stylesheet links. */
    public static function relativePath(): string
    {
        $catalog = defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/';
        return $catalog . 'zc_plugins/' . self::PLUGIN_KEY . '/' . self::version() . '/';
    }

    /**
     * Zen Cart 2.0.0 through 2.3: the releases whose dashboard needs this.
     * 3.0.0 ships the same design in core, with its own per-admin layout
     * store, so there the plugin installs and stands aside.
     */
    public static function isActive(): bool
    {
        if (!defined('PROJECT_VERSION_MAJOR')) {
            return false;
        }
        return (int)PROJECT_VERSION_MAJOR === 2;
    }

    public static function tableName(): string
    {
        if (defined('TABLE_DASHBOARD_BOXES_LAYOUT')) {
            return TABLE_DASHBOARD_BOXES_LAYOUT;
        }
        return (defined('DB_PREFIX') ? DB_PREFIX : '') . self::TABLE_SUFFIX;
    }

    /** Does this store's orders_status table carry the v2.2.1+ color column? */
    public static function statusColorsSupported(): bool
    {
        if (self::$statusColors === null) {
            global $sniffer;
            self::$statusColors = defined('TABLE_ORDERS_STATUS')
                && is_object($sniffer)
                && method_exists($sniffer, 'field_exists')
                && $sniffer->field_exists(TABLE_ORDERS_STATUS, 'orders_status_color_code');
        }
        return self::$statusColors;
    }

    /**
     * The widgets the plugin ships, in their default zones and order.
     *
     * 'title' is what a layout controller (the Pro plugin) shows; 'file' is
     * relative to admin/includes/modules/dashboard_widgets/ unless 'path' is
     * given outright, which is how another plugin adds a widget of its own.
     */
    public static function defaultRegistry(): array
    {
        $t = static function ($constant, $fallback) {
            return defined($constant) ? constant($constant) : $fallback;
        };
        return [
            'KpiCards' => ['file' => 'DbxKpiCards.php', 'zone' => 'top', 'sort' => 10, 'title' => $t('DBX_WIDGET_KPI_CARDS', 'Today at a glance')],
            'SalesReport' => ['file' => 'DbxSalesReport.php', 'zone' => 'main', 'sort' => 20, 'title' => $t('DBX_SALES_HEADING', 'Sales')],
            'RecentOrders' => ['file' => 'DbxRecentOrders.php', 'zone' => 'main', 'sort' => 10, 'title' => $t('DBX_ORDERS_HEADING', 'Recent Orders')],
            'Traffic' => ['file' => 'DbxTraffic.php', 'zone' => 'main', 'sort' => 30, 'title' => $t('DBX_TRAFFIC_HEADING', 'Traffic History')],
            'OrderStatus' => ['file' => 'DbxOrderStatus.php', 'zone' => 'side', 'sort' => 10, 'title' => $t('DBX_ORDER_STATUS_HEADING', 'Orders by Status')],
            'MostPopular' => ['file' => 'DbxMostPopular.php', 'zone' => 'side', 'sort' => 20, 'title' => $t('DBX_TOP_SELLERS', 'Top Sellers')],
            'WhosOnline' => ['file' => 'DbxWhosOnline.php', 'zone' => 'side', 'sort' => 30, 'title' => $t('DBX_WO_TITLE', 'Who is Online')],
            'RecentCustomers' => ['file' => 'DbxRecentCustomers.php', 'zone' => 'side', 'sort' => 40, 'title' => $t('DBX_CUSTOMERS_HEADING', 'New Customers')],
            'Specials' => ['file' => 'DbxSpecials.php', 'zone' => 'side', 'sort' => 50, 'title' => $t('DBX_SPECIALS_HEADING', 'Sales and Specials')],
            'StoreSnapshot' => ['file' => 'DbxStoreSnapshot.php', 'zone' => 'side', 'sort' => 60, 'title' => $t('DBX_SNAPSHOT_HEADING', 'Store Snapshot')],
        ];
    }

    /**
     * The registry as used: defaults, resolved to absolute paths, then offered
     * to other plugins through NOTIFY_DASHBOARD_BOXES_REGISTRY, which may add,
     * remove or re-home entries. Only entries whose file exists survive.
     */
    public static function registry(): array
    {
        if (self::$registry !== null) {
            return self::$registry;
        }
        $registry = self::defaultRegistry();
        $base = self::pluginDir() . '/admin/includes/modules/dashboard_widgets/';
        foreach ($registry as $name => $entry) {
            $registry[$name]['path'] = $base . $entry['file'];
        }

        global $zco_notifier;
        if (is_object($zco_notifier) && method_exists($zco_notifier, 'notify')) {
            $zco_notifier->notify('NOTIFY_DASHBOARD_BOXES_REGISTRY', null, $registry);
        }

        $clean = [];
        foreach ($registry as $name => $entry) {
            if (!is_string($name) || preg_match(self::NAME_PATTERN, $name) !== 1 || !is_array($entry)) {
                continue;
            }
            if (empty($entry['path']) || !is_file($entry['path'])) {
                continue;
            }
            $clean[$name] = [
                'path' => $entry['path'],
                'zone' => in_array($entry['zone'] ?? '', self::ZONES, true) ? $entry['zone'] : 'side',
                'sort' => (int)($entry['sort'] ?? 999),
                'title' => (string)($entry['title'] ?? $name),
                // the box's width when no layout says otherwise
                'width' => in_array((int)($entry['width'] ?? 0), self::WIDTHS, true) ? (int)$entry['width'] : self::FULL_WIDTH,
            ];
        }
        return self::$registry = $clean;
    }

    /** Every registered widget in its default zone, in default order. */
    public static function defaultLayout(array $registry): array
    {
        $layout = ['zones' => array_fill_keys(self::ZONES, []), 'hidden' => [], 'widths' => []];
        $names = array_keys($registry);
        usort($names, static function ($a, $b) use ($registry) {
            $bySort = $registry[$a]['sort'] <=> $registry[$b]['sort'];
            return $bySort !== 0 ? $bySort : strcmp($a, $b);
        });
        foreach ($names as $name) {
            $layout['zones'][$registry[$name]['zone']][] = $name;
        }
        return $layout;
    }

    /**
     * Whatever came in (a decoded JSON blob, a stale row, another plugin's
     * idea) becomes a layout that names only registered widgets, each once,
     * in known zones, with every registered widget that was not placed
     * appended to its default zone. A widget in 'hidden' is not rendered.
     * 'widths' keeps only registered names with a value from WIDTHS; a box
     * with no entry takes its registry width.
     */
    public static function normalize($layout, array $registry): array
    {
        $out = ['zones' => array_fill_keys(self::ZONES, []), 'hidden' => [], 'widths' => []];
        $seen = [];
        $zones = is_array($layout) && isset($layout['zones']) && is_array($layout['zones']) ? $layout['zones'] : [];
        foreach (self::ZONES as $zone) {
            if (empty($zones[$zone]) || !is_array($zones[$zone])) {
                continue;
            }
            foreach ($zones[$zone] as $name) {
                if (!is_string($name) || !isset($registry[$name]) || isset($seen[$name])) {
                    continue;
                }
                $seen[$name] = true;
                $out['zones'][$zone][] = $name;
            }
        }
        $hidden = is_array($layout) && isset($layout['hidden']) && is_array($layout['hidden']) ? $layout['hidden'] : [];
        foreach ($hidden as $name) {
            if (is_string($name) && isset($registry[$name]) && !in_array($name, $out['hidden'], true)) {
                $out['hidden'][] = $name;
            }
        }
        $widths = is_array($layout) && isset($layout['widths']) && is_array($layout['widths']) ? $layout['widths'] : [];
        foreach ($widths as $name => $width) {
            if (is_string($name) && isset($registry[$name]) && is_numeric($width) && in_array((int)$width, self::WIDTHS, true)) {
                $out['widths'][$name] = (int)$width;
            }
        }
        $defaults = self::defaultLayout($registry);
        foreach ($defaults['zones'] as $zone => $names) {
            foreach ($names as $name) {
                if (!isset($seen[$name])) {
                    $seen[$name] = true;
                    $out['zones'][$zone][] = $name;
                }
            }
        }
        return $out;
    }

    /** The width a box is asked for: the layout's entry, else its registry default. */
    public static function boxWidth(string $name, array $layout, array $registry): int
    {
        if (isset($layout['widths'][$name])) {
            return (int)$layout['widths'][$name];
        }
        return isset($registry[$name]) ? (int)$registry[$name]['width'] : self::FULL_WIDTH;
    }

    /**
     * The CSS class for a box of $width columns rendered in $zone: the share
     * of the zone it takes, and never more than the zone.
     */
    public static function widthClass(int $width, string $zone): string
    {
        $zoneWidth = self::ZONE_WIDTHS[$zone] ?? self::FULL_WIDTH;
        $share = min($width, $zoneWidth) / $zoneWidth;
        if ($share >= 1) {
            return 'dbx-w100';
        }
        if ($share >= 0.66) {
            return 'dbx-w66';
        }
        if ($share >= 0.5) {
            return 'dbx-w50';
        }
        return 'dbx-w33';
    }

    /**
     * What a panel puts in its wrapper's class attribute. Full width until
     * arrange() has placed the box, so a panel rendered outside the dashboard
     * (or by a layout controller's preview) still looks right.
     */
    public static function boxClasses(string $name): string
    {
        return 'dbx-widget ' . (self::$boxClasses[$name] ?? 'dbx-w100');
    }

    public static function loadLayout(int $adminId, array $registry): array
    {
        global $db;
        $stored = null;
        if ($adminId > 0 && is_object($db)) {
            $result = $db->Execute(
                'SELECT layout FROM ' . self::tableName() . ' WHERE admin_id = ' . (int)$adminId . ' LIMIT 1'
            );
            if (!$result->EOF && !empty($result->fields['layout'])) {
                $stored = json_decode($result->fields['layout'], true);
            }
        }
        return self::normalize($stored, $registry);
    }

    public static function saveLayout(int $adminId, array $layout, array $registry): array
    {
        global $db;
        $clean = self::normalize($layout, $registry);
        if ($adminId > 0 && is_object($db)) {
            $json = json_encode($clean);
            $db->Execute(
                'INSERT INTO ' . self::tableName() . ' (admin_id, layout, last_modified)'
                . " VALUES (" . (int)$adminId . ", '" . $db->prepare_input($json) . "', now())"
                . " ON DUPLICATE KEY UPDATE layout = '" . $db->prepare_input($json) . "', last_modified = now()"
            );
        }
        return $clean;
    }

    public static function resetLayout(int $adminId): void
    {
        global $db;
        if ($adminId > 0 && is_object($db)) {
            $db->Execute('DELETE FROM ' . self::tableName() . ' WHERE admin_id = ' . (int)$adminId);
        }
    }

    /**
     * The NOTIFY_ADMIN_DASHBOARD_WIDGETS handler: hide the core widgets the
     * plugin replaces, then add the plugin's own in the zones and order this
     * admin last left them. Other plugins' widgets are left exactly as they
     * were. NOTIFY_DASHBOARD_BOXES_LAYOUT lets a layout controller replace
     * what was loaded (a profile default, a locked layout) before it is used.
     */
    public static function arrange(array &$widgets): void
    {
        if (!self::isActive()) {
            return;
        }
        foreach ($widgets as $key => $widget) {
            if (isset($widget['path']) && in_array(basename((string)$widget['path']), self::CORE_WIDGETS, true)) {
                $widgets[$key]['visible'] = false;
            }
        }

        $registry = self::registry();
        $adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
        $layout = self::loadLayout($adminId, $registry);

        global $zco_notifier;
        if (is_object($zco_notifier) && method_exists($zco_notifier, 'notify')) {
            $zco_notifier->notify('NOTIFY_DASHBOARD_BOXES_LAYOUT', $adminId, $layout, $registry);
            $layout = self::normalize($layout, $registry);
        }

        self::$boxClasses = [];
        foreach (self::ZONES as $zone) {
            $position = 0;
            foreach ($layout['zones'][$zone] as $name) {
                $position += 10;
                if (in_array($name, $layout['hidden'], true)) {
                    continue;
                }
                self::$boxClasses[$name] = self::widthClass(self::boxWidth($name, $layout, $registry), $zone);
                $widgets[] = [
                    'column' => self::ZONE_COLUMNS[$zone],
                    'sort' => $position,
                    'visible' => true,
                    'path' => $registry[$name]['path'],
                    'dbx' => $name,
                ];
            }
        }
        self::$effective = $layout;
    }

    /** Null until arrange() has run on this request. */
    public static function effectiveLayout(): ?array
    {
        return self::$effective;
    }

    /** What the page script needs, as one JSON object. */
    public static function clientConfig(): array
    {
        $t = static function ($constant, $fallback) {
            return defined($constant) ? constant($constant) : $fallback;
        };
        $titles = [];
        $defaultWidths = [];
        foreach (self::registry() as $name => $entry) {
            $titles[$name] = $entry['title'];
            $defaultWidths[$name] = $entry['width'];
        }
        return [
            'columns' => self::COLUMN_IDS,
            'zoneWidths' => self::ZONE_WIDTHS,
            'defaultWidths' => $defaultWidths,
            'layout' => self::$effective,
            'titles' => $titles,
            'text' => [
                'hint' => $t('DBX_DRAG_HINT', 'Drag a panel by its heading to move it. The layout is saved as you go.'),
                'reset' => $t('DBX_RESET_LAYOUT', 'Reset layout'),
                'resetConfirm' => $t('DBX_RESET_CONFIRM', 'Put every panel back where it started?'),
                'saved' => $t('DBX_LAYOUT_SAVED', 'Layout saved'),
                'saveFailed' => $t('DBX_LAYOUT_SAVE_FAILED', 'The layout could not be saved. Reload the page and try again.'),
            ],
        ];
    }

    /**
     * The CSRF check the AJAX class applies, kept here so it can be tested
     * without Zen Cart.
     */
    public static function tokenIsValid($submitted): bool
    {
        if (!is_string($submitted) || $submitted === '') {
            return false;
        }
        if (!isset($_SESSION['securityToken']) || !is_string($_SESSION['securityToken']) || $_SESSION['securityToken'] === '') {
            return false;
        }
        return hash_equals($_SESSION['securityToken'], $submitted);
    }

    /**
     * A hex color from the orders_status table, or '' when it is not one.
     * Everything that reaches a style attribute goes through here.
     */
    public static function cssColor($value): string
    {
        $value = is_string($value) ? trim($value) : '';
        return preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $value) === 1 ? $value : '';
    }

    /** A date label for a chart axis, using the admin's own date formatter. */
    public static function shortDate(int $timestamp, bool $withYear = false): string
    {
        global $zcDate;
        $format = $withYear
            ? (defined('DATE_FORMAT_SHORT_NO_DAY') ? DATE_FORMAT_SHORT_NO_DAY : '%B %Y')
            : (defined('DATE_FORMAT_SHORT_NO_YEAR') ? DATE_FORMAT_SHORT_NO_YEAR : '%m/%d');
        if (is_object($zcDate) && method_exists($zcDate, 'output')) {
            return (string)$zcDate->output($format, $timestamp);
        }
        return date($withYear ? 'F Y' : 'm/d', $timestamp);
    }
}

}
