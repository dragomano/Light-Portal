<?php declare(strict_types=1);

/**
 * KeywordSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Partials;

final class KeywordSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$this->context['lp_tags'] = $this->getEntityList('tag');

		$data = $values = [];
		foreach ($this->context['lp_tags'] as $value => $label) {
			$data[] = [
				'label' => $label,
				'value' => $value
			];
		}

		foreach ($this->context['lp_page']['keywords'] as $tag_id => $tag_data) {
			$values[] = is_array($tag_data) ? $tag_id : $this->jsEscape($tag_data);
		}

		return /** @lang text */ '
		<div id="keywords" name="keywords"></div>
		<script>
			VirtualSelect.init({
				ele: "#keywords",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				allowNewOption: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . $this->txt['lp_page_keywords_placeholder'] . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				noOptionsText: "' . $this->txt['lp_page_keywords_empty'] . '",
				clearButtonText: "' . $this->txt['remove'] . '",
				maxValues: ' . ($this->modSettings['lp_page_maximum_keywords'] ?? 10) . ',
				options: ' . json_encode($data) . ',
				selectedValue: [' . implode(',', $values) . ']
			});
		</script>';
	}
}
