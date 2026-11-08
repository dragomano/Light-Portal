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

namespace LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Row;
use Bugo\Bricks\Tables\RowPosition;
use Bugo\Compat\Utils;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Str;

class PageSearchRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		$url = Utils::$context['lp_actions_url'] ?? '';

		// Typing, Enter and the magnifier all request the same live update of the list
		$action = "@post('" . $url . "', {payload: {search_items: \$search, page_type: \$page_type}})";

		$masks = __('lp_search_masks');

		$input = Str::html('input', [
			'type'                          => 'search',
			'name'                          => 'search',
			'value'                         => Utils::$context['search']['string'],
			'placeholder'                   => __('lp_pages_search') . ' (' . $masks . ')',
			'title'                         => $masks,
			'data-bind'                     => 'search',
			'data-indicator'                => 'fetching',
			'data-on:input__debounce.300ms' => $action,
			'data-on:keydown'               => "evt.key === 'Enter' && (evt.preventDefault(), $action)",
		]);

		// The magnifier repeats the live request, so the classic form submit is only a fallback
		$icon = Str::html('span', [
			'class'         => 'lp_search_icon',
			'data-show'     => '!$fetching',
			'data-on:click' => $action,
		])->setHtml(Icon::get('search'));

		$spinner = Str::html('i', [
			'class'       => 'lp_search_icon fa-solid fa-spinner fa-spin',
			'aria-hidden' => 'true',
			'data-show'   => '$fetching',
		]);

		$box = Str::html('div', ['class' => 'lp_search_box'])
			->addHtml($input)
			->addHtml($icon)
			->addHtml($spinner);

		$col = Str::html('div', ['class' => 'col-lg-12'])->setHtml($box);

		// The number of pages matching the filters comes from the context and is patched with the search
		if (($found = Utils::$context['lp_pages_found'] ?? null) !== null) {
			$col->addHtml(Str::html('small', ['class' => 'lp_search_found'])
				->setHtml(__('lp_pages_found') . ': ' . Str::html('span', ['data-text' => '$pages_found'])->setText((string) $found)));
		}

		// Patching the list never touches what is being typed here
		$row = Str::html('div', ['class' => 'row', 'data-ignore-morph' => ''])
			->addHtml($col)
			->toHtml();

		return parent::make($value ?: $row)
			->setClass('floatnone')
			->setPosition(RowPosition::AFTER_TITLE);
	}
}
