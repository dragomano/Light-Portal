<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Traits\HasCache;

use function urlencode;

use const LP_CACHE_TIME;
use const LP_PAGE_PARAM;

class BuildRoute
{
	use HasCache;

	private ?array $categories;

	private ?array $tags;

	public function __construct()
	{
		if (($this->categories = $this->cache()->get('lp_sef_categories', LP_CACHE_TIME)) === null) {
			$result = Db::$db->query('', /** @lang text */ '
				SELECT c.category_id, t.value AS title
				FROM {db_prefix}lp_categories AS c
					LEFT JOIN {db_prefix}lp_titles AS t ON (
						c.category_id = t.item_id AND t.type = {literal:category} AND t.lang = {string:lang}
					)
				ORDER BY c.category_id',
				[
					'lang' => Config::$language,
				]
			);

			$this->categories[0] = urlencode('no-category');
			while ($row = Db::$db->fetch_assoc($result)) {
				$this->categories[$row['category_id']] = urlencode(Utils::$smcFunc['strtolower']($row['title']));
			}

			Db::$db->free_result($result);

			$this->cache()->put('lp_sef_categories', $this->categories, LP_CACHE_TIME);
		}

		if (($this->tags = $this->cache()->get('lp_sef_tags', LP_CACHE_TIME)) == null) {
			$result = Db::$db->query('', /** @lang text */ '
				SELECT tag.tag_id, t.value AS title
				FROM {db_prefix}lp_tags AS tag
					LEFT JOIN {db_prefix}lp_titles AS t ON (
						tag.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
					)
				ORDER BY tag.tag_id',
				[
					'lang' => Config::$language,
				]
			);

			while ($row = Db::$db->fetch_assoc($result)) {
				$this->tags[$row['tag_id']] = urlencode(Utils::$smcFunc['strtolower']($row['title']));
			}

			Db::$db->free_result($result);

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
