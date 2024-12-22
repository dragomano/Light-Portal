<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;

if (! defined('SMF'))
	die('No direct access...');

class DownloadRequest
{
	public function __invoke(mixed &$attachRequest): void
	{
		Lang::load('LightPortal/LightPortal');

		app('events')->dispatch(
			PortalHook::downloadRequest,
			new Event(new class ($attachRequest) {
				public function __construct(public mixed &$attachRequest) {}
			})
		);
	}
}
