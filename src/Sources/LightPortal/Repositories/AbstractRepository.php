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

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Msg;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;
use Bugo\LightPortal\Utils\Traits\HasSession;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractRepository implements RepositoryInterface
{
	use HasCache;
	use HasRequest;
	use HasResponse;
	use HasSession;

	protected string $entity;

	abstract public function getData(int $item): array;

	abstract public function setData(int $item = 0): void;

	abstract public function remove(array $items): void;

	public function toggleStatus(array $items = []): void
	{
		if ($items === [])
			return;

		$table = match ($this->entity) {
			'category' => 'categories',
			default    => $this->entity . 's',
		};

		Db::$db->query('
			UPDATE {db_prefix}lp_' . $table . '
			SET status = CASE status
				WHEN 1 THEN 0
				WHEN 0 THEN 1
				WHEN 2 THEN 1
				WHEN 3 THEN 0
				ELSE status
			END
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

	protected function saveTranslations(int $item, string $method = ''): void
	{
		$rows = [
			'item_id'     => $item,
			'type'        => $this->entity,
			'lang'        => User::$me->language,
			'title'       => Utils::$context['lp_' . $this->entity]['title'] ?? '',
			'content'     => Utils::$context['lp_' . $this->entity]['content'] ?? '',
			'description' => Utils::htmlspecialchars(Utils::$context['lp_' . $this->entity]['description'] ?? ''),
		];

		$params = [
			'item_id'     => 'int',
			'type'        => 'string',
			'lang'        => 'string',
			'title'       => 'string-255',
			'content'     => 'string',
			'description' => 'string-510',
		];

		if (! Language::isDefault()) {
			$default = $this->getDefaultTranslations($item);

			foreach (['title', 'content', 'description'] as $field) {
				if ($rows[$field] === $default[$field]) {
					unset($rows[$field], $params[$field]);
				}
			}
		}

		Db::$db->insert($method, '{db_prefix}lp_translations', $params, $rows, ['item_id', 'type', 'lang']);
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

	protected function getLangQueryParams(): array
	{
		return [
			'empty_string'  => '',
			'lang'          => User::$me->language,
			'fallback_lang' => Config::$language,
		];
	}

	private function getDefaultTranslations(int $item): array
	{
		$result = Db::$db->query('
			SELECT title, content, description
			FROM {db_prefix}lp_translations
			WHERE item_id = {int:item_id}
				AND type = {string:type}
				AND lang = {string:lang}',
			[
				'item_id' => $item,
				'type'    => $this->entity,
				'lang'    => Config::$language,
			]
		);

		$translations = Db::$db->fetch_assoc($result) ?: ['title' => null, 'content' => null, 'description' => null];

		Db::$db->free_result($result);

		return $translations;
	}
}
