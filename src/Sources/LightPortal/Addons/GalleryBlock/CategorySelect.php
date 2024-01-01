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
 * @version 09.04.23
 */

namespace Bugo\LightPortal\Addons\GalleryBlock;

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
				'value' => $id
			];
		}

		return /** @lang text */ '
		<div id="categories" name="categories"></div>
		<script>
			VirtualSelect.init({
				ele: "#categories",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: true,
				search: true,
				markSearchResults: true,
				placeholder: "' . $this->txt['lp_gallery_block']['categories_select'] . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				allOptionsSelectedText: "' . $this->txt['all'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . ($this->context['lp_block']['options']['parameters']['categories'] ?? '') . ']
			});
		</script>';
	}

	private function getGalleryCategories(): array
	{
		$this->dbExtend();

		if (empty($this->smcFunc['db_list_tables'](false, $this->db_prefix . 'gallery_cat')))
			return [];

		if (($categories = $this->cache()->get('smf_gallery_categories')) === null) {
			$result = $this->smcFunc['db_query']('', '
				SELECT id_cat, title
				FROM {db_prefix}gallery_cat
				WHERE redirect = {int:redirect}
				ORDER BY roworder',
				[
					'redirect' => 0,
				]
			);

			$categories = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($result))
				$categories[$row['id_cat']] = $row['title'];

			$this->smcFunc['db_free_result']($result);
			$this->context['lp_num_queries']++;

			$this->cache()->put('smf_gallery_categories', $categories);
		}

		return $categories;
	}
}
