<?php declare(strict_types=1);

/**
 * ActionSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Partials;

final class ActionSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_disabled_actions';
		$params['data'] ??= (empty($this->modSettings['lp_disabled_actions']) ? [] : explode(',', $this->modSettings['lp_disabled_actions']));
		$params['value'] = [];

		$data = [];
		foreach ($params['data'] as $value) {
			$data[] = [
				'label' => $value,
				'value' => $this->jsEscape($value)
			];

			$params['value'][] = $this->jsEscape($value);
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: true,
				search: true,
				markSearchResults: true,
				placeholder: "' . $this->txt['lp_example'] . 'mlist, calendar",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				allOptionsSelectedText: "' . $this->txt['all'] . '",
				noOptionsText: "' . $this->txt['no'] . '",
				showValueAsTags: true,
				allowNewOption: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . implode(',', $params['value']) . ']
			});
		</script>';
	}
}
