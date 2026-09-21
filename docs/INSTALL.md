# Installing Dashboard Boxes

Applies to Zen Cart v2.0.0 through v2.3 on PHP 8.0 through 8.5. On Zen Cart
3.0.0 the plugin installs cleanly and does nothing, because that release's
own dashboard is the same design.

---

## 1. Copy the files

This repository contains one directory that belongs in your store:

```
zc_plugins/DashboardBoxes/
```

Upload it so it lands at:

```
<your store root>/zc_plugins/DashboardBoxes/v1.0.0/
```

That directory should contain `manifest.php`, `readme.html`, `changelog.txt`,
and the `Installer/`, `lib/`, `admin/` and `catalog/` sub-directories.

Nothing goes anywhere else. The plugin does not drop files into your `admin/`
or `includes/` directories, which is what makes it safe to remove later.

## 2. Install it

1. Log in to your admin.
2. Open **Modules > Plugin Manager**.
3. Select **Dashboard Boxes** and click **Install**.
4. Open the admin home page.

The installer creates one table, `dashboard_boxes_layout` (with your store's
table prefix, if any), to hold each admin's arrangement. It creates no
configuration keys, registers no admin page, and changes no core table.

## 3. Arrange it

Drag any panel by its heading. Drop it in the strip across the top, the wide
main area or the sidebar. Each drop is saved for the admin who made it; other
admins keep their own arrangement. *Reset layout* in the small toolbar above
the panels puts the defaults back.

## Upgrading the plugin

Upload the new version directory beside the old one, open Plugin Manager and
click **Upgrade**. Saved layouts are kept. Delete the old version directory
once Plugin Manager shows the new one.

## Uninstalling

Plugin Manager > **Dashboard Boxes** > **Uninstall**. The layout table is
dropped and the stock dashboard is back immediately. Delete
`zc_plugins/DashboardBoxes/` afterwards if you want the files gone too.

## If the dashboard still looks stock

- The admin home page shows Zen Cart's setup wizard until *Store Name*, *Store
  Owner*, *Store Owner Email* and *Store Name and Address* are all set in
  **Configuration > My Store**. The plugin has no say until the wizard is
  done.
- Plugin Manager must show the plugin as **Installed** and **enabled**, not
  merely present.
- On Zen Cart 3.0.0 the dashboard you see is core's own; that is the intended
  behavior.
