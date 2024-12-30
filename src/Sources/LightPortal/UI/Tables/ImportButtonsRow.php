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

use Bugo\Bricks\Tables\Row;
use Bugo\Compat\Lang;

class ImportButtonsRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		return parent::make($value ?: implode('', [
			HiddenInput::make(),
			Button::make('import_selection', Lang::$txt['lp_import_selection']),
			Button::make('import_all', Lang::$txt['lp_import_all']),
		]), $class ?? '');
	}
}
