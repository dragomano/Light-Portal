<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\DateColumn as BaseDateColumn;
use Bugo\Compat\Lang;

class DateColumn extends BaseDateColumn
{
	public static function make(string $name = 'date', string $title = ''): static
	{
		return parent::make($name, Lang::$txt['date'])
			->setData('created_at', 'centertext');
	}
}
