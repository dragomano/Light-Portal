<?php declare(strict_types=1);

/**
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\EzPortalMigration;

use Bugo\Compat\User;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\Icon;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::IMPEX)]
class EzPortalMigration extends Plugin
{
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
		app()->add(BlockImport::class)
			->addArguments([PortalSqlInterface::class, ErrorHandlerInterface::class]);

		$e->args->areas[self::AREA] = [app(BlockImport::class), 'main'];
	}

	public function extendPageAreas(Event $e): void
	{
		app()->add(PageImport::class)
			->addArguments([PortalSqlInterface::class, ErrorHandlerInterface::class]);

		$e->args->areas[self::AREA] = [app(PageImport::class), 'main'];
	}
}
