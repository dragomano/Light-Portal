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

namespace Bugo\LightPortal\Repositories;

use Bugo\LightPortal\Utils\SessionTrait;
use Bugo\Compat\{Db, Msg, Utils};

use function implode;
use function is_array;
use function preg_replace;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractRepository
{
	use SessionTrait;

	protected string $entity;

	abstract public function getData(int $item): array;

	abstract public function setData(int $item = 0);

	abstract public function remove(array $items): void;

	public function toggleStatus(array $items = []): void
	{
		if ($items === [])
			return;

		$table = match ($this->entity) {
			'category' => 'categories',
			default    => $this->entity . 's',
		};

		Db::$db->query('', '
			UPDATE {db_prefix}lp_' . $table . '
			SET status = CASE status WHEN 1 THEN 0 WHEN 0 THEN 1 WHEN 2 THEN 1 WHEN 3 THEN 0 ELSE status END
			WHERE ' . $this->entity . '_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$this->session('lp')->free('active_' . $table);
	}

	protected function prepareBbcContent(array &$entity): void
	{
		if ($entity['type'] !== 'bbc')
			return;

		$entity['content'] = Utils::htmlspecialchars($entity['content'], ENT_QUOTES);

		Msg::preparseCode($entity['content']);
	}

	protected function saveTitles(int $item, string $method = ''): void
	{
		if (empty(Utils::$context['lp_' . $this->entity]['titles']))
			return;

		$titles = [];
		foreach (Utils::$context['lp_' . $this->entity]['titles'] as $lang => $title) {
			$title = Utils::$smcFunc['htmltrim']($title);

			if ($method === '' && $title === '')
				continue;

			$titles[] = [
				'item_id' => $item,
				'type'    => $this->entity,
				'lang'    => $lang,
				'title'   => $title,
			];
		}

		if ($titles === [])
			return;

		Db::$db->insert($method,
			'{db_prefix}lp_titles',
			[
				'item_id' => 'int',
				'type'    => 'string',
				'lang'    => 'string',
				'value'   => 'string',
			],
			$titles,
			['item_id', 'type', 'lang']
		);
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

		if ($params === [])
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
	}

	protected function prepareTitles(): void
	{
		// Remove all punctuation symbols
		Utils::$context['lp_' . $this->entity]['titles'] = preg_replace(
			"#[[:punct:]]#", "", (array) Utils::$context['lp_' . $this->entity]['titles']
		);
	}
}
