<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;

use function define;
use function dirname;
use function microtime;

if (! defined('SMF'))
	die('No direct access...');

class Init
{
	public function __invoke(): void
	{
		Utils::$context['lp_load_time'] ??= microtime(true);

		define('LP_NAME', 'Light Portal');
		define('LP_VERSION', '2.7.1');
		define('LP_PLUGIN_LIST', 'https://d8d75ea98b25aa12.mokky.dev/addons');
		define('LP_ADDON_DIR', dirname(__DIR__) . '/Addons');
		define('LP_CACHE_TIME', (int) (Config::$modSettings['lp_cache_interval'] ?? 72000));
		define('LP_ACTION', Config::$modSettings['lp_portal_action'] ?? 'portal');
		define('LP_PAGE_PARAM', Config::$modSettings['lp_page_param'] ?? 'page');
		define('LP_BASE_URL', Config::$scripturl . '?action=' . LP_ACTION);
		define('LP_PAGE_URL', Config::$scripturl . '?' . LP_PAGE_PARAM . '=');
		define('LP_ALIAS_PATTERN', '^[a-z][a-z0-9\-]+$');
		define('LP_AREAS_PATTERN', '^[a-z][a-z0-9=|\-,!]+$');
		define('LP_ADDON_PATTERN', '^[A-Z][a-zA-Z]+$');
	}
}
