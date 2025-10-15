<?php declare(strict_types=1);

/**
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 15.10.25
 */

namespace Bugo\LightPortal\Plugins\GalleryBlock;

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
		if (! $this->sql->tableExists('gallery_cat'))
			return [];

		if (($categories = $this->cache()->get('smf_gallery_categories')) === null) {
			$select = $this->sql->select()
				->from('gallery_cat')
				->columns(['id_cat', 'title'])
				->where(['redirect' => 0])
				->order('roworder');

			$result = $this->sql->execute($select);

			$categories = [];
			foreach ($result as $row) {
				$categories[$row['id_cat']] = $row['title'];
			}

			$this->cache()->put('smf_gallery_categories', $categories);
		}

		return $categories;
	}
}
