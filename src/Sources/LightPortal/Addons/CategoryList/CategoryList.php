<?php

/**
 * CategoryList.php
 *
 * @package CategoryList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.09.23
 */

namespace Bugo\LightPortal\Addons\CategoryList;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Actions\Category;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryList extends Block
{
	public string $icon = 'fas fa-folder';

	public function getData(): array
	{
		return (new Category)->getAll(0, 0, 'c.priority');
	}

	public function prepareContent(object $data): void
	{
		if ($data->type !== 'category_list')
			return;

		$categories = $this->cache('category_list_addon_u' . $this->context['user']['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData');

		if (empty($categories)) {
			echo $this->txt['lp_category_list']['no_items'];
			return;
		}

		$currentCat = $this->context['current_action'] === 'portal' && $this->request()->has('sa') && $this->request('sa') === 'categories' ? (int) $this->request('id', 0) : false;

		// Are we watching a portal page?
		if (isset($this->context['lp_page']['category_id']))
			$currentCat = $this->context['lp_page']['category_id'];

		echo '
			<ul>';

		foreach ($categories as $id => $category) {
			echo '
				<li class="sub_bar">
					<div class="subbg"', $currentCat >= 0 && $currentCat === $id ? ' style="background: gainsboro"' : '', '>
						<a href="', $category['link'], '">', $category['name'], '</a> <span class="floatright amt">', $category['num_pages'], '</span>
					</div>
				</li>';
		}

		echo '
			</ul>';
	}
}
