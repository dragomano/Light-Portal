<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Hooks;

use Bugo\Compat\Config;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

class Redirect
{
	use HasRequest;

	public function __invoke(string &$setLocation): void
	{
		if (empty(Config::$modSettings['lp_frontpage_mode']) || Setting::isStandaloneMode())
			return;

		if ($this->request()->is('markasread')) {
			$setLocation = Config::$scripturl . '?action=forum';
		}
	}
}
