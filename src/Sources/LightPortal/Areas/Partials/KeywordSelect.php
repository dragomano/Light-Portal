<?php declare(strict_types=1);

/**
 * KeywordSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\Compat\{Config, Lang, Utils};

final class KeywordSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		Utils::$context['lp_tags'] = $this->getEntityData('tag');

		$data = $values = [];
		foreach (Utils::$context['lp_tags'] as $value => $label) {
			$data[] = [
				'label' => $label,
				'value' => $value
			];
		}

		foreach (Utils::$context['lp_page']['keywords'] as $tag_id => $tag_data) {
			$values[] = is_array($tag_data) ? $tag_id : Utils::escapeJavaScript($tag_data);
		}

		return /** @lang text */ '
		<div id="keywords" name="keywords"></div>
		<script>
			VirtualSelect.init({
				ele: "#keywords",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				allowNewOption: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . Lang::$txt['lp_page_keywords_placeholder'] . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				noOptionsText: "' . Lang::$txt['lp_page_keywords_empty'] . '",
				clearButtonText: "' . Lang::$txt['remove'] . '",
				maxValues: ' . (Config::$modSettings['lp_page_maximum_keywords'] ?? 10) . ',
				options: ' . json_encode($data) . ',
				selectedValue: [' . implode(',', $values) . ']
			});
		</script>';
	}
}
