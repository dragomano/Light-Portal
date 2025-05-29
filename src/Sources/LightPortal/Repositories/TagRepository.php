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

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Icon;

if (! defined('SMF'))
	die('No direct access...');

final class TagRepository extends AbstractRepository
{
	protected string $entity = 'tag';

	public function getAll(int $start, int $limit, string $sort): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT tag.*, COALESCE(t.title, tf.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_tags AS tag
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					tag.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($this->getLangQueryParams(), [
				'sort'  => $sort,
				'start' => $start,
				'limit' => $limit,
			])
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);

			$items[$row['tag_id']] = [
				'id'     => (int) $row['tag_id'],
				'slug'   => $row['slug'],
				'icon'   => Icon::parse($row['icon']),
				'status' => (int) $row['status'],
				'title'  => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT COUNT(tag_id)
			FROM {db_prefix}lp_tags',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	public function getData(int $item): array
	{
		if ($item === 0)
			return [];

		$result = Db::$db->query('', '
			SELECT tag.*, COALESCE(t.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_tags AS tag
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
			WHERE tag.tag_id = {int:item}',
			array_merge($this->getLangQueryParams(), compact('item'))
		);

		if (empty(Db::$db->num_rows($result))) {
			Utils::$context['error_link'] = Config::$scripturl . '?action=admin;area=lp_tags';

			ErrorHandler::fatalLang('lp_tag_not_found', false, status: 404);
		}

		while ($row = Db::$db->fetch_assoc($result)) {
			$data ??= [
				'id'     => (int) $row['tag_id'],
				'slug'   => $row['slug'],
				'icon'   => $row['icon'],
				'status' => (int) $row['status'],
				'title'  => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $data ?? [];
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || $this->request()->hasNot(['save', 'save_exit'])) {
			return;
		}

		Security::checkSubmitOnce('check');

		if (empty($item)) {
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		$this->session('lp')->free('active_tags');

		if ($this->request()->has('save_exit')) {
			$this->response()->redirect('action=admin;area=lp_tags;sa=main');
		}

		if ($this->request()->has('save')) {
			$this->response()->redirect('action=admin;area=lp_tags;sa=edit;id=' . $item);
		}
	}

	public function remove(array $items): void
	{
		if ($items === [])
			return;

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_tags
			WHERE tag_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_translations
			WHERE item_id IN ({array_int:items})
				AND type = {literal:tag}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_page_tag
			WHERE tag_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$this->cache()->flush();

		$this->session('lp')->free('active_tags');
	}

	private function addData(): int
	{
		Db::$db->transaction('begin');

		$item = (int) Db::$db->insert('',
			'{db_prefix}lp_tags',
			[
				'slug'   => 'string',
				'icon'   => 'string-60',
				'status' => 'int',
			],
			[
				Utils::$context['lp_tag']['slug'],
				Utils::$context['lp_tag']['icon'],
				Utils::$context['lp_tag']['status'],
			],
			['tag_id'],
			1
		);

		if (empty($item)) {
			Db::$db->transaction('rollback');
			return 0;
		}

		$this->saveTranslations($item);

		Db::$db->transaction();

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('', '
			UPDATE {db_prefix}lp_tags
			SET slug = {string:slug}, icon = {string:icon}, status = {int:status}
			WHERE tag_id = {int:tag_id}',
			[
				'slug'   => Utils::$context['lp_tag']['slug'],
				'icon'   => Utils::$context['lp_tag']['icon'],
				'status' => Utils::$context['lp_tag']['status'],
				'tag_id' => $item,
			]
		);

		$this->saveTranslations($item, 'replace');

		Db::$db->transaction();
	}
}
