<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Setting;

use function define;
use function defined;
use function dirname;
use function microtime;

if (! defined('SMF'))
	die('No direct access...');

class Init
{
	public function __invoke(): void
	{
		if (defined('LP_NAME'))
			return;

		Utils::$context['lp_load_time'] ??= microtime(true);

		define('LP_NAME', DateTime::getValueForDate());
		define('LP_VERSION', '3.0 alpha');
		define('LP_PLUGIN_LIST', 'https://d8d75ea98b25aa12.mokky.dev/json');
		define('LP_ADDON_DIR', dirname(__DIR__) . '/Plugins');
		define('LP_ADDON_URL', Config::$boardurl . '/Sources/LightPortal/Plugins');
		define('LP_CACHE_TIME', Setting::get('lp_cache_interval', 'int', 72000));
		define('LP_ACTION', Setting::get('lp_portal_action', 'string', 'portal'));
		define('LP_PAGE_PARAM', Setting::get('lp_page_param', 'string', 'page'));
		define('LP_BASE_URL', Config::$scripturl . '?action=' . LP_ACTION);
		define('LP_PAGE_URL', Config::$scripturl . '?' . LP_PAGE_PARAM . '=');
		define('LP_ALIAS_PATTERN', '^[a-z][a-z0-9\-]+$');
		define('LP_AREAS_PATTERN', '^[a-z][a-z0-9=|\-,!]+$');
		define('LP_ADDON_PATTERN', '^[A-Z][a-zA-Z]+$');
	}
}
