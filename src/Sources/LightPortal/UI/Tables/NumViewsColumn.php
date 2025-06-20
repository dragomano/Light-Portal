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

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;
use Bugo\LightPortal\Utils\Icon;

class NumViewsColumn extends Column
{
	public static function make(string $name = 'num_views', string $title = ''): static
	{
		return parent::make($name, $title ?: Icon::get('views', Lang::$txt['lp_views']))
			->setData('num_views', 'centertext')
			->setSort('p.num_views DESC', 'p.num_views');
	}
}
