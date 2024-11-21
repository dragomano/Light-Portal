<?php declare(strict_types=1);

/**
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 11.11.24
 */

namespace Bugo\LightPortal\Plugins\GalleryBlock;

use Bugo\Compat\{Config, Db, Lang, Utils};
use Bugo\LightPortal\Areas\Partials\AbstractPartial;
use Bugo\LightPortal\Utils\CacheTrait;

final class CategorySelect extends AbstractPartial
{
	use CacheTrait;

	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

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
				selectedValue: [' . $params['categories'] . ']
			});
		</script>';
	}

	private function getGalleryCategories(): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'gallery_cat')))
			return [];

		if (($categories = $this->cache()->get('smf_gallery_categories')) === null) {
			$result = Db::$db->query('', '
				SELECT id_cat, title
				FROM {db_prefix}gallery_cat
				WHERE redirect = {int:redirect}
				ORDER BY roworder',
				[
					'redirect' => 0,
				]
			);

			$categories = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$categories[$row['id_cat']] = $row['title'];
			}

			Db::$db->free_result($result);

			$this->cache()->put('smf_gallery_categories', $categories);
		}

		return $categories;
	}
}
