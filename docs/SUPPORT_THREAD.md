# Dashboard Boxes - support thread

Opening post for the Zen Cart forum support thread (Addon Admin Tools).
The forum takes Markdown. Not part of the release package.

Once posted, the opening-post permalink (`/threads/N?page=1#post-M`) goes
into `$dbxForumUrl` in manifest.php, where it becomes the third button in
the Plugin Manager panel, and into the Library listing. Set it before the
first release: on Zen Cart 2.0 and 2.1 the panel text is captured on a
store's first Plugin Manager scan and never refreshed.

Posted by John 2026-09-21 in Addon Admin Tools: https://www.zen-cart.com/threads/207350

---

**Dashboard Boxes v1.0.0** - the Zen Cart 3.0.0 admin dashboard for Zen Cart 2.0 through 2.3, as an encapsulated plugin

**Plugins Library:** (link once listed)
**GitHub:** https://github.com/dbltoe/Dashboard_Boxes
**Zen Cart:** 2.0.0, 2.0.1, 2.1.0, 2.2.0 through 2.2.3 and the 2.3 branch. Installs on 3.0.0 and stands aside, because that release's own dashboard is this design.
**PHP:** 8.0 through 8.5
**License:** GPL-2.0

**What it does**

Replaces the panels on the admin home page with the set Zen Cart 3.0.0 ships, laid out as a strip across the top, a wide main area and a sidebar:

- **Today at a Glance**: orders, revenue and new customers since midnight, and reviews awaiting approval, each card linking to its page.
- **Sales**: revenue and order count per day for the last 30 days, as a chart.
- **Recent Orders**: the latest orders with a status badge, a products popover, invoice and edit links, and quick-filter pills for Pending and Processing in the heading.
- **Traffic History**: sessions and hits per day from the store's counter.
- **Orders by Status**, **Top Sellers**, **Who's Online**, **New Customers**, **Sales and Specials** and **Store Snapshot**.

Drag any panel by its heading into the top strip, the main area or the sidebar. The arrangement is saved as you drop it, for you alone; every admin keeps their own. A *Reset layout* link puts the defaults back.

Order-status colors set under Localization > Orders Status (Zen Cart 2.2.1 and later) show as badges in the Recent Orders and Orders by Status panels.

**Encapsulated.** One folder under `zc_plugins/`, installed from Plugin Manager. No core file is changed, no core table is altered, nothing is loaded from a CDN. Uninstalling drops the plugin's one table and hands the dashboard back to core.

**Where it comes from**

The design is ZenExpert's Modern Dynamic Dashboard, which the Zen Cart team merged into core for 3.0.0. Full credit to ZenExpert for it; portions of the panel code carry their copyright. ZenExpert also offers the same dashboard for 2.x stores as a set of replacement admin files, and if that suits your store it is a fine choice. This plugin takes a different route to the same screen, and that route is what it adds:

- **Nothing in `admin/` is replaced.** The panels come in through the notifier the stock dashboard already fires. A Zen Cart upgrade cannot overwrite it, and Plugin Manager's Uninstall puts everything back.
- **Each admin's layout is kept in the plugin's own table**, so no column is added to a core table.
- **Chart.js is bundled** and served from the plugin directory; jQuery UI and Bootstrap are the copies core already loads. No CDN, no second copy of anything.
- **One package for 2.0.0 through 2.3**, verified on each release branch, with no branch to choose.
- **Admin profiles are respected panel by panel**: an admin without access to Orders sees no order panels, and the Today at a Glance strip drops the cards they may not see.
- **Other plugins can add a panel of their own** through the plugin's notifiers, or replace the layout about to be rendered, without editing this plugin.
- **A width per box** (full, two thirds or one third of the page) is carried with the layout and rendered as a wrapping row, ready for a layout controller to set; in this free plugin every box is full width.

**Installing**

1. Upload `zc_plugins/DashboardBoxes/` so it lands at `<store root>/zc_plugins/DashboardBoxes/v1.0.0/`.
2. Admin -> Modules -> Plugin Manager -> Dashboard Boxes -> Install.
3. Open the admin home page.

The full documentation (readme.html) is inside the plugin and linked from the Plugin Manager panel: the panels, arranging them, order-status colors, admin profiles, customizing, upgrading, uninstalling, troubleshooting and compatibility.

**Customizing**

The Recent Orders panel honors the same site-specific overrides the stock widget does (`$recentOrdersMaxRows`, `$recentOrdersWidgetOrderStatusIDs`, `$show_status_pills`, `$includeAttributesInPopoverRows`). Styling goes in `admin/includes/css/site-specific-styles.php`; every panel wrapper carries `data-dbx-widget="<Name>"` so a single panel can be targeted.

**Not included**

A controller page for widths, per-profile defaults and locked layouts, and further panels, are a separate Pro edition built on this plugin's notifiers. The dashboard for Zen Cart 1.5.8 is not covered: that release's admin home has no notifier to hook.

**Reporting a problem**

Please include: your Zen Cart and PHP versions, whether the store is behind a CDN or a caching proxy, and what the admin home page shows. If the panels are there but sit in three equal columns and will not drag, the plugin's stylesheet or script did not load; check that `zc_plugins/DashboardBoxes/v1.0.0/admin/includes/css/index.css` opens in a browser. If the dashboard looks like the previous version after an upgrade, hard-refresh: browsers keep the plugin's stylesheet for an hour. Anything else: look in `logs/` for a `myDEBUG-adm-` file from the moment of the problem and attach it.
