<?php
/**
 * Dashboard Boxes -- the layout save/reset endpoint.
 *
 * Reached as admin/ajax.php?act=dashboardBoxes&method=save (or reset). The
 * admin's ajax.php runs the admin application_top first, so the caller is a
 * logged-in admin or nothing runs; the file sits on the catalog side only
 * because that is where every release's ajax.php looks for a plugin's
 * classes. It refuses the storefront and any request without the session's
 * own security token.
 *
 * @package  DashboardBoxes
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require_once dirname(__DIR__, 4) . '/lib/DashboardBoxesLib.php';

class zcDashboardBoxes extends base
{
    public const ALLOWED_METHODS = ['save', 'reset'];

    public function save(): array
    {
        $denied = $this->denied();
        if ($denied !== null) {
            return $denied;
        }
        $layout = json_decode($this->postedLayout(), true);
        if (!is_array($layout)) {
            return $this->response('error', 'No layout received.', true);
        }
        $registry = DashboardBoxesLib::registry();
        $clean = DashboardBoxesLib::saveLayout((int)$_SESSION['admin_id'], $layout, $registry);
        return $this->response('success', 'Layout saved', false, $clean);
    }

    public function reset(): array
    {
        $denied = $this->denied();
        if ($denied !== null) {
            return $denied;
        }
        DashboardBoxesLib::resetLayout((int)$_SESSION['admin_id']);
        $registry = DashboardBoxesLib::registry();
        return $this->response('success', 'Layout reset', false, DashboardBoxesLib::defaultLayout($registry));
    }

    /**
     * The layout JSON as the browser sent it.
     *
     * The admin request sanitizer runs htmlspecialchars() over every POST
     * value it has no rule for, which turns the JSON's quotes into &quot;
     * before this class ever sees them. Core's own 3.0.0 dashboard class
     * reads the raw request body for the same reason. The token, being hex,
     * survives the sanitizer and is read from $_POST as usual. Only the
     * 'layout' key is taken from the raw body, and only as a string that
     * json_decode() then has to accept.
     */
    protected function postedLayout(): string
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $parsed = [];
            parse_str($raw, $parsed);
            if (isset($parsed['layout']) && is_string($parsed['layout'])) {
                return $parsed['layout'];
            }
        }
        if (isset($_POST['layout']) && is_string($_POST['layout'])) {
            return html_entity_decode($_POST['layout'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return '';
    }

    /** Null when the request may proceed, otherwise the refusal to return. */
    protected function denied(): ?array
    {
        if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true || empty($_SESSION['admin_id'])) {
            return $this->response('error', 'Not an admin request.', true);
        }
        if (!DashboardBoxesLib::isActive()) {
            return $this->response('error', 'Not active on this Zen Cart version.', true);
        }
        $token = isset($_POST['securityToken']) && is_string($_POST['securityToken']) ? $_POST['securityToken'] : '';
        if (!DashboardBoxesLib::tokenIsValid($token)) {
            return $this->response('error', 'Invalid security token.', true);
        }
        return null;
    }

    protected function response(string $status, string $message, bool $error, ?array $layout = null): array
    {
        $out = ['status' => $status, 'message' => $message, 'error' => $error];
        if ($layout !== null) {
            $out['layout'] = $layout;
        }
        return $out;
    }
}
