<?php

/**
 * @package RandomPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\RandomPages;

use Bugo\Compat\{Config, Lang, User, Utils};
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
		if (Utils::$context['current_block']['type'] !== 'random_pages')
			return;

		$e->args->params = [
			'no_content_class' => true,
			'categories'       => '',
			'num_pages'        => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'random_pages')
			return;

		$e->args->params = [
			'categories' => FILTER_DEFAULT,
			'num_pages'  => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'random_pages')
			return;

		CustomField::make('categories', Lang::$txt['lp_categories'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'    => 'categories',
				'hint'  => Lang::$txt['lp_random_pages']['categories_select'],
				'value' => Utils::$context['lp_block']['options']['categories'] ?? '',
			]);

		NumberField::make('num_pages', Lang::$txt['lp_random_pages']['num_pages'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_pages']);
	}

	public function getData(array $parameters): array
	{
		$categories = empty($parameters['categories']) ? null : explode(',', (string) $parameters['categories']);
		$pagesCount = empty($parameters['num_pages']) ? 0 : (int) $parameters['num_pages'];

		if (empty($pagesCount))
			return [];

		$titles = $this->getEntityData('title');

		if (Config::$db_type === 'postgresql') {
			$result = Utils::$smcFunc['db_query']('', '
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
			while ($row = Utils::$smcFunc['db_fetch_assoc']($result))
				$pageIds[] = $row['page_id'];

			Utils::$smcFunc['db_free_result']($result);

			if (empty($pageIds))
				return $this->getData(array_merge($parameters, ['num_pages' => $pagesCount - 1]));

			$result = Utils::$smcFunc['db_query']('', '
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
			$result = Utils::$smcFunc['db_query']('', '
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
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
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

		Utils::$smcFunc['db_free_result']($result);

		return $pages;
	}

	/**
	 * @throws IntlException
	 */
	public function prepareContent(Event $e): void
	{
		[$data, $parameters] = [$e->args->data, $e->args->parameters];

		if ($data->type !== 'random_pages')
			return;

		$randomPages = $this->cache('random_pages_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if ($randomPages) {
			echo '
			<ul class="random_pages noup">';

			foreach ($randomPages as $page) {
				if (empty($title = Str::getTranslatedTitle($page['title'])))
					continue;

				echo '
				<li class="windowbg">
					<a href="', Config::$scripturl, '?', LP_PAGE_PARAM, '=', $page['slug'], '">', $title, '</a> ', Lang::$txt['by'], ' ', (empty($page['author_id']) ? $page['author_name'] : '<a href="' . Config::$scripturl . '?action=profile;u=' . $page['author_id'] . '">' . $page['author_name'] . '</a>'), ', ', DateTime::relative($page['created_at']), ' (', Lang::getTxt('lp_views_set', ['views' => $page['num_views']]);

				echo ')
				</li>';
			}

			echo '
			</ul>';
		} else {
			echo '<div class="infobox">', Lang::$txt['lp_random_pages']['none'], '</div>';
		}
	}
}
