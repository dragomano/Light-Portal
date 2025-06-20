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

namespace Bugo\LightPortal\UI\Tables;

use Bugo\LightPortal\Utils\Str;

class HiddenInput
{
	public static function make(): string
	{
		$button = new self();

		return $button->render();
	}

	public function render(): string
	{
		return Str::html('input', [
			'type'  => 'hidden',
		])->toHtml();
	}
}
