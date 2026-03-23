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

class ExportButtonsRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		return parent::make($value ?: implode('', [
			HiddenInput::make(),
			Button::make('export_selection', __('lp_export_selection')),
			Button::make('export_all', __('lp_export_all')),
		]), $class ?? '');
	}
}
