<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Hooks;

use Bugo\Compat\Utils;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasPortalSql;
use LightPortal\Utils\Traits\HasTranslationJoins;

use const LP_CACHE_TIME;
use const LP_PAGE_PARAM;

class BuildRoute
{
	use HasCache;
	use HasPortalSql;
	use HasTranslationJoins;

	private ?array $categories;

	private ?array $tags;

	public function __construct()
	{
		if (($this->categories = $this->cache()->get('lp_sef_categories', LP_CACHE_TIME)) === null) {
			$select = $this->getPortalSql()->select()
				->from(['c' => 'lp_categories'])
				->columns(['category_id', 'slug'])
				->order('c.category_id');

			$this->addTranslationJoins($select, [
				'primary' => 'c.category_id',
				'entity'  => 'category',
			]);

			$result = $this->getPortalSql()->execute($select);

			$this->categories[0] = urlencode('no-category');
			foreach ($result as $row) {
				$this->categories[$row['category_id']] = empty($row['title'])
					? $row['slug']
					: urlencode(Utils::$smcFunc['strtolower']($row['title']));
			}

			$this->cache()->put('lp_sef_categories', $this->categories, LP_CACHE_TIME);
		}

		if (($this->tags = $this->cache()->get('lp_sef_tags', LP_CACHE_TIME)) === null) {
			$select = $this->getPortalSql()->select()
				->from(['tag' => 'lp_tags'])
				->columns(['tag_id', 'slug'])
				->order('tag.tag_id');

			$this->addTranslationJoins($select, [
				'primary' => 'tag.tag_id',
				'entity'  => 'tag',
			]);

			$result = $this->getPortalSql()->execute($select);

			foreach ($result as $row) {
				$this->tags[$row['tag_id']] = empty($row['title'])
					? $row['slug']
					: urlencode(Utils::$smcFunc['strtolower']($row['title']));
			}

			$this->cache()->put('lp_sef_tags', $this->tags, LP_CACHE_TIME);
		}
	}

	public function __invoke(&$route_base, array $params): void
	{
		if (isset($params[LP_PAGE_PARAM])) {
			$route_base = 'pages';
		}
	}
}
