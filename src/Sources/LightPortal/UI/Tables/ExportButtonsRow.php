<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Row;
use Bugo\Compat\Lang;

class ExportButtonsRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		return parent::make($value ?: implode('', [
			HiddenInput::make(),
			Button::make('export_selection', Lang::$txt['lp_export_selection']),
			Button::make('export_all', Lang::$txt['lp_export_all']),
		]), $class ?? '');
	}
}
