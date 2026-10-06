/**
 * Dashboard Boxes -- drag-and-drop layout for the Zen Cart 2.x admin home.
 *
 * Loaded on the dashboard page only, after index.php has set
 * window.dashboardBoxes. The three core columns become connected sortable
 * lists; every drop posts the new arrangement to the plugin's AJAX class,
 * which stores it for this admin.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
jQuery(function ($) {
    'use strict';

    var cfg = window.dashboardBoxes;
    if (!cfg || !cfg.columns || typeof zcJS === 'undefined' || typeof $.fn.sortable !== 'function') {
        return;
    }

    var columnSelector = '#' + cfg.columns.top + ', #' + cfg.columns.main + ', #' + cfg.columns.side;
    var $columns = $(columnSelector);
    if ($columns.length !== 3) {
        return;
    }

    // ---- a small toolbar above the first column ------------------------
    var $bar = $('<div>', {'class': 'dbx-toolbar'});
    $('<span>', {'class': 'dbx-hint', text: cfg.text.hint}).appendTo($bar);
    var $status = $('<span>', {'class': 'dbx-status', 'aria-live': 'polite'}).appendTo($bar);
    var $reset = $('<a>', {'class': 'dbx-reset', href: '#', text: cfg.text.reset}).appendTo($bar);
    $('#' + cfg.columns.top).before($bar);

    var statusTimer = null;
    function flash(message, isError) {
        $status.text(message).toggleClass('dbx-status-error', !!isError).addClass('dbx-status-show');
        window.clearTimeout(statusTimer);
        statusTimer = window.setTimeout(function () {
            $status.removeClass('dbx-status-show');
        }, isError ? 6000 : 2000);
    }

    function post(method, data) {
        return zcJS.ajax({
            url: 'ajax.php?act=dashboardBoxes&method=' + method,
            data: data || {}
        });
    }

    // ---- box widths ------------------------------------------------------
    // Widths (12, 8 or 4 page columns) are set by a layout controller, not
    // here; this script only keeps them through a save and re-applies the
    // right class when a box lands in a zone of a different width.
    var widths = (cfg.layout && cfg.layout.widths && typeof cfg.layout.widths === 'object') ? $.extend({}, cfg.layout.widths) : {};
    var zoneWidths = cfg.zoneWidths || {top: 12, main: 8, side: 4};
    var defaultWidths = cfg.defaultWidths || {};
    var widthClasses = 'dbx-w100 dbx-w66 dbx-w50 dbx-w33';

    function widthClassFor(name, zone) {
        var width = widths[name] || defaultWidths[name] || 12;
        var zoneWidth = zoneWidths[zone] || 12;
        var share = Math.min(width, zoneWidth) / zoneWidth;
        if (share >= 1) { return 'dbx-w100'; }
        if (share >= 0.66) { return 'dbx-w66'; }
        if (share >= 0.5) { return 'dbx-w50'; }
        return 'dbx-w33';
    }

    function zoneOf($column) {
        var id = $column.attr('id');
        var found = 'main';
        $.each(cfg.columns, function (zone, columnId) {
            if (columnId === id) { found = zone; }
        });
        return found;
    }

    function applyWidth($box, zone) {
        var name = $box.attr('data-dbx-widget');
        if (name) {
            $box.removeClass(widthClasses).addClass(widthClassFor(name, zone));
        }
    }

    // ---- reading the page back into a layout ---------------------------
    function collect() {
        var layout = {zones: {top: [], main: [], side: []}, hidden: [], widths: $.extend({}, widths)};
        if (cfg.layout && $.isArray(cfg.layout.hidden)) {
            layout.hidden = cfg.layout.hidden.slice();
        }
        $.each(cfg.columns, function (zone, columnId) {
            $('#' + columnId).children('.dbx-widget').each(function () {
                var name = $(this).attr('data-dbx-widget');
                if (name) {
                    layout.zones[zone].push(name);
                }
            });
        });
        return layout;
    }

    function save() {
        post('save', {layout: JSON.stringify(collect())})
            .done(function (response) {
                if (response && response.error === false) {
                    flash(cfg.text.saved, false);
                } else {
                    flash((response && response.message) ? response.message : cfg.text.saveFailed, true);
                }
            })
            .fail(function () {
                flash(cfg.text.saveFailed, true);
            });
    }

    // ---- sortable ------------------------------------------------------
    $columns.sortable({
        items: '> .dbx-widget',
        handle: '.panel-heading',
        connectWith: columnSelector,
        placeholder: 'dbx-placeholder',
        tolerance: 'pointer',
        forcePlaceholderSize: true,
        distance: 5,
        cursor: 'move',
        opacity: 0.9,
        start: function (event, ui) {
            // the placeholder takes the dragged box's width so the row keeps its shape
            var widthClass = (ui.item.attr('class') || '').match(/dbx-w\d+/);
            ui.placeholder.removeClass(widthClasses).addClass(widthClass ? widthClass[0] : 'dbx-w100');
        },
        over: function (event, ui) {
            // entering a zone of another width: the placeholder follows the rule too
            var name = ui.item.attr('data-dbx-widget');
            if (name) {
                ui.placeholder.removeClass(widthClasses).addClass(widthClassFor(name, zoneOf($(this))));
            }
        },
        stop: function (event, ui) {
            applyWidth(ui.item, zoneOf(ui.item.parent()));
            save();
        }
    });

    $reset.on('click', function (event) {
        event.preventDefault();
        if (!window.confirm(cfg.text.resetConfirm)) {
            return;
        }
        post('reset').done(function (response) {
            if (response && response.error === false) {
                window.location.reload();
            } else {
                flash((response && response.message) ? response.message : cfg.text.saveFailed, true);
            }
        }).fail(function () {
            flash(cfg.text.saveFailed, true);
        });
    });

    // ---- Bootstrap bits the widgets use ---------------------------------
    if (typeof $.fn.tooltip === 'function') {
        $('.dbx-widget [data-toggle="tooltip"]').tooltip();
    }
    if (typeof $.fn.popover === 'function') {
        $('.dbx-widget [data-toggle="popover"]').popover({html: true, sanitize: true});
    }
});
