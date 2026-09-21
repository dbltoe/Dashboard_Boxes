<?php
/**
 * Dashboard Boxes -- plugin manifest.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */

// Only ever read by Plugin Manager, inside the admin, where this is defined.
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Links shown in the Plugin Manager, alongside Install / Uninstall / Disable.
 *
 * Zen Cart echoes `pluginDescription` into the Plugin Manager's info box as
 * raw HTML, so the Read Me and GitHub buttons live here, where the store
 * owner is already looking. The Read Me link is built from DIR_WS_CATALOG so
 * it works whatever the store lives at; Zen Cart's own `zc_plugins/.htaccess`
 * re-allows `.html`, so readme.html is reachable by design.
 *
 * On v2.0 and v2.1 the description is written only by the INSERT that first
 * creates the plugin_control row, so nothing state-dependent belongs here.
 */
$dbxPluginDir = 'zc_plugins/DashboardBoxes/v1.0.0/';
$dbxReadmeUrl = (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/') . $dbxPluginDir . 'readme.html';
$dbxGithubUrl = 'https://github.com/dbltoe/Dashboard_Boxes';

/**
 * The Zen Cart forum's support thread for this plugin.
 *
 * Set it BEFORE THE FIRST RELEASE: on v2.0 and v2.1 the description is
 * captured on the first scan and never refreshed. The forum runs on XenForo, whose thread address
 * is /threads/<id>/. An empty string renders nothing.
 */
$dbxForumUrl = 'https://www.zen-cart.com/threads/207350';

$dbxButtonGap = '6px';
$dbxButton = static function ($url, $label) use ($dbxButtonGap) {
    return '<a href="' . $url . '" class="btn btn-primary" role="button" target="_blank" rel="noopener noreferrer" style="margin:0 ' . $dbxButtonGap . ' 0 0">' . $label . '</a>';
};
// Read Me, GitHub and, once the thread exists, Forum Support Thread: three
// buttons in one row, styled as Plugin Manager's own.
$dbxLinks = '<div style="padding:0 0 0 ' . $dbxButtonGap . '">'
    . $dbxButton($dbxReadmeUrl, 'Read Me')
    . $dbxButton($dbxGithubUrl, 'GitHub')
    . ($dbxForumUrl === '' ? '' : $dbxButton($dbxForumUrl, 'Forum Support Thread'))
    . '</div>';
$dbxForumLink = '';

return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Dashboard Boxes',
    'pluginDescription' =>
        'A modern admin home for Zen Cart 2.0 through 2.3: a strip of today\'s numbers, '
        . 'a sales chart, recent orders with status badges, top sellers, traffic, who\'s '
        . 'online and the rest, in panels you drag into the arrangement you want. Each '
        . 'admin keeps their own layout. Installs from Plugin Manager; no core file is '
        . 'changed, and uninstalling puts the stock dashboard back. On Zen Cart 3.0.0, '
        . 'where this dashboard is part of core, the plugin installs and stands aside.'
        . $dbxLinks
        . $dbxForumLink,
    // Shown as the Author in Plugin Manager (varchar(64)).
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    // ID from the Zen Cart Plugins Library. Zero until the Library assigns one;
    // it is what makes "a new version is available" work in Plugin Manager.
    'pluginId' => 0,
    'zcVersions' => ['v200', 'v210', 'v220', 'v230', 'v300'],
    'changelog' => 'changelog.txt',
    'github_repo' => $dbxGithubUrl,
    'pluginGroups' => [],
];
