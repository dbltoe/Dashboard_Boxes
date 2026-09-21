# Changelog

All notable changes to this project are recorded here. This project follows
[Semantic Versioning](https://semver.org/).

## [1.0.0] — 2026-09-21

First release.

The Zen Cart 3.0.0 admin dashboard for Zen Cart 2.0.0 through 2.3, as an
encapsulated plugin: ten panels, drag-and-drop arrangement saved per admin,
order-status colors where the store has them. Nothing outside `zc_plugins/`
is written, and uninstalling restores the stock dashboard.

### Added

- Ten panels: Today at a Glance, Sales, Recent Orders, Traffic History, Orders
  by Status, Top Sellers, Who's Online, New Customers, Sales and Specials,
  Store Snapshot.
- Drag-and-drop across a top strip, a main area and a sidebar, saved per admin
  in the plugin's own table, with a Reset link.
- Status badges from the Orders Status color column on Zen Cart 2.2.1 and
  later; Bootstrap badges below that.
- A width model for every box (full, two thirds or one third of the page, kept with the layout and rendered as a wrapping row), with no control in Free; a layout controller sets it.
- Two notifiers for other plugins: `NOTIFY_DASHBOARD_BOXES_REGISTRY` to add or
  remove panels and `NOTIFY_DASHBOARD_BOXES_LAYOUT` to replace the layout
  that is about to be rendered.
- Chart.js 4.5.1 bundled and served from the plugin directory; no CDN.
- One codebase for Zen Cart v2.0.0 through v2.3 and PHP 8.0 through 8.5,
  verified against each release branch. Installs and stands aside on v3.0.0.

[1.0.0]: https://github.com/dbltoe/Dashboard_Boxes/releases/tag/v1.0.0
