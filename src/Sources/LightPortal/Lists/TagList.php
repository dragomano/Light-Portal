<?php declare(strict_types=1);

/**
 * TagList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Lists;

use Bugo\Compat\{Config, Db, User, Utils};
use Bugo\LightPortal\Actions\PageListInterface;

if (! defined('SMF'))
	die('No direct access...');

final class TagList implements ListInterface
{
	public function __invoke(): array
	{
		return $this->getAll();
	}

	public function getAll(): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT tag.tag_id, tag.icon, COALESCE(t.title, tf.title) AS tag_title
			FROM {db_prefix}lp_tags AS tag
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					tag.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE tag.status = {int:status}
			ORDER BY t.title',
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'status'        => PageListInterface::STATUS_ACTIVE,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] = [
				'id'    => (int) $row['tag_id'],
				'icon'  => $row['icon'],
				'title' => $row['tag_title'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
