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

namespace LightPortal\Database\Migrations\Columns;

use Laminas\Db\Adapter\Platform\Postgresql;
use Laminas\Db\Sql\Ddl\Column\AbstractLengthColumn;
use LightPortal\Database\Migrations\DbPlatform;

class MediumText extends AbstractLengthColumn
{
	protected $type = 'MEDIUMTEXT';

	public function __construct($name, $length = null, $nullable = false, $default = null, array $options = [])
	{
		$platform = DbPlatform::get();

		if ($platform instanceof Postgresql) {
			$this->type = 'TEXT';
		}

		parent::__construct($name, $length, $nullable, $default, $options);
	}
}
