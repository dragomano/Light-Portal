<?php declare(strict_types=1);

/**
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.08.25
 */

namespace Bugo\LightPortal\Plugins\EzPortalMigration;

use Bugo\Compat\User;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Icon;

if (! defined('LP_NAME'))
	die('No direct access...');

class EzPortalMigration extends Plugin
{
	public string $type = 'impex';

	private const AREA = 'import_from_ez';

	public function extendAdminAreas(Event $e): void
	{
		$areas = &$e->args->areas;

		if (User::$me->is_admin) {
			$areas['lp_blocks']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];

			$areas['lp_pages']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];
		}
	}

	public function extendBlockAreas(Event $e): void
	{
		$e->args->areas[self::AREA] = [new BlockImport(), 'main'];
	}

	public function extendPageAreas(Event $e): void
	{
		$e->args->areas[self::AREA] = [new PageImport(), 'main'];
	}
}
