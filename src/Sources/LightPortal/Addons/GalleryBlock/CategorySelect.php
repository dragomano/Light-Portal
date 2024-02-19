<?php declare(strict_types=1);

/**
 * CategorySelect.php
 *
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Addons\GalleryBlock;

use Bugo\Compat\{Config, Database as Db, Lang, Utils};
use Bugo\LightPortal\Areas\Partials\AbstractPartial;

final class CategorySelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$categories = $this->getGalleryCategories();

		$data = [];
		foreach ($categories as $id => $title) {
			$data[] = [
				'label' => $title,
				'value' => $id,
			];
		}

		return /** @lang text */ '
		<div id="categories" name="categories"></div>
		<script>
			VirtualSelect.init({
				ele: "#categories",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: true,
				search: true,
				markSearchResults: true,
				placeholder: "' . Lang::$txt['lp_gallery_block']['categories_select'] . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				allOptionsSelectedText: "' . Lang::$txt['all'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . (Utils::$context['lp_block']['options']['categories'] ?? '') . ']
			});
		</script>';
	}

	private function getGalleryCategories(): array
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'gallery_cat')))
			return [];

		if (($categories = $this->cache()->get('smf_gallery_categories')) === null) {
			$result = Utils::$smcFunc['db_query']('', '
				SELECT id_cat, title
				FROM {db_prefix}gallery_cat
				WHERE redirect = {int:redirect}
				ORDER BY roworder',
				[
					'redirect' => 0,
				]
			);

			$categories = [];
			while ($row = Utils::$smcFunc['db_fetch_assoc']($result))
				$categories[$row['id_cat']] = $row['title'];

			Utils::$smcFunc['db_free_result']($result);
			Utils::$context['lp_num_queries']++;

			$this->cache()->put('smf_gallery_categories', $categories);
		}

		return $categories;
	}
}
