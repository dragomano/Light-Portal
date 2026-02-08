<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils\Traits;

use LightPortal\Database\PortalSqlInterface;

use function LightPortal\app;

trait HasPortalSql
{
	public function getPortalSql(): PortalSqlInterface
	{
		return app(PortalSqlInterface::class);
	}
}
