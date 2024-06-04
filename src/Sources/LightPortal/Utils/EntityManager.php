<?php declare(strict_types=1);

/**
 * EntityManager.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Lists\IconList;
use Bugo\LightPortal\Lists\PageList;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\Lists\TitleList;

if (! defined('SMF'))
	die('No direct access...');

final class EntityManager
{
	use Helper;

	public function __invoke(string $entity): array
	{
		return match ($entity) {
			'category' => $this->cache('all_categories')->setFallback(CategoryList::class),
			'page'     => $this->cache('all_pages')->setFallback(PageList::class),
			'tag'      => $this->cache('all_tags')->setFallback(TagList::class),
			'title'    => $this->cache('all_titles')->setFallback(TitleList::class),
			'icon'     => $this->cache('all_icons')->setFallback(IconList::class),
			'plugin'   => (new PluginList())->getAll(),
			default    => [],
		};
	}
}
