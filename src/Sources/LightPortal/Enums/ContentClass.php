<?php declare(strict_types=1);

/**
 * ContentClass.php
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

enum ContentClass: string
{
	use HasHtml;

	case ROUNDFRAME = 'roundframe';
	case ROUNDFRAME2 = 'roundframe2';
	case WINDOWBG = 'windowbg';
	case WINDOWBG2 = 'windowbg2';
	case INFORMATION = 'information';
	case ERRORBOX = 'errorbox';
	case NOTICEBOX = 'noticebox';
	case INFOBOX = 'infobox';
	case DESCBOX = 'descbox';
	case BBC_CODE = 'bbc_code';
	case GENERIC_LIST_WRAPPER = 'generic_list_wrapper';
	case EMPTY = '';

	public function getList(): string
	{
		return match ($this) {
			self::ROUNDFRAME => '<div class="' . self::ROUNDFRAME->value . ' noup">%s</div>',
			self::ROUNDFRAME2 => '<div class="' . self::ROUNDFRAME->value . '">%s</div>',
			self::WINDOWBG => '<div class="' . self::WINDOWBG->value . ' noup">%s</div>',
			self::WINDOWBG2 => '<div class="' . self::WINDOWBG->value . '">%s</div>',
			self::INFORMATION => '<div class="' . self::INFORMATION->value . '">%s</div>',
			self::ERRORBOX => '<div class="' . self::ERRORBOX->value . '">%s</div>',
			self::NOTICEBOX => '<div class="' . self::NOTICEBOX->value . '">%s</div>',
			self::INFOBOX => '<div class="' . self::INFOBOX->value . '">%s</div>',
			self::DESCBOX => '<div class="' . self::DESCBOX->value . '">%s</div>',
			self::BBC_CODE => '<div class="' . self::BBC_CODE->value . '">%s</div>',
			self::GENERIC_LIST_WRAPPER => '<div class="' . self::GENERIC_LIST_WRAPPER->value . '">%s</div>',
			self::EMPTY => '<div>%s</div>',
		};
	}
}
