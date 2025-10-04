<?php declare(strict_types=1);

/**
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 04.10.25
 */

namespace Bugo\LightPortal\Plugins\GalleryBlock;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\LightPortal\UI\Partials\AbstractSelect;
use Bugo\LightPortal\Utils\Traits\HasCache;

if (! defined('LP_NAME'))
	die('No direct access...');

final class CategorySelect extends AbstractSelect
{
	use HasCache;

	public function getData(): array
	{
		$data = [];
		foreach ($this->params['data'] as $id => $title) {
			$data[] = [
				'label' => $title,
				'value' => $id,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'categories',
			'multiple' => true,
			'hint'     => Lang::$txt['lp_gallery_block']['categories_select'],
			'data'     => $this->getGalleryCategories(),
			'value'    => $this->normalizeValue($this->params['categories']),
		];
	}

	private function getGalleryCategories(): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'gallery_cat')))
			return [];

		if (($categories = $this->cache()->get('smf_gallery_categories')) === null) {
			$result = Db::$db->query('
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
