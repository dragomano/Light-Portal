<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Lists;

use Bugo\Compat\{Config, Db, Lang, User};
use Bugo\LightPortal\Enums\Status;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryList implements ListInterface
{
	public function __invoke(): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT c.category_id, c.icon, c.description, c.priority, COALESCE(t.value, tf.value) AS title
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
				'status'        => Status::ACTIVE->value,
			]
		);

		$items = [
			0 => [
				'icon'  => '',
				'title' => Lang::$txt['lp_no_category'],
			]
		];

		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['category_id']] = [
				'id'          => (int) $row['category_id'],
				'icon'        => $row['icon'],
				'title'       => $row['title'],
				'description' => $row['description'],
				'priority'    => (int) $row['priority'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
