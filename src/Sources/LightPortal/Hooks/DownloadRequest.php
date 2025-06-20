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

use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\HasEvents;

if (! defined('SMF'))
	die('No direct access...');

class DownloadRequest
{
	use HasEvents;

	public function __invoke(mixed &$attachRequest): void
	{
		Lang::load('LightPortal/LightPortal');

		$this->events()->dispatch(PortalHook::downloadRequest, ['attachRequest' => &$attachRequest]);
	}
}
