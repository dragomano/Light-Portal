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

namespace Bugo\LightPortal\Enums;

use Bugo\LightPortal\Enums\Traits\HasHtml;
use Bugo\LightPortal\Utils\Str;

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
		$class = match ($this) {
			self::ROUNDFRAME, self::WINDOWBG => $this->value . ' noup',
			self::ROUNDFRAME2 => self::ROUNDFRAME->value,
			self::WINDOWBG2 => self::WINDOWBG->value,
			self::EMPTY => null,
			default => $this->value,
		};

		return Str::html('div', '%s')
			->class($class)
			->toHtml();
	}
}
