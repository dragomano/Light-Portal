<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\Enums\PortalHook;

if (! defined('SMF'))
	die('No direct access...');

class DownloadRequest
{
	public function __invoke(&$attachRequest): void
	{
		(new LoadTheme())();

		AddonHandler::getInstance()->run(PortalHook::downloadRequest, [&$attachRequest]);
	}
}
