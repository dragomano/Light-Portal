<?php

/**
 * @package RandomPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */

namespace Bugo\LightPortal\Plugins\RandomPages;

use Bugo\Compat\{Config, Db, Lang, User};
use Bugo\LightPortal\Areas\Fields\{CustomField, NumberField};
use Bugo\LightPortal\Areas\Partials\CategorySelect;
use Bugo\LightPortal\Enums\{EntryType, Permission, Status, Tab};
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\Utils\{DateTime, Str};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class RandomPages extends Block
{
	public string $icon = 'fas fa-random';

	public function prepareBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'no_content_class' => true,
			'categories'       => '',
			'num_pages'        => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'categories' => FILTER_DEFAULT,
			'num_pages'  => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$options = $e->args->options;

		CustomField::make('categories', Lang::$txt['lp_categories'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'    => 'categories',
				'hint'  => $this->txt['categories_select'],
				'value' => $options['categories'] ?? '',
			]);

		NumberField::make('num_pages', $this->txt['num_pages'])
			->setAttribute('min', 1)
			->setValue($options['num_pages']);
	}

	public function getData(array $parameters): array
	{
		$categories = empty($parameters['categories']) ? null : explode(',', (string) $parameters['categories']);
		$pagesCount = empty($parameters['num_pages']) ? 0 : (int) $parameters['num_pages'];

		if (empty($pagesCount))
			return [];

		$titles = $this->getEntityData('title');

		if (Config::$db_type === 'postgresql') {
			$result = Db::$db->query('', '
				WITH RECURSIVE r AS (
					WITH b AS (
						SELECT min(p.page_id), (
							SELECT p.page_id FROM {db_prefix}lp_pages AS p
							WHERE p.status = {int:status}
								AND p.entry_type = {string:entry_type}
								AND p.deleted_at = 0
								AND p.created_at <= {int:current_time}
								AND p.permissions IN ({array_int:permissions})' . (empty($categories) ? '' : '
								AND p.category_id IN ({array_int:categories})') . '
							ORDER BY p.page_id DESC
							LIMIT 1 OFFSET {int:limit} - 1
						) max
						FROM {db_prefix}lp_pages AS p
						WHERE p.status = {int:status}
							AND p.entry_type = {string:entry_type}
							AND p.deleted_at = 0
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})' . (empty($categories) ? '' : '
							AND p.category_id IN ({array_int:categories})') . '
					)
					(
						SELECT p.page_id, min, max, array[]::integer[] || p.page_id AS a, 0 AS n
						FROM {db_prefix}lp_pages AS p, b
						WHERE p.status = {int:status}
							AND p.entry_type = {string:entry_type}
							AND p.deleted_at = 0
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})
							AND p.page_id >= min + ((max - min) * random())::int' . (empty($categories) ? '' : '
							AND p.category_id IN ({array_int:categories})') . '
						LIMIT 1
					) UNION ALL (
						SELECT p.page_id, min, max, a || p.page_id, r.n + 1 AS n
						FROM {db_prefix}lp_pages AS p, r
						WHERE p.status = {int:status}
							AND p.entry_type = {string:entry_type}
							AND p.deleted_at = 0
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})
							AND p.page_id >= min + ((max - min) * random())::int
							AND p.page_id <> all(a)' . (empty($categories) ? '' : '
							AND p.category_id IN ({array_int:categories})') . '
							AND r.n + 1 < {int:limit}
						LIMIT 1
					)
				)
				SELECT p.page_id
				FROM {db_prefix}lp_pages AS p, r
				WHERE r.page_id = p.page_id',
				[
					'status'       => Status::ACTIVE->value,
					'entry_type'   => EntryType::DEFAULT->name(),
					'current_time' => time(),
					'permissions'  => Permission::all(),
					'categories'   => $categories,
					'limit'        => $pagesCount,
				]
			);

			$pageIds = [];
			while ($row = Db::$db->fetch_assoc($result))
				$pageIds[] = $row['page_id'];

			Db::$db->free_result($result);

			if (empty($pageIds))
				return $this->getData(array_merge($parameters, ['num_pages' => $pagesCount - 1]));

			$result = Db::$db->query('', '
				SELECT
					p.page_id, p.slug, p.created_at, p.num_views,
					COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				WHERE p.page_id IN ({array_int:page_ids})',
				[
					'guest'    => Lang::$txt['guest_title'],
					'page_ids' => $pageIds,
				]
			);
		} else {
			$result = Db::$db->query('', '
				SELECT
					p.page_id, p.slug, p.created_at, p.num_views,
					COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				WHERE p.status = {int:status}
					AND p.entry_type = {string:entry_type}
					AND p.deleted_at = 0
					AND p.created_at <= {int:current_time}
					AND p.permissions IN ({array_int:permissions})' . (empty($categories) ? '' : '
					AND p.category_id IN ({array_int:categories})') . '
				ORDER BY RAND()
				LIMIT {int:limit}',
				[
					'guest'        => Lang::$txt['guest_title'],
					'status'       => Status::ACTIVE->value,
					'entry_type'   => EntryType::DEFAULT->name(),
					'current_time' => time(),
					'permissions'  => Permission::all(),
					'categories'   => $categories,
					'limit'        => $pagesCount,
				]
			);
		}

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$pages[] = [
				'page_id'     => $row['page_id'],
				'slug'        => $row['slug'],
				'created_at'  => $row['created_at'],
				'num_views'   => $row['num_views'],
				'title'       => $titles[$row['page_id']] ?? [],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
			];
		}

		Db::$db->free_result($result);

		return $pages;
	}

	/**
	 * @throws IntlException
	 */
	public function prepareContent(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$parameters = $e->args->parameters;

		$randomPages = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if ($randomPages) {
			$ul = Str::html('ul', ['class' => $this->name . ' noup']);

			foreach ($randomPages as $page) {
				if (empty($title = Str::getTranslatedTitle($page['title'])))
					continue;

				$li = Str::html('li', ['class' => 'windowbg']);
				$link = Str::html('a', $title)
					->href(Config::$scripturl . '?' . LP_PAGE_PARAM . '=' . $page['slug']);

				$author = empty($page['author_id'])
					? $page['author_name']
					: Str::html('a', $page['author_name'])
						->href(Config::$scripturl . '?action=profile;u=' . $page['author_id']);

				$li->addHtml($link)
					->addText(' ' . Lang::$txt['by'] . ' ')
					->addHtml($author)
					->addHtml(', ' . DateTime::relative($page['created_at']) . ' (')
					->addText(Lang::getTxt('lp_views_set', ['views' => $page['num_views']]) . ')');

				$ul->addHtml($li);
			}

			echo $ul;
		} else {
			echo Str::html('div', ['class' => 'infobox'])
				->setText($this->txt['none']);
		}
	}
}
