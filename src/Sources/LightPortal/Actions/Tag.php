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

namespace Bugo\LightPortal\Actions;

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\TablePresenter;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use WPLake\Typed\Typed;

use function Bugo\LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

final class Tag extends AbstractPageList
{
	use HasRequest;

	public function __construct(private readonly CardListInterface $cardList) {}

	public function show(): void
	{
		if ($this->request()->hasNot('id')) {
			$this->showAll();
		}

		$tag = [
			'id' => Typed::int($this->request()->get('id'))
		];

		$tags = app(TagList::class)();
		if (array_key_exists($tag['id'], $tags) === false) {
			Utils::$context['error_link'] = PortalSubAction::TAGS->url();
			Lang::$txt['back'] = Lang::$txt['lp_all_page_tags'];
			ErrorHandler::fatalLang('lp_tag_not_found', false, status: 404);
		}

		$tag = $tags[$tag['id']];
		Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_tags_by_key'], $tag['title']);

		Utils::$context['current_tag'] = $tag['id'];

		Utils::$context['canonical_url']  = PortalSubAction::TAGS->url() . ';id=' . $tag['id'];
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()
			->add(Lang::$txt['lp_all_page_tags'], PortalSubAction::TAGS->url())
			->add($tag['title']);

		$this->cardList->show($this);

		$builder = $this->cardList->getBuilder('lp_tags');
		$builder->setItems($this->getPages(...));
		$builder->setCount($this->getTotalPages(...));

		app(TablePresenter::class)->show($builder);

		Utils::obExit();
	}

	public function getPages(int $start, int $limit, string $sort): array
	{
		$result = Db::$db->query('
			SELECT
				p.*, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, {string:empty_string}) AS author_name,
				COALESCE(NULLIF(t.title, {string:empty_string}), tf.title, {string:empty_string}) AS title,
				COALESCE(NULLIF(t.content, {string:empty_string}), tf.content, {string:empty_string}) AS content,
				COALESCE(NULLIF(t.description, {string:empty_string}), tf.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE pt.tag_id = {int:id}
				AND tag.status = {int:status}
				AND p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'empty_string'  => '',
				'lang'          => User::$me->language,
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

	public function getTotalPages(): int
	{
		$result = Db::$db->query('
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
			WHERE pt.tag_id = {int:id}
				AND tag.status = {int:status}
				AND p.status  = {int:status}
				AND p.deleted_at = 0
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
		Utils::$context['canonical_url']  = PortalSubAction::TAGS->url();
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()->add(Utils::$context['page_title']);

		app(TablePresenter::class)->show(
			PortalTableBuilder::make('tags', Utils::$context['page_title'])
				->withParams(
					Setting::get('defaultMaxListItems', 'int', 50),
					Lang::$txt['lp_no_tags'],
					Utils::$context['canonical_url'],
					'value'
				)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
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
		$result = Db::$db->query('
			SELECT
				tag.tag_id, tag.slug, tag.icon, COUNT(tag.tag_id) AS frequency,
				COALESCE(NULLIF(t.title, {string:empty_string}), tf.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					pt.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					pt.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND tag.status = {int:status}
			GROUP BY tag.tag_id, tag.slug, tag.icon, title
			ORDER BY {raw:sort}' . ($limit ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'empty_string'  => '',
				'lang'          => User::$me->language,
				'fallback_lang' => Config::$language,
				'status'        => Status::ACTIVE->value,
				'types'         => EntryType::names(),
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] = [
				'slug'      => $row['slug'],
				'icon'      => Icon::parse($row['icon']),
				'link'      => PortalSubAction::TAGS->url() . ';id=' . $row['tag_id'],
				'frequency' => (int) $row['frequency'],
				'title'     => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(DISTINCT tag.tag_id) AS unique_tag_count
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
			WHERE p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND tag.status = {int:status}
			LIMIT 1',
			[
				'status'       => Status::ACTIVE->value,
				'types'        => EntryType::names(),
				'current_time' => time(),
				'permissions'  => Permission::all(),
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}
}
