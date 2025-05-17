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
use Bugo\Bricks\Tables\Row;
use Bugo\Bricks\Tables\RowPosition;
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
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use WPLake\Typed\Typed;

use function array_key_exists;
use function sprintf;
use function time;

if (! defined('SMF'))
	die('No direct access...');

final class Category extends AbstractPageList
{
	use HasRequest;

	public function __construct(private readonly CardListInterface $cardList) {}

	public function show(): void
	{
		if ($this->request()->hasNot('id')) {
			$this->showAll();
		}

		$category = [
			'id' => Typed::int($this->request()->get('id'))
		];

		$categories = app(CategoryList::class)();
		if (array_key_exists($category['id'], $categories) === false) {
			Utils::$context['error_link'] = PortalSubAction::CATEGORIES->url();
			Lang::$txt['back'] = Lang::$txt['lp_all_categories'];
			ErrorHandler::fatalLang('lp_category_not_found', false, status: 404);
		}

		if ($category['id'] === 0) {
			Utils::$context['page_title'] = Lang::$txt['lp_all_pages_without_category'];
		} else {
			$category = $categories[$category['id']];
			Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_pages_with_category'], $category['title']);
		}

		Utils::$context['description'] = $category['description'] ?? '';
		Utils::$context['lp_category_edit_link'] = Config::$scripturl . '?action=admin;area=lp_categories;sa=edit;id=' . $category['id'];
		Utils::$context['canonical_url']  = PortalSubAction::CATEGORIES->url() . ';id=' . $category['id'];
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()
			->add(Lang::$txt['lp_all_categories'], PortalSubAction::CATEGORIES->url())
			->add($category['title'] ?? Lang::$txt['lp_no_category']);

		$this->cardList->show($this);

		$builder = $this->cardList->getBuilder('lp_categories');
		$builder->setItems($this->getPages(...));
		$builder->setCount($this->getTotalPages(...));

		! empty($category['description']) && $builder->addRow(
			Row::make($category['description'])
				->setClass('information')
				->setPosition(RowPosition::TOP_OF_LIST)
		);

		app(TablePresenter::class)->show($builder);

		Utils::obExit();
	}

	public function getPages(int $start, int $limit, string $sort): array
	{
		$result = Db::$db->query('', '
			SELECT
				p.*, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, {string:empty_string}) AS author_name,
				COALESCE(NULLIF(t.title, {string:empty_string}), tf.title, {string:empty_string}) AS title,
				COALESCE(NULLIF(t.content, {string:empty_string}), tf.content, {string:empty_string}) AS content,
				COALESCE(NULLIF(t.description, {string:empty_string}), tf.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE p.category_id = {int:id}
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
				'id'            => $this->request()->get('id'),
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
		$result = Db::$db->query('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE category_id = {string:id}
				AND status = {int:status}
				AND deleted_at = 0
				AND entry_type IN ({array_string:types})
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})',
			[
				'id'           => $this->request()->get('id'),
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
		Utils::$context['page_title']     = Lang::$txt['lp_all_categories'];
		Utils::$context['canonical_url']  = PortalSubAction::CATEGORIES->url();
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()->add(Utils::$context['page_title']);

		app(TablePresenter::class)->show(
			PortalTableBuilder::make('categories', Utils::$context['page_title'])
				->withParams(
					Setting::get('defaultMaxListItems', 'int', 50),
					Lang::$txt['lp_no_categories'],
					Utils::$context['canonical_url'],
					'title'
				)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					Column::make('title', Lang::$txt['lp_category'])
						->setData(static fn($entry) => $entry['icon'] . ' ' . Str::html('a', $entry['title'])
							->href($entry['link']) . (empty($entry['description'])
								? ''
								: Str::html('p', $entry['description'])
							->class('smalltext')))
						->setSort('title DESC', 'title'),
					Column::make('num_pages', Lang::$txt['lp_total_pages_column'])
						->setStyle('width: 16%')
						->setData('num_pages', 'centertext')
						->setSort('frequency DESC', 'frequency'),
				])
		);

		Utils::obExit();
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'title'): array
	{
		$result = Db::$db->query('', '
			SELECT
				COALESCE(c.category_id, 0) AS category_id, c.icon, c.priority, COUNT(p.page_id) AS frequency,
				COALESCE(NULLIF(t.title, {string:empty_string}), tf.title, {string:empty_string}) AS title,
				COALESCE(NULLIF(t.description, {string:empty_string}), tf.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS c ON (p.category_id = c.category_id)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					c.category_id = t.item_id AND t.type = {literal:category} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					c.category_id = tf.item_id AND tf.type = {literal:category} AND tf.lang = {string:fallback_lang}
				)
			WHERE (c.status = {int:status} OR p.category_id = 0)
				AND p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			GROUP BY c.category_id, c.icon, c.priority, title, description
			ORDER BY {raw:sort}' . ($limit ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'empty_string'  => '',
				'lang'          => User::$me->language,
				'fallback_lang' => Config::$language,
				'status'        => Status::ACTIVE->value,
				'types'         => EntryType::withoutDrafts(),
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['category_id']] = [
				'icon'        => Icon::parse($row['icon']),
				'link'        => PortalSubAction::CATEGORIES->url() . ';id=' . $row['category_id'],
				'priority'    => (int) $row['priority'],
				'num_pages'   => (int) $row['frequency'],
				'title'       => $row['title'] ?: Lang::$txt['lp_no_category'],
				'description' => $row['description'] ?? '',
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT COUNT(DISTINCT COALESCE(c.category_id, 0)) AS unique_category_count
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS c ON (p.category_id = c.category_id)
			WHERE (c.status = {int:status} OR p.category_id = 0)
				AND p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			LIMIT 1',
			[
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
}
