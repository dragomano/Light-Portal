<?php declare(strict_types=1);

/**
 * @package RandomPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.12.24
 */

namespace Bugo\LightPortal\Plugins\RandomPages;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Partials\CategorySelect;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class RandomPages extends Block
{
	public string $icon = 'fas fa-random';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_categories' => '',
			'include_categories' => '',
			'num_pages'          => 10,
			'show_num_views'   => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_categories' => FILTER_DEFAULT,
			'include_categories' => FILTER_DEFAULT,
			'num_pages'          => FILTER_VALIDATE_INT,
			'show_num_views'     => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('exclude_categories', $this->txt['exclude_categories'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'    => 'exclude_categories',
				'hint'  => $this->txt['exclude_categories_select'],
				'value' => $options['exclude_categories'] ?? '',
			]);

		CustomField::make('include_categories', $this->txt['include_categories'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'    => 'include_categories',
				'hint'  => $this->txt['include_categories_select'],
				'value' => $options['include_categories'] ?? '',
			]);

		NumberField::make('num_pages', $this->txt['num_pages'])
			->setAttribute('min', 1)
			->setValue($options['num_pages']);

		CheckboxField::make('show_num_views', $this->txt['show_num_views'])
			->setValue($options['show_num_views']);
	}

	public function getData(array $parameters): array
	{
		$excludeCategories = empty($parameters['exclude_categories']) ? null : explode(',', (string) $parameters['exclude_categories']);
		$includeCategories = empty($parameters['include_categories']) ? null : explode(',', (string) $parameters['include_categories']);
		$pagesCount = empty($parameters['num_pages']) ? 0 : (int) $parameters['num_pages'];

		if (empty($pagesCount))
			return [];

		$titles = app('title_list');

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
								AND p.permissions IN ({array_int:permissions})' . (empty($excludeCategories) ? '' : '
								AND p.category_id NOT IN ({array_int:exclude_categories})') . (empty($includeCategories) ? '' : '
								AND p.category_id IN ({array_int:categories})') . '
							ORDER BY p.page_id DESC
							LIMIT 1 OFFSET {int:limit} - 1
						) max
						FROM {db_prefix}lp_pages AS p
						WHERE p.status = {int:status}
							AND p.entry_type = {string:entry_type}
							AND p.deleted_at = 0
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})' . (empty($excludeCategories) ? '' : '
							AND p.category_id NOT IN ({array_int:exclude_categories})') . (empty($includeCategories) ? '' : '
							AND p.category_id IN ({array_int:include_categories})') . '
					)
					(
						SELECT p.page_id, min, max, array[]::integer[] || p.page_id AS a, 0 AS n
						FROM {db_prefix}lp_pages AS p, b
						WHERE p.status = {int:status}
							AND p.entry_type = {string:entry_type}
							AND p.deleted_at = 0
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})
							AND p.page_id >= min + ((max - min) * random())::int' . (empty($excludeCategories) ? '' : '
							AND p.category_id NOT IN ({array_int:exclude_categories})') . (empty($includeCategories) ? '' : '
							AND p.category_id IN ({array_int:include_categories})') . '
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
							AND p.page_id <> all(a)' . (empty($includeCategories) ? '' : '
							AND p.category_id IN ({array_int:categories})') . '
							AND r.n + 1 < {int:limit}
						LIMIT 1
					)
				)
				SELECT p.page_id
				FROM {db_prefix}lp_pages AS p, r
				WHERE r.page_id = p.page_id',
				[
					'status'             => Status::ACTIVE->value,
					'entry_type'         => EntryType::DEFAULT->name(),
					'current_time'       => time(),
					'permissions'        => Permission::all(),
					'exclude_categories' => $excludeCategories,
					'include_categories' => $includeCategories,
					'limit'              => $pagesCount,
				]
			);

			$pageIds = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$pageIds[] = $row['page_id'];
			}

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
					AND p.permissions IN ({array_int:permissions})' . (empty($excludeCategories) ? '' : '
					AND p.category_id NOT IN ({array_int:exclude_categories})') . (empty($includeCategories) ? '' : '
					AND p.category_id IN ({array_int:include_categories})') . '
				ORDER BY RAND()
				LIMIT {int:limit}',
				[
					'guest'              => Lang::$txt['guest_title'],
					'status'             => Status::ACTIVE->value,
					'entry_type'         => EntryType::DEFAULT->name(),
					'current_time'       => time(),
					'permissions'        => Permission::all(),
					'exclude_categories' => $excludeCategories,
					'include_categories' => $includeCategories,
					'limit'              => $pagesCount,
				]
			);
		}

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$pages[] = [
				'page_id'     => (int) $row['page_id'],
				'slug'        => $row['slug'],
				'created_at'  => (int) $row['created_at'],
				'num_views'   => (int) $row['num_views'],
				'title'       => $titles[$row['page_id']] ?? [],
				'author_id'   => (int) $row['author_id'],
				'author_name' => $row['author_name'],
			];
		}

		Db::$db->free_result($result);

		return $pages;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;
		$parameters['show_num_views'] ??= false;

		$randomPages = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($parameters));

		if ($randomPages) {
			$ul = Str::html('ul', ['class' => $this->name . ' noup']);

			$i = 0;
			foreach ($randomPages as $page) {
				if (empty($title = Str::getTranslatedTitle($page['title'])))
					continue;

				$li = Str::html('li', ['class' => 'generic_list_wrapper bg ' . ($i % 2 === 0 ? 'odd' : 'even')]);
				$link = Str::html('a', $title)->href(Config::$scripturl . '?' . LP_PAGE_PARAM . '=' . $page['slug']);
				$author = empty($page['author_id'])
					? $page['author_name']
					: Str::html('a', $page['author_name'])
						->href(Config::$scripturl . '?action=profile;u=' . $page['author_id']);

				$li
					->addHtml($link)
					->addText(' ' . Lang::$txt['by'] . ' ')
					->addHtml($author)
					->addHtml(', ' . DateTime::relative($page['created_at']));

				$parameters['show_num_views'] && $li
					->addText(' (' . Lang::getTxt('lp_views_set', ['views' => $page['num_views']]) . ')');

				$ul->addHtml($li);
				$i++;
			}

			echo $ul;
		} else {
			echo Str::html('div', ['class' => 'infobox'])
				->setText($this->txt['none']);
		}
	}
}
