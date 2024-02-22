<?php declare(strict_types=1);

/**
 * CategoryList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Actions\Category;
use Bugo\Compat\{Config, Database as Db, Lang, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class CategoryList implements ListInterface
{
	public function __invoke(): array
	{
		return $this->getAll();
	}

	public function getAll(): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT c.category_id, c.description, c.priority, t.title, tf.title AS fallback_title
			FROM {db_prefix}lp_categories AS c
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					c.category_id = t.item_id AND t.type = {literal:category} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					c.category_id = tf.item_id AND tf.type = {literal:category} AND tf.lang = {string:fallback_lang}
				)				
			WHERE c.status = {int:status}
			ORDER BY c.priority',
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'status'        => Category::STATUS_ACTIVE,
			]
		);

		$items = [0 => ['title' => Lang::$txt['lp_no_category']]];

		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['category_id']] = [
				'id'          => (int) $row['category_id'],
				'title'       => ($row['title'] ?: $row['fallback_title']) ?: '',
				'description' => $row['description'],
				'priority'    => (int) $row['priority'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
