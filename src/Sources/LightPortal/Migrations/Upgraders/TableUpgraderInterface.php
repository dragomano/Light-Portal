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

namespace Bugo\LightPortal\Migrations\Upgraders;

use Bugo\LightPortal\Migrations\PortalAdapter;
use Laminas\Db\Sql\Sql;

interface TableUpgraderInterface
{
	public function __construct(?PortalAdapter $adapter = null, ?Sql $sql = null);

	public function upgrade(): void;
}
