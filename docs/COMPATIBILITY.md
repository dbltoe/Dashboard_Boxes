# Compatibility notes: Zen Cart v2.0.0 → v2.3, PHP 8.0 → 8.5

The plugin runs unmodified across the four Zen Cart 2.x release branches and
installs harmlessly on 3.0.0. That comes from choosing, at each decision
point, the mechanism that exists in *every* supported version. This document
records those choices so a future maintainer knows which are load-bearing.

## How it is verified

Not by assertion. The maintainer's harness (not shipped) runs on every PHP
build from 8.0 to 8.5, and the plugin was mounted and installed through
Plugin Manager on a clean checkout of each release branch, where the
dashboard, the save and reset calls, a forged token, a junk layout and the
other admin pages were exercised and the debug logs checked.

| Check | Method |
|---|---|
| **Zen Cart API** | Every `zen_*` function the plugin calls is looked up in each install. The notifier it observes is confirmed to fire from `admin/index_dashboard.php` on each. The constants it reads are confirmed present. |
| **Layout logic** | The library's normalizer against forged, stale and foreign input: unknown names dropped, duplicates collapsed, unplaced panels restored to their default zone. |
| **Installer** | Against a recording fake database: one `CREATE TABLE IF NOT EXISTS`, one `DROP TABLE IF EXISTS`, nothing else; exactly the three ScriptedInstaller hooks. |
| **Security** | The Plugin Library's own scan questions, plus the escaping paths specific to this plugin. |
| **PHP** | Lint plus the whole suite on 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5 with zero diagnostics at `E_ALL`. |

---

## What we rely on

### The dashboard widgets notifier (v2.0.0 and later)

`admin/index_dashboard.php` builds a `$widgets` array and fires
`NOTIFY_ADMIN_DASHBOARD_WIDGETS` with it by reference on every release from
2.0.0. Each entry is `column`, `sort`, `visible`, `path`. The observer sets
`visible => false` on the nine core widgets it replaces and appends its own
ten, with `path` inside the plugin directory. Core accepts any path under
`DIR_FS_CATALOG`, which the plugin directory is.

1.5.8 has no such notifier; its dashboard includes eight widgets by name.
That is why the floor is 2.0.0.

### Three columns become three zones

The 2.x dashboard renders `#colone`, `#coltwo` and `#colthree` as three equal
Bootstrap columns in that DOM order. The plugin's stylesheet makes the first
full width and the other two a 2:1 pair, so column one is the strip across
the top, column two the main area, column three the sidebar. Floats keep the
DOM order, so the strip is on top with no script moving anything and no
reflow on load. The rules apply only under `html.dbx-active`, a class the
head script adds, so they cannot touch a page the plugin is not driving.

### Assets are auto-loaded by page name

`admin/includes/admin_html_head.php` links a plugin's
`admin/includes/css/<page>.css`, and `admin/includes/javascript_loader.php`
includes a plugin's `admin/includes/javascript/<page>.php` and links
`<page>.js`, on every release. The dashboard's page name is `index`. So
`index.css`, `index.php` and `index.js` load there and nowhere else, and the
plugin needs no output buffering and no header hook.

### The layout endpoint

`admin/ajax.php?act=dashboardBoxes&method=save` resolves to
`zcDashboardBoxes` in the plugin's `catalog/includes/classes/ajax/`, which
core's `ajax.php` searches on every release from 2.0.0. The admin `ajax.php`
runs the admin `application_top` first, so an admin session is required
before the class is even loaded; core checks the security token on 2.2 and
later, and the class checks it again itself for the releases that do not.

The admin request sanitizer runs `htmlspecialchars()` over POST values it has
no rule for, which mangles JSON. The class therefore reads the layout from
the raw request body, as core's own 3.0.0 dashboard class does.

### The layout table

Core 3.0.0 stores each admin's layout in a column it added to the `admin`
table. A plugin must not alter a core table, so this one keeps a table of
its own, `dashboard_boxes_layout` (admin_id, layout JSON, last_modified),
created by the installer and dropped by the uninstaller.

### Order-status colors

`orders_status.orders_status_color_code` exists from 2.2.1 (added by the
2.2.0 upgrade script). The library asks the sniffer once per request and the
panels only select the column when it is there. Colors reach a style
attribute only after passing a hex-color check.

### Language keys

Every key the plugin defines carries the `DBX_` prefix, so it cannot collide
with core on any release, including 3.0.0 where the same panels define
`BOX_*` keys. Two date formats the charts want (`DATE_FORMAT_SHORT_NO_YEAR`,
`DATE_FORMAT_SHORT_NO_DAY`) exist only from 2.2, so the library falls back
to fixed formats below that.

### The shared library is loaded by path

Zen Cart 3.0.0's master branch stopped auto-loading a plugin's admin
`extra_functions` in June 2026. Nothing here depends on that loader: every
entry point (observer, AJAX class, head file, each panel) does a
`require_once` of `lib/DashboardBoxesLib.php` by its own `__DIR__`.

### `currencies` on 2.0 and 2.1

2.2 autoloads the `currencies` class; 2.0 and 2.1 need
`require_once DIR_WS_CLASSES . 'currencies.php'`. The two panels that format
money check `class_exists()` first, which triggers the autoloader where there
is one, and require the file where there is not.

### Chart.js

Bundled (4.5.1, MIT) and served from the plugin directory, which core's
`zc_plugins/.htaccess` allows for `.js`. Core 2.x loads jQuery UI (with
sortable) and Bootstrap 3 on every admin page already; the plugin adds no
second copy of either.

## What is deliberately not done

- No override of `orders.php`, `orders_status.php`, `header.php` or
  `login.php`. Core wins over a plugin for any admin page name that exists in
  core, and there is no notifier in the header or login pages to hang a
  change on.
- No `ALTER TABLE` of a core table, ever.
- No configuration group and no admin page, so System Inspection has nothing
  to flag and there is nothing to register.
- No CDN.
