<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Utils;

use Bugo\LightPortal\Lists;

if (! defined('SMF'))
	die('No direct access...');

final class EntityManager
{
	use CacheTrait;

	public function __invoke(string $entity): array
	{
		return match ($entity) {
			'category' => $this->cache('all_categories')->setFallback(Lists\CategoryList::class),
			'page'     => $this->cache('all_pages')->setFallback(Lists\PageList::class),
			'tag'      => $this->cache('all_tags')->setFallback(Lists\TagList::class),
			'title'    => $this->cache('all_titles')->setFallback(Lists\TitleList::class),
			'icon'     => $this->cache('all_icons')->setFallback(Lists\IconList::class),
			'plugin'   => (new Lists\PluginList())(),
			default    => [],
		};
	}
}
