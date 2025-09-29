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

namespace Bugo\LightPortal\Migrations;

use Laminas\Db\Adapter\Adapter;

if (! defined('SMF'))
	die('No direct access...');

class PortalAdapter extends Adapter implements PortalAdapterInterface
{
	public function getPrefix(): string
	{
		return $this->getDriver()->getConnection()->getConnectionParameters()['prefix'] ?? '';
	}

	public function getSqlBuilder(): PortalSqlInterface
	{
		return new PortalSql($this);
	}
}
