<?php declare(strict_types=1);

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.09.25
 */

namespace Bugo\LightPortal\Plugins\EhPortalMigration;

use Bugo\Compat\User;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\Icon;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

class EhPortalMigration extends Plugin
{
	public string $type = 'impex';

	private const AREA = 'import_from_ep';

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

			$areas['lp_categories']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];
		}
	}

	public function extendBlockAreas(Event $e): void
	{
		$db = app(DatabaseInterface::class);
		$errorHandler = app(ErrorHandlerInterface::class);

		$e->args->areas[self::AREA] = [new BlockImport($db, $errorHandler), 'main'];
	}

	public function extendPageAreas(Event $e): void
	{
		$db = app(DatabaseInterface::class);
		$errorHandler = app(ErrorHandlerInterface::class);

		$e->args->areas[self::AREA] = [new PageImport($db, $errorHandler), 'main'];
	}

	public function extendCategoryAreas(Event $e): void
	{
		$db = app(DatabaseInterface::class);
		$errorHandler = app(ErrorHandlerInterface::class);

		$e->args->areas[self::AREA] = [new CategoryImport($db, $errorHandler), 'main'];
	}
}
