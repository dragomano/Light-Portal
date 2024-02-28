<?php declare(strict_types=1);

/**
 * AbstractRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\{Db, Msg, Utils};
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractRepository
{
	use Helper;

	protected string $entity;

	public function toggleStatus(array $items = [], string $type = 'block'): void
	{
		if (empty($items))
			return;

		switch ($type) {
			case 'block':
				$table = 'blocks';
				break;
			case 'page':
				$table = 'pages';
				break;
			case 'category':
				$table = 'categories';
				break;
			case 'tag':
				$table = 'tags';
				break;
		}

		if (empty($table))
			return;

		Db::$db->query('', '
			UPDATE {db_prefix}lp_' . $table . '
			SET status = CASE status WHEN 1 THEN 0 WHEN 0 THEN 1 WHEN 2 THEN 1 WHEN 3 THEN 0 END
			WHERE ' . $type . '_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Utils::$context['lp_num_queries']++;

		$this->session('lp')->free('active_' . $table);
	}

	protected function prepareBbcContent(array &$entity): void
	{
		if ($entity['type'] !== 'bbc')
			return;

		$entity['content'] = Utils::$smcFunc['htmlspecialchars']($entity['content'], ENT_QUOTES);

		Msg::preparseCode($entity['content']);
	}

	protected function saveTitles(int $item, string $method = ''): void
	{
		if (empty(Utils::$context['lp_' . $this->entity]['titles']))
			return;

		$titles = [];
		foreach (Utils::$context['lp_' . $this->entity]['titles'] as $lang => $title) {
			$titles[] = [
				'item_id' => $item,
				'type'    => $this->entity,
				'lang'    => $lang,
				'title'   => $title,
			];
		}

		if (empty($titles))
			return;

		Db::$db->insert($method,
			'{db_prefix}lp_titles',
			[
				'item_id' => 'int',
				'type'    => 'string',
				'lang'    => 'string',
				'title'   => 'string',
			],
			$titles,
			['item_id', 'type', 'lang']
		);

		Utils::$context['lp_num_queries']++;
	}

	protected function saveOptions(int $item, string $method = ''): void
	{
		if (empty(Utils::$context['lp_' . $this->entity]['options']))
			return;

		$params = [];
		foreach (Utils::$context['lp_' . $this->entity]['options'] as $name => $value) {
			$value = is_array($value) ? implode(',', $value) : $value;

			$params[] = [
				'item_id' => $item,
				'type'    => $this->entity,
				'name'    => $name,
				'value'   => $value,
			];
		}

		if (empty($params))
			return;

		Db::$db->insert($method,
			'{db_prefix}lp_params',
			[
				'item_id' => 'int',
				'type'    => 'string',
				'name'    => 'string',
				'value'   => 'string',
			],
			$params,
			['item_id', 'type', 'name'],
		);

		Utils::$context['lp_num_queries']++;
	}
}
