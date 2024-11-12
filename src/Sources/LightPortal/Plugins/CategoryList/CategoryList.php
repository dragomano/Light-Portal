<?php

/**
 * @package CategoryList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 12.11.24
 */

namespace Bugo\LightPortal\Plugins\CategoryList;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryList extends Block
{
	public string $icon = 'fas fa-folder';

	public function getData(): array
	{
		return (new Category())->getAll(0, 0, 'c.priority');
	}

	public function prepareContent(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$categories = $this->cache($this->name . '_addon_u' . Utils::$context['user']['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData');

		if (empty($categories)) {
			echo $this->txt['no_items'];
			return;
		}

		$currentCat = Utils::$context['current_action'] === 'portal'
			&& $this->request()->has('sa')
			&& $this->request('sa') === 'categories'
				? (int) $this->request('id', 0) : false;

		if (isset(Utils::$context['lp_page']['category_id'])) {
			$currentCat = Utils::$context['lp_page']['category_id'];
		}

		echo Str::html('ul')->addHtml(
			implode('', array_map(function ($id, $category) use ($currentCat) {
				$subbg = Str::html('div', ['class' => 'subbg']);

				if ($currentCat >= 0 && $currentCat === $id) {
					$subbg->style('background: gainsboro');
				}

				$subbg->addHtml(
					Str::html('a', ['href' => $category['link']])
						->setHtml($category['icon'] . ' ' . $category['title'])
				)->addHtml(' ' . Str::html('span', $category['num_pages'])
					->class('floatright amt')
				);

				return Str::html('li', ['class' => 'sub_bar'])->addHtml($subbg);
			}, array_keys($categories), $categories))
		);
	}
}
