# Customizing Dashboard Boxes

## The Recent Orders panel

The panel honors the same site-specific overrides the stock widget does, set
in `admin/includes/extra_datafiles/site_specific_admin_overrides.php` (create
the file if your store has none):

```php
<?php
// rows in the Recent Orders panel (default 10)
$recentOrdersMaxRows = 20;

// the status quick-filter pills in its heading (default Pending and Processing)
$recentOrdersWidgetOrderStatusIDs = [1, 2, 5];

// hide the pills altogether
$show_status_pills = false;

// leave attribute lines out of the products popover
$includeAttributesInPopoverRows = false;
```

## Styling

Everything the plugin draws is inside `.dbx-widget` and reads
`admin/includes/css/index.css` inside the plugin directory. To restyle
without editing the plugin, put your rules in
`admin/includes/css/site-specific-styles.php`, which core includes after
every stylesheet on every release. The panel wrappers carry
`data-dbx-widget="<Name>"`, so a single panel can be targeted:

```css
.dbx-widget[data-dbx-widget="Traffic"] .panel { border-top-color: #337ab7; }
```

## Adding a panel from another plugin

The plugin fires `NOTIFY_DASHBOARD_BOXES_REGISTRY` with the registry by
reference before it is used. An observer can add, remove or re-home entries:

```php
$this->attach($this, ['NOTIFY_DASHBOARD_BOXES_REGISTRY']);

public function notify_dashboard_boxes_registry(&$class, $eventID, $param1, &$registry)
{
    $registry['LowStock'] = [
        'path' => __DIR__ . '/../../modules/dashboard_widgets/MyLowStock.php',
        'zone' => 'side',   // top | main | side
        'sort' => 15,
        'title' => 'Low Stock',
    ];
    unset($registry['Specials']);
}
```

A name is letters, digits and underscores, up to 40 characters, starting
with a letter. The panel file must wrap its output in
`<div class="<?php echo DashboardBoxesLib::boxClasses('LowStock'); ?>" data-dbx-widget="LowStock">…</div>`
so it can be dragged, sized and saved like the others; use a `.panel-heading`
inside it for the drag handle. An optional `'width' => 4` on the entry gives
the box a default width (see below).

## Replacing the layout

`NOTIFY_DASHBOARD_BOXES_LAYOUT` fires after this admin's saved layout is
loaded and before it is rendered, with the admin id as the first parameter
and the layout array and the registry by reference. A layout controller can
substitute a profile default, force an order, or hide panels:

```php
public function notify_dashboard_boxes_layout(&$class, $eventID, $adminId, &$layout, &$registry)
{
    $layout['hidden'][] = 'WhosOnline';
}
```

The layout shape is:

```php
[
    'zones' => ['top' => ['KpiCards'], 'main' => [...], 'side' => [...]],
    'hidden' => ['WhosOnline'],
    'widths' => ['OrderStatus' => 4, 'MostPopular' => 4],
]
```

Whatever comes back is normalized: unknown names are dropped, duplicates
collapsed, and every registered panel that was not placed is appended to its
default zone. A hidden panel keeps its place in the saved order but is not
rendered.

## Box widths

Every box has a width, given as Bootstrap columns of the page: 12 (full),
8 (two thirds, the main area's width) or 4 (one third, the sidebar's width).
A box keeps that physical size whichever zone it lands in, and a box wider
than its zone renders full there. So two one-third boxes dropped together
into the main area sit side by side; three fit across the top strip; in the
sidebar everything is full.

Free carries the model and renders it but has no control to set it; that is
a layout controller's job. Two places take a width:

- `'width' => 4` on a registry entry, the box's default.
- `$layout['widths'][$name] = 4` for this admin, which wins over the default.

The panel wrapper's class comes from `DashboardBoxesLib::boxClasses($name)`,
which yields `dbx-widget` plus `dbx-w100`, `dbx-w66`, `dbx-w50` or `dbx-w33`
for the share of its zone the box takes. A panel you add must use it:

```php
<div class="<?php echo DashboardBoxesLib::boxClasses('LowStock'); ?>" data-dbx-widget="LowStock">
```

The page script keeps widths through drags and re-applies the class when a
box moves to a zone of a different width. Below the 992px breakpoint every
box renders full, because the columns are too narrow to share.
