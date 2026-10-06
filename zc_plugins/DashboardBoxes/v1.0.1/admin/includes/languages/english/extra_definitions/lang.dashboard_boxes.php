<?php
/**
 * Dashboard Boxes -- English strings.
 *
 * Every key carries the DBX_ prefix so nothing here can collide with a core
 * definition on any release, including v3.0.0 where the same dashboard is
 * core and defines its own BOX_* keys.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$define = [
    // the page
    'DBX_DRAG_HINT' => 'Drag a panel by its heading to move it. The layout is saved as you go.',
    'DBX_RESET_LAYOUT' => 'Reset layout',
    'DBX_RESET_CONFIRM' => 'Put every panel back where it started?',
    'DBX_LAYOUT_SAVED' => 'Layout saved',
    'DBX_LAYOUT_SAVE_FAILED' => 'The layout could not be saved. Reload the page and try again.',

    // today at a glance
    'DBX_WIDGET_KPI_CARDS' => 'Today at a Glance',
    'DBX_KPI_ORDERS_TODAY' => 'Orders Today',
    'DBX_KPI_REVENUE_TODAY' => 'Revenue Today',
    'DBX_KPI_CUSTOMERS_TODAY' => 'New Customers Today',
    'DBX_KPI_REVIEWS_PENDING' => 'Reviews Pending',
    'DBX_KPI_MORE_INFO' => 'More info',

    // sales
    'DBX_SALES_HEADING' => 'Sales',
    'DBX_SALES_SUBHEADING' => '(Last %s Days)',
    'DBX_SALES_LABEL_REVENUE' => 'Revenue',
    'DBX_SALES_LABEL_ORDERS' => 'Orders',
    'DBX_SALES_NUMBER_FORMAT' => 'en-US',

    // recent orders
    'DBX_ORDERS_HEADING' => 'Recent Orders',
    'DBX_VIEW_ALL' => 'View All',
    'DBX_ORDERS_ID' => 'ID',
    'DBX_ORDERS_CUSTOMER' => 'Customer',
    'DBX_ORDERS_STATUS' => 'Status',
    'DBX_ORDERS_DATE' => 'Date',
    'DBX_ORDERS_TOTAL' => 'Total',
    'DBX_ORDERS_ACTIONS' => 'Actions',
    'DBX_ORDERS_PRODUCTS' => 'Products',
    'DBX_ORDERS_PRINT_INVOICE' => 'Print Invoice',
    'DBX_ORDERS_VIEW_ORDER' => 'View Order',
    'DBX_ORDERS_NONE' => 'No orders yet.',

    // traffic
    'DBX_TRAFFIC_HEADING' => 'Traffic History',
    'DBX_TRAFFIC_SUBHEADING' => '(Last %s Days)',
    'DBX_TRAFFIC_SESSIONS' => 'Sessions (Visits)',
    'DBX_TRAFFIC_HITS' => 'Hits (Page Views)',
    'DBX_TRAFFIC_NO_DATA' => 'No traffic data available.',

    // orders by status
    'DBX_ORDER_STATUS_HEADING' => 'Orders by Status',
    'DBX_ORDER_STATUS_MANAGE' => 'Manage Order Statuses',

    // top sellers
    'DBX_TOP_SELLERS' => 'Top Sellers',
    'DBX_TOP_SELLERS_PERIOD' => '(30 Days)',
    'DBX_PRODUCTS_ID' => 'ID: ',
    'DBX_PRODUCTS_MODEL' => 'Model: ',
    'DBX_SOLD' => ' sold',
    'DBX_VIEW_FULL_REPORT' => 'View Full Report',
    'DBX_NO_SALES' => 'No sales in the last 30 days.',

    // who's online
    'DBX_WO_TITLE' => 'Who\'s Online',
    'DBX_WO_CUSTOMERS' => 'Customers',
    'DBX_WO_GUESTS' => 'Guests',
    'DBX_WO_SPIDERS' => 'Spiders',
    'DBX_WO_ACTIVE_WITH_CART' => 'Active with Cart',
    'DBX_WO_ACTIVE_BROWSING' => 'Active Browsing',
    'DBX_WO_IDLE_WITH_CART' => 'Idle with Cart',
    'DBX_WO_IDLE' => 'Idle',
    'DBX_WO_ACTIVE_SPIDERS' => 'Active Spiders',
    'DBX_WO_TOTAL' => 'Total Sessions:',

    // new customers
    'DBX_CUSTOMERS_HEADING' => 'New Customers',
    'DBX_CUSTOMERS_NONE' => 'No customers yet.',

    // sales and specials
    'DBX_SPECIALS_HEADING' => 'Sales and Specials',
    'DBX_SPECIALS_SPECIALS' => 'Specials',
    'DBX_SPECIALS_FEATURED' => 'Featured',
    'DBX_SPECIALS_SALEMAKER' => 'Salemaker Sales',
    'DBX_LABEL_ACTIVE' => 'Active',
    'DBX_LABEL_EXPIRED' => 'Expired',
    'DBX_LABEL_INACTIVE' => 'Inactive',

    // store snapshot
    'DBX_SNAPSHOT_HEADING' => 'Store Snapshot',
    'DBX_TITLE_PRODUCTS' => 'Products',
    'DBX_TITLE_CUSTOMERS' => 'Customers',
    'DBX_TITLE_REVIEWS' => 'Reviews',
    'DBX_TITLE_TOTAL_VISITS' => 'Total Visits',
    'DBX_LABEL_TOTAL_ACCOUNTS' => 'Total Accounts',
    'DBX_LABEL_NEWSLETTER_SUBSCRIBERS' => 'Newsletter Subscribers',
    'DBX_LABEL_TOTAL_REVIEWS' => 'Total Reviews',
    'DBX_LABEL_REVIEWS_PENDING' => 'Pending Approval',
    'DBX_SINCE_DATE' => 'Since %s',
];

return $define;
