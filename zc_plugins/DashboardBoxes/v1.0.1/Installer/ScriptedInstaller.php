<?php
/**
 * Dashboard Boxes -- Plugin Manager installer.
 *
 * Only executeInstall, executeUpgrade and executeUninstall are ever called;
 * any other method here would be dead code. The plugin owns one table, for
 * each admin's saved layout, and nothing else: no configuration group, no
 * admin page, no change to any core table.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    public const PLUGIN_KEY = 'DashboardBoxes';
    public const PLUGIN_BASE_NAME = 'Dashboard Boxes';
    public const TABLE_SUFFIX = 'dashboard_boxes_layout';

    protected function executeInstall()
    {
        if (!$this->versionIsSupported()) {
            return false;
        }
        $this->createTable();
        zen_record_admin_activity(self::PLUGIN_BASE_NAME . ' installed.', 'info');
        return true;
    }

    protected function executeUpgrade($oldVersion = null)
    {
        if (!$this->versionIsSupported()) {
            return false;
        }
        $this->createTable();
        zen_record_admin_activity(self::PLUGIN_BASE_NAME . ' upgraded.', 'info');
        return true;
    }

    protected function executeUninstall()
    {
        $this->executeInstallerSql('DROP TABLE IF EXISTS ' . $this->tableName());
        zen_record_admin_activity(self::PLUGIN_BASE_NAME . ' uninstalled and its layout table removed.', 'info');
        return true;
    }

    /**
     * The manifest keeps the plugin out of the list below v2.0.0, and on
     * v3.0.0 the plugin installs and stands aside (the dashboard is core
     * there). This is the belt to that pair of braces.
     */
    protected function versionIsSupported(): bool
    {
        if (!defined('PROJECT_VERSION_MAJOR') || (int)PROJECT_VERSION_MAJOR >= 2) {
            return true;
        }
        $this->errorContainer->addError(
            0,
            self::PLUGIN_BASE_NAME . ' needs Zen Cart 2.0.0 or later; this store reports '
            . PROJECT_VERSION_MAJOR . '.' . (defined('PROJECT_VERSION_MINOR') ? PROJECT_VERSION_MINOR : '?') . '.',
            true
        );
        return false;
    }

    protected function tableName(): string
    {
        return (defined('DB_PREFIX') ? DB_PREFIX : '') . self::TABLE_SUFFIX;
    }

    protected function createTable(): void
    {
        $this->executeInstallerSql(
            'CREATE TABLE IF NOT EXISTS ' . $this->tableName() . ' ('
            . ' admin_id int NOT NULL,'
            . ' layout text NOT NULL,'
            . ' last_modified datetime NOT NULL,'
            . ' PRIMARY KEY (admin_id)'
            . ')'
        );
    }
}
