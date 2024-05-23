<?php declare(strict_types=1);

/**
 * TitleClass.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Enums;

use Bugo\LightPortal\Enums\Traits\HasHtml;

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
		return match ($this) {
			self::CAT_BAR => '<div class="' . self::CAT_BAR->value . '"><h3 class="catbg">%s</h3></div>',
			self::TITLE_BAR => '<div class="' . self::TITLE_BAR->value . '"><h3 class="titlebg">%s</h3></div>',
			self::SUB_BAR => '<div class="' . self::SUB_BAR->value . '"><h3 class="subbg">%s</h3></div>',
			self::NOTICEBOX => '<div class="' . self::NOTICEBOX->value . '"><h3>%s</h3></div>',
			self::INFOBOX => '<div class="' . self::INFOBOX->value . '"><h3>%s</h3></div>',
			self::DESCBOX => '<div class="' . self::DESCBOX->value . '"><h3>%s</h3></div>',
			self::GENERIC_LIST_WRAPPER => '<div class="' . self::GENERIC_LIST_WRAPPER->value . '"><h3>%s</h3></div>',
			self::PROGRESS_BAR => '<div class="' . self::PROGRESS_BAR->value . '"><h3>%s</h3></div>',
			self::POPUP_CONTENT => '<div class="' . self::POPUP_CONTENT->value . '"><h3>%s</h3></div>',
			self::EMPTY => '<div>%s</div>',
		};
	}
}
