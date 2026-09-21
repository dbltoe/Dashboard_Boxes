# Dashboard Boxes 1.0.0

An encapsulated Zen Cart plugin that gives the admin home page of **Zen Cart
2.0.0 through 2.3** the dashboard Zen Cart 3.0.0 ships: a strip of today's
numbers across the top, a sales chart, recent orders with status badges and
quick filters, top sellers, traffic, who's online, new customers, sales and
specials, and a store snapshot, in panels you drag into the arrangement you
want. Every admin keeps their own layout.

**No core file is changed.** Install it from Plugin Manager and the dashboard
is there; uninstall it and the stock dashboard is back, with nothing left
behind.

Runs on Zen Cart v2.0.0 through v2.3 and PHP 8.0 through 8.5 from a single
codebase, verified on every release branch rather than assumed. On Zen Cart
3.0.0, where this dashboard is part of core, the plugin installs and stands
aside. See [docs/COMPATIBILITY.md](docs/COMPATIBILITY.md).

---

## The story behind it

ZenExpert's *Modern Dynamic Dashboard* was merged into Zen Cart core in 2026
and ships with 3.0.0. For the 2.x releases it exists only as a package that
overwrites thirty-one core admin files, and the copy on GitHub replaces 2.2's
`orders.php` and `general.php` with 2.1.0's, which kills every Configuration
page on a 2.2 store. That is not something to put on a client's site.

This plugin is the same dashboard, built the way a plugin should be: it uses
the notifier the stock dashboard already fires, ships its own panels,
stylesheet and script, keeps its layouts in a table of its own, and touches
nothing else.

## What it does

```
+---------------------------------------------------------------+
|  Orders today  |  Revenue today  |  New customers  |  Reviews  |
+---------------------------------------+-----------------------+
|  Recent orders, with status badges    |  Orders by status     |
|  and quick filters                    |  Top sellers          |
|                                       |  Who's online         |
|  Sales, last 30 days (chart)          |  New customers        |
|                                       |  Sales and specials   |
|  Traffic history (chart)              |  Store snapshot       |
+---------------------------------------+-----------------------+
```

Drag any panel by its heading into the top strip, the main area or the
sidebar. The arrangement is saved as you go, per admin. A *Reset layout*
link puts the defaults back.

Order-status colors set in **Admin > Localization > Orders Status** (Zen Cart
2.2.1 and later) show as badges in the Recent Orders and Orders by Status
panels. On 2.0 and 2.1, which have no color column, the panels use plain
Bootstrap badges.

## Installing

Upload `zc_plugins/DashboardBoxes/` to your store, open **Admin > Modules >
Plugin Manager**, select *Dashboard Boxes* and click **Install**. Then open
the admin home page. Details in [docs/INSTALL.md](docs/INSTALL.md).

## Uninstalling

Plugin Manager > *Dashboard Boxes* > **Uninstall** drops the plugin's layout
table and hands the dashboard back to core. Delete the
`zc_plugins/DashboardBoxes/` directory afterwards if you like.

## Support

Questions and problem reports go in the plugin's thread on the Zen Cart
forum: https://www.zen-cart.com/threads/207350. Please include your Zen
Cart and PHP versions and what the admin home page shows.

## For other plugins

A plugin can register a panel of its own, or take over the layout, through
two notifiers the plugin fires. See [docs/CUSTOMIZING.md](docs/CUSTOMIZING.md).

## License

GNU General Public License v2.0. Portions copyright the Zen Cart Development
Team and ZenExpert (https://zenexpert.com). Chart.js is bundled under the MIT
License. See [LICENSE](LICENSE).

As always, no warranty or guarantee is applied or implied. Always make
backups before adding or editing any mod.
