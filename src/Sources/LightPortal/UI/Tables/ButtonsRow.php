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
use LightPortal\Utils\Str;

class ButtonsRow extends Row
{
	private static function getSelectionExpression(string $formName): string
	{
		return "Array.from(document.forms['$formName']?.elements || [])"
			. ".some(el => el.name === 'items[]' && el.checked)";
	}

	public static function massActions(
		string $formName = '',
		string $actionName = '',
		array $options = ['delete' => 'remove'],
		string $value = ''
	): static
	{
		$selectionExpr = self::getSelectionExpression($formName);

		$selectOptions = '';
		foreach ($options as $action => $label) {
			$selectOptions .= Str::html('option', ['value' => $action])
				->setText(__($label))
				->toHtml();
		}

		$submit = Str::html('input', [
			'type'      => 'submit',
			'name'      => 'mass_actions',
			'value'     => __('quick_mod_go'),
			'class'     => 'button',
			':disabled' => '!hasSelection',
			'onclick'   => "return document.forms['$formName']['$actionName'].value && confirm('" . __('quickmod_confirm') . "');",
		]);

		$select = Str::html('select', [
			'name'      => $actionName,
			':disabled' => '!hasSelection',
		])
			->setHtml($selectOptions);

		$wrapper = Str::html('span', [
			'x-data'                        => '{ hasSelection: false }',
			'x-init'                        => "hasSelection = $selectionExpr",
			'x-on:lp-mass-selection.window' => "if (\$event.detail?.formName === '$formName') hasSelection = $selectionExpr",
		])->setHtml($value ?: $select . ' ' . $submit)->toHtml();

		return parent::make($wrapper)->setClass('floatright');
	}
}
