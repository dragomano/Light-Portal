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
use Bugo\LightPortal\Utils\Str;

class TitleColumn extends Column
{
	public static function make(string $name = 'title', string $title = '', ?string $entity = null): static
	{
		return parent::make($name, $title ?: Lang::$txt['lp_title'])
			->setData(static fn($entry) => $entry['status']
				? Str::html('a', ['class' => 'bbc_link'])
					->href(LP_BASE_URL . ";sa=$entity;id=" . $entry['id'])
					->setText($entry['title'])
				: $entry['title'], 'word_break')
			->setSort('title', 'title DESC');
	}
}
