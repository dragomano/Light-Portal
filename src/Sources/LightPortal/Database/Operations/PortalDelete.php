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

namespace LightPortal\Database\Operations;

use Laminas\Db\Sql\Delete;

if (! defined('SMF'))
	die('No direct access...');

class PortalDelete extends Delete
{
	public function __construct($table = null, private readonly string $prefix = '')
	{
		parent::__construct($table);
	}

	public function from($table): self
	{
		$table = $this->prefix . $table;

		return parent::from($table);
	}
}
