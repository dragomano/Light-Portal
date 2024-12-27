<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;

use function implode;
use function is_array;
use function json_encode;

final class TagSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		Utils::$context['lp_tags'] = app('tag_list');

		$data = $values = [];
		foreach (Utils::$context['lp_tags'] as $id => $tag) {
			$data[] = [
				'label' => Icon::parse($tag['icon']) . $tag['title'],
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
				maxValues: ' . Setting::get('lp_page_maximum_tags', 'int', 10) . ',
				options: ' . json_encode($data) . ',
				selectedValue: [' . implode(',', $values) . ']
			});
		</script>';
	}
}
