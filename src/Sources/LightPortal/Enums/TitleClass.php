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

namespace LightPortal\Enums;

use LightPortal\Enums\Traits\HasHtml;
use LightPortal\Utils\Str;

enum TitleClass: string
{
	use HasHtml;

	case CAT_BAR = 'cat_bar';
	case TITLE_BAR = 'title_bar';
	case SUB_BAR = 'sub_bar';
	case NOTICEBOX = 'noticebox';
	case INFOBOX = 'infobox';
	case DESCBOX = 'descbox';
	case GENERIC_LIST_WRAPPER = 'generic_list_wrapper';
	case PROGRESS_BAR = 'progress_bar';
	case POPUP_CONTENT = 'popup_content';
	case EMPTY = '';

	public function getList(): string
	{
		$isEmpty = $this === self::EMPTY;

		$class = match ($this) {
			self::CAT_BAR => 'catbg',
			self::TITLE_BAR => 'titlebg',
			self::SUB_BAR => 'subbg',
			default => null,
		};

		return Str::html('div')
			->class($isEmpty ? null : $this->value)
			->addText($isEmpty ? '%s' : '')
			->addHtml($isEmpty ? '' : Str::html('h3', '%s')->class($class))
			->toHtml();
	}
}
