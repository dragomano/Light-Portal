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

namespace Bugo\LightPortal\Lists;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Status;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryList implements ListInterface
{
	public function __invoke(): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT
				c.*,
				COALESCE(t.title, tf.title, {string:empty_string}) AS title,
				COALESCE(t.description, tf.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_categories AS c
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					c.category_id = t.item_id AND t.type = {literal:category} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					c.category_id = tf.item_id AND tf.type = {literal:category} AND tf.lang = {string:fallback_lang}
				)
			WHERE c.status = {int:status}
			ORDER BY c.priority',
			[
				'empty_string'  => '',
				'lang'          => User::$me->language,
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
			Lang::censorText($row['title']);
			Lang::censorText($row['description']);

			$items[$row['category_id']] = [
				'id'          => (int) $row['category_id'],
				'slug'        => $row['slug'],
				'icon'        => $row['icon'],
				'priority'    => (int) $row['priority'],
				'title'       => $row['title'],
				'description' => $row['description'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
