<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Database\Migrations\Columns;

use Laminas\Db\Adapter\Platform\Postgresql;
use LightPortal\Database\Migrations\DbPlatform;

class TinyInteger extends UnsignedInteger
{
	protected $type = 'TINYINT';

	public function __construct($name = null, $nullable = false, $default = 0, array $options = [])
	{
		$platform = DbPlatform::get();

		if ($platform instanceof Postgresql) {
			$this->type = 'SMALLINT';
		}

		parent::__construct($name, $nullable, $default, $options);
	}
}
