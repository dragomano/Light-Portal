<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;

class PageSlugColumn extends Column
{
	public static function make(string $name = 'slug', string $title = ''): static
	{
		return parent::make($name, $title ?: Lang::$txt['lp_slug'])
			->setData('slug', 'centertext')
			->setSort('p.slug DESC', 'p.slug');
	}
}
