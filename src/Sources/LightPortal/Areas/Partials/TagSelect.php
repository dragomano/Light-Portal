<?php declare(strict_types=1);

/**
 * TagSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\Compat\{Config, Lang, Utils};

final class TagSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		Utils::$context['lp_tags'] = $this->getEntityData('tag');

		$data = $values = [];
		foreach (Utils::$context['lp_tags'] as $id => $tag) {
			$data[] = [
				'label' => $this->getIcon($tag['icon']) . $tag['title'],
				'value' => $id,
			];
		}

		foreach (Utils::$context['lp_page']['tags'] as $tagId => $tagData) {
			$values[] = is_array($tagData) ? $tagId : Utils::escapeJavaScript($tagData);
		}

		return /** @lang text */ '
		<div id="tags" name="tags"></div>
		<script>
			VirtualSelect.init({
				ele: "#tags",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				allowNewOption: false,
				showSelectedOptionsFirst: true,
				placeholder: "' . Lang::$txt['lp_page_tags_placeholder'] . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				noOptionsText: "' . Lang::$txt['lp_page_tags_empty'] . '",
				clearButtonText: "' . Lang::$txt['remove'] . '",
				maxValues: ' . (Config::$modSettings['lp_page_maximum_tags'] ?? 10) . ',
				options: ' . json_encode($data) . ',
				selectedValue: [' . implode(',', $values) . ']
			});
		</script>';
	}
}
