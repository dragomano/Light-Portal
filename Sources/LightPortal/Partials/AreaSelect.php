<?php declare(strict_types=1);

/**
 * AreaSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Partials;

final class AreaSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params['data'] = [
			'all'    => 'all',
			'pages'  => 'pages',
			'boards' => 'boards',
			'topics' => 'topics',
		];

		$params['value'] = explode(',', $this->context['lp_block']['areas']);
		$params['data'] = array_merge($params['data'], array_combine($params['value'], $params['value']));

		$data = $values = [];
		foreach ($params['data'] as $value => $text) {
			if (in_array($value, $params['value']))
				$values[] = $value;

			$data[] = [
				'label' => $text,
				'value' => $value
			];
		}

		return /** @lang text */ '
		<div id="areas" name="areas"></div>
		<script>
			VirtualSelect.init({
				ele: "#areas",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				allowNewOption: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . $this->txt['lp_block_areas_subtext'] . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				clearButtonText: "' . $this->txt['remove'] . '",
				options: ' . json_encode($data) . ',
				selectedValue: ' . json_encode($values) . '
			});
		</script>';
	}
}
