<?php declare(strict_types=1);

/**
 * @package CategoryList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 30.09.25
 */

namespace Bugo\LightPortal\Plugins\CategoryList;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-folder')]
class CategoryList extends Block
{
	public function getData(): array
	{
		return app(Category::class)->getAll(0, 0, 'c.priority');
	}

	#[HookAttribute(PortalHook::prepareContent)]
	public function prepareContent(Event $e): void
	{
		$categories = $this->userCache($this->name . '_addon')
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData());

		if (empty($categories)) {
			echo $this->txt['no_items'];
			return;
		}

		$currentCat = Utils::$context['current_action'] === 'portal'
			&& $this->request()->has('sa')
			&& $this->request()->get('sa') === PortalSubAction::CATEGORIES->name()
				? Typed::int($this->request()->get('id')) : false;

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
				)->addHtml(' ' . Str::html('span', (string) $category['num_pages'])
					->class('floatright amt')
				);

				return Str::html('li', ['class' => 'sub_bar'])->addHtml($subbg);
			}, array_keys($categories), $categories))
		);
	}
}
