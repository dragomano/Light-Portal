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

namespace Bugo\LightPortal\Actions;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

use function array_key_exists;
use function count;
use function implode;
use function sprintf;
use function time;

use const LP_BASE_URL;

if (! defined('SMF'))
	die('No direct access...');

final class Tag extends AbstractPageList
{
	use RequestTrait;

	public function show(CardListInterface $cardList): void
	{
		if ($this->request()->hasNot('id')) {
			$this->showAll();
		}

		$tag = [
			'id' => (int) $this->request('id', 0)
		];

		$tags = app('tag_list');
		if (array_key_exists($tag['id'], $tags) === false) {
			Utils::$context['error_link'] = LP_BASE_URL . ';sa=tags';
			Lang::$txt['back'] = Lang::$txt['lp_all_page_tags'];
			ErrorHandler::fatalLang('lp_tag_not_found', false, status: 404);
		}

		$tag = $tags[$tag['id']];
		Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_tags_by_key'], $tag['title']);

		Utils::$context['current_tag'] = $tag['id'];

		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=tags;id=' . $tag['id'];
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Lang::$txt['lp_all_page_tags'],
			'url'  => LP_BASE_URL . ';sa=tags',
		];

		Utils::$context['linktree'][] = [
			'name' => $tag['title'],
		];

		$cardList->show($this);

		$builder = $cardList->getBuilder('lp_tags');
		$builder->setItems($this->getPages(...));
		$builder->setCount(fn() => $this->getTotalCount());

		TablePresenter::show($builder);

		Utils::obExit();
	}

	public function getPages(int $start, int $limit, string $sort): array
	{
		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.slug, p.description, p.content,
				p.type, p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, \'\') AS author_name, COALESCE(t.value, tf.value) AS title,
				COALESCE(tt.value, ttf.value) AS tag_title
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tt ON (
					pt.tag_id = tt.item_id AND tt.type = {literal:tag} AND tt.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS ttf ON (
					pt.tag_id = ttf.item_id AND ttf.type = {literal:tag} AND ttf.lang = {string:fallback_lang}
				)
			WHERE pt.tag_id = {int:id}
				AND tag.status = {int:status}
				AND p.status = {int:status}
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'id'            => Utils::$context['current_tag'],
				'status'        => Status::ACTIVE->value,
				'types'         => EntryType::withoutDrafts(),
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$rows = Db::$db->fetch_all($result);

		Db::$db->free_result($result);

		return $this->getPreparedResults($rows);
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
			WHERE pt.tag_id = {int:id}
				AND tag.status = {int:status}
				AND p.status  = {int:status}
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			[
				'id'           => Utils::$context['current_tag'],
				'status'       => Status::ACTIVE->value,
				'types'        => EntryType::withoutDrafts(),
				'current_time' => time(),
				'permissions'  => Permission::all(),
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	public function showAll(): void
	{
		Utils::$context['page_title']     = Lang::$txt['lp_all_page_tags'];
		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=tags';
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title'],
		];

		TablePresenter::show(
			PortalTableBuilder::make('tags', Utils::$context['page_title'])
				->withParams(
					Setting::get('defaultMaxListItems', 'int', 50),
					Lang::$txt['lp_no_tags'],
					Utils::$context['canonical_url'],
					'value'
				)
				->setItems($this->getAll(...))
				->setCount(fn() => count($this->getAll()))
				->addColumns([
					Column::make('value', Lang::$txt['lp_tag_column'])
						->setData(static fn($entry) => implode('', [
							$entry['icon'] . ' ',
							Str::html('a', $entry['title'])
								->href($entry['link']),
						]))
						->setSort('title DESC', 'title'),
					Column::make('frequency', Lang::$txt['lp_frequency_column'])
						->setData('frequency', 'centertext')
						->setSort('frequency DESC', 'frequency'),
				])
		);

		Utils::obExit();
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'title'): array
	{
		$result = Db::$db->query('', '
			SELECT tag.tag_id, tag.icon, COALESCE(tt.value, tf.value) AS title, COUNT(tag.tag_id) AS frequency
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
				LEFT JOIN {db_prefix}lp_titles AS tt ON (
					pt.tag_id = tt.item_id AND tt.type = {literal:tag} AND tt.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					pt.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE p.status = {int:status}
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND tag.status = {int:status}
			GROUP BY tag.tag_id, tag.icon, tt.value, tf.value
			ORDER BY {raw:sort}' . ($limit ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'types'         => EntryType::names(),
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'status'        => Status::ACTIVE->value,
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] = [
				'icon'      => Icon::parse($row['icon']),
				'title'     => $row['title'],
				'link'      => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id'],
				'frequency' => (int) $row['frequency'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
