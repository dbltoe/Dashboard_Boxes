# Plugins Library listing text

Not yet listed. Paste into the Zen Cart Plugins Library submission form,
category **Admin Tools**. The form takes Markdown. Not part of the release
package.

The Plugin ID arrives on acceptance: put it in the manifest, rebuild, run
the suite, commit, push, re-cut the GitHub release and re-upload the zip.
The listing's version string must match the manifest exactly, `v1.0.0`,
`v` included, or nobody is notified of updates. The install root the
Library derives from the zip must be unique across plugin records.

---

## Title

Dashboard Boxes

## Short description

The Zen Cart 3.0.0 admin dashboard for Zen Cart 2.0 through 2.3, as an
encapsulated plugin: today's numbers, a sales chart, recent orders with
status badges, top sellers, traffic, who's online and more, in panels you
drag into place. Each admin keeps their own layout. No core file is changed;
uninstall and the stock dashboard is back.

## Description

**What it does.** Replaces the panels on the admin home page with the set
Zen Cart 3.0.0 ships, laid out as a strip across the top, a wide main area
and a sidebar: Today at a Glance (orders, revenue and new customers since
midnight, reviews pending), Sales (last 30 days as a chart), Recent Orders
(status badges, products popover, invoice and edit links, quick-filter
pills), Traffic History, Orders by Status, Top Sellers, Who's Online, New
Customers, Sales and Specials, and Store Snapshot.

Drag any panel by its heading into the top strip, the main area or the
sidebar. The arrangement is saved as you drop it, per admin. A *Reset
layout* link puts the defaults back. Order-status colors set under
Localization > Orders Status (2.2.1 and later) show as badges.

**Encapsulated.** One folder under `zc_plugins/`, installed from Plugin
Manager. The panels come in through the notifier the stock dashboard
already fires, so nothing in `admin/` is replaced and a Zen Cart upgrade
cannot overwrite it. Each admin's layout is kept in the plugin's own table;
no core table is altered. Chart.js is bundled and served from the plugin
directory; nothing is loaded from a CDN. Uninstalling drops the table and
hands the dashboard back to core.

**Admin profiles** are respected panel by panel: an admin without access to
Orders sees no order panels.

**For other plugins:** two notifiers let another plugin add a panel of its
own or replace the layout about to be rendered.

**Credit.** The design is ZenExpert's Modern Dynamic Dashboard, merged into
Zen Cart core for 3.0.0; portions of the panel code carry their copyright.

**Runs on Zen Cart 2.0.0, 2.0.1, 2.1.0, 2.2.0 through 2.2.3 and the 2.3
branch, PHP 8.0 through 8.5, from a single codebase**, verified on each
release branch. On Zen Cart 3.0.0 it installs and stands aside, because that
release's own dashboard is this design. Zen Cart 1.5.8 is not supported: its
admin home has no notifier to hook.

## Compatible versions

2.0.0, 2.0.1, 2.1.0, 2.2.0, 2.2.1, 2.2.2, 2.2.3, 2.3.0 (and 3.0.0: installs, does nothing)

## Encapsulated

Yes

## Version string

v1.0.0

## Links

- GitHub: https://github.com/dbltoe/Dashboard_Boxes
- Support thread: (link once posted)
