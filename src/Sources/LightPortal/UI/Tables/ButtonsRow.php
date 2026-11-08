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
use Bugo\Compat\Utils;
use LightPortal\Utils\Str;

class ButtonsRow extends Row
{
	public static function bulkActions(
		string $formName = '',
		string $actionName = '',
		array $options = ['delete' => 'remove'],
		string $value = ''
	): static
	{
		$selectOptions = '';
		foreach ($options as $action => $label) {
			$selectOptions .= Str::html('option', ['value' => $action])
				->setText(__($label))
				->toHtml();
		}

		$submit = Str::html('input', [
			'type'               => 'submit',
			'name'               => 'bulk_actions',
			'value'              => __('quick_mod_go'),
			'class'              => 'button',
			'data-attr:disabled' => '!$has_selection',
			'data-on:click'      => self::getBulkSubmitExpression($formName, $actionName),
		]);

		$select = Str::html('select', [
			'name'               => $actionName,
			'data-attr:disabled' => '!$has_selection',
		])
			->setHtml($selectOptions);

		$wrapper = Str::html('span', [
			// The morph preserves the button state, so this row must be replaced instead
			'data-ignore-morph' => '',
			'data-signals'      => '{ has_selection: false }',
		])->setHtml($value ?: $select . ' ' . $submit)->toHtml();

		return parent::make($wrapper)->setClass('floatright');
	}

	/**
	 * The classic submit is kept as a fallback, while the Datastar click posts the selected
	 * ids together with the chosen action as one bulk_items payload. The selection is
	 * cleared before posting, because the morph of the patched list preserves input states
	 */
	private static function getBulkSubmitExpression(string $formName, string $actionName): string
	{
		$url = Utils::$context['lp_actions_url'] ?? '';

		return "evt.preventDefault();"
			. " if (!confirm('" . __('quickmod_confirm') . "')) return;"
			. " const els = Array.from(document.forms['$formName'].elements)"
			. ".filter(el => el.name === 'items[]' && el.checked);"
			. " els.forEach(el => el.checked = false);"
			. " \$has_selection = false;"
			. " @post('$url', {payload: {bulk_items: {"
			. "action: document.forms['$formName']['$actionName'].value,"
			. " items: els.map(el => el.value)"
			. "}}})";
	}
}
