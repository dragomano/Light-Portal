<?php

/**
 * CategoryList.php
 *
 * @package CategoryList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 11.03.22
 */

namespace Bugo\LightPortal\Addons\CategoryList;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Lists\Category;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryList extends Plugin
{
	public string $icon = 'fas fa-folder';

	public function getData(): array
	{
		return (new Category)->getAll(0, 0, 'c.priority');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time)
	{
		if ($type !== 'category_list')
			return;

		$categories = $this->cache('category_list_addon_u' . $this->context['user']['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData');

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
				<li class="sub_bar', $currentCat >= 0 && $currentCat === $id ? ' roundframe' : '', '">
					<div class="subbg">
						<a href="', $category['link'], '">', $category['name'], '</a> <span class="floatright amt">', $category['num_pages'], '</span>
					</div>
				</li>';
		}

		echo '
			</ul>';
	}
}
