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

use Bugo\Bricks\Tables\Column;
use Bugo\LightPortal\Utils\Str;

class CheckboxColumn extends Column
{
	public static function make(string $name = 'actions', string $title = '', ?string $entity = null): static
	{
		return parent::make($name, $title ?: Str::html('input', [
			'type' => 'checkbox',
			'onclick' => 'invertAll(this, this.form);',
		])->toHtml())
			->setStyle('width: 5%')
			->setData(static fn($entry) => Str::html('input', [
				'type' => 'checkbox',
				'value' => $entry['id'],
				'name' => $entity . '[]',
			]), 'centertext');
	}
}
