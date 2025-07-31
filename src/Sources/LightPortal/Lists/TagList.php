<?php declare(strict_types=1);

/**
 * TagList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Lists;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Status;

if (! defined('SMF'))
	die('No direct access...');

final class TagList implements ListInterface
{
	public function __invoke(): array
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT tag.tag_id, tag.slug, tag.icon, COALESCE(t.title, tf.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_tags AS tag
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					tag.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE tag.status = {int:status}
			ORDER BY title',
			[
				'empty_string'  => '',
				'lang'          => User::$me->language,
				'fallback_lang' => Config::$language,
				'status'        => Status::ACTIVE->value,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);

			$items[$row['tag_id']] = [
				'id'    => (int) $row['tag_id'],
				'slug'  => $row['slug'],
				'icon'  => $row['icon'],
				'title' => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
