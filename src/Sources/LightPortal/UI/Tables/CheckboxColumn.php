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

use Bugo\Bricks\Tables\Column;
use LightPortal\Utils\Str;

class CheckboxColumn extends Column
{
	/**
	 * The optional signal makes the checkbox update it on every change, so the bulk
	 * actions row can enable or disable its controls; export and import tables pass
	 * no signal and stay free of extra attributes
	 */
	public static function make(string $name = 'actions', string $title = '', ?string $entity = null, string $signal = ''): static
	{
		$updateSignal = $signal === ''
			? []
			: ['data-on:input' => "\$$signal = Array.from(evt.target.form?.elements || [])"
				. ".some(el => el.name === '{$entity}[]' && el.checked)"];

		return parent::make($name, $title ?: Str::html('input', [
				'type'    => 'checkbox',
				'onclick' => 'invertAll(this, this.form)',
			] + $updateSignal)->toHtml())
			->setStyle('width: 5%')
			->setData(static fn($entry) => Str::html('input', [
				'type'    => 'checkbox',
				'value'   => $entry['id'],
				'name'    => $entity . '[]',
			] + $updateSignal), 'centertext');
	}
}
