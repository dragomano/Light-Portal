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

use Bugo\Bricks\Tables\Row;
use Bugo\Bricks\Tables\RowPosition;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Str;

class PageSearchRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		return parent::make($value ?: Str::html('div', ['class' => 'row'])
			->addHtml(
				Str::html('div', ['class' => 'col-lg-10'])->setHtml(
					Str::html('input', [
						'type' => 'search',
						'name' => 'search',
						'value' => Utils::$context['search']['string'],
						'placeholder' => Lang::$txt['lp_pages_search'],
						'style' => 'width: 100%',
					])
				)
			)
			->addHtml(
				Str::html('div', ['class' => 'col-lg-2'])->setHtml(
					Str::html('button', [
						'type' => 'submit',
						'name' => 'is_search',
						'class' => 'button floatnone',
						'style' => 'width: 100%',
					])->setHtml(Icon::get('search') . Lang::$txt['search'])
				)
			)
			->toHtml()
		)
			->setClass('floatnone')
			->setPosition(RowPosition::AFTER_TITLE);
	}
}
