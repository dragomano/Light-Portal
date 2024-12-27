<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Str;

use function preg_match;

use const LP_NAME;
use const LP_VERSION;

if (! defined('SMF'))
	die('No direct access...');

class PackageUpload
{
	public function __invoke(): void
	{
		if (Utils::$context['package']['name'] !== LP_NAME || Utils::$context['package']['version'] < LP_VERSION)
			return;

		if (isset(Utils::$context['package']['upgrade']) && preg_match('/^2\.8\..*/', LP_VERSION)) {
			Utils::$context['package']['install']['link'] = Str::html('a')
				->href(Config::$scripturl . '?action=admin;area=packages;sa=install;package=' . Utils::$context['package']['filename'])
				->setText('[ ' . Lang::$txt['package_upgrade'] . ' ]')
				->toHtml();
		}
	}
}
