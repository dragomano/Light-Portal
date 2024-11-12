<?php

/**
 * @package PageList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */

namespace Bugo\LightPortal\Plugins\PageList;

use Bugo\Compat\{Config, Db, Lang, User};
use Bugo\LightPortal\Areas\Fields\{CustomField, NumberField, VirtualSelectField};
use Bugo\LightPortal\Areas\Partials\{CategorySelect, EntryTypeSelect};
use Bugo\LightPortal\Enums\{EntryType, Permission, Status, Tab};
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\{DateTime, Setting, Str};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageList extends Block
{
	public string $icon = 'far fa-file-alt';

	private const SORTING_SET = [
		'page_id', 'author_name', 'title', 'slug', 'type', 'num_views', 'created_at', 'updated_at'
	];

	public function prepareBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'categories' => '',
			'types'      => EntryType::DEFAULT->name(),
			'sort'       => 'page_id',
			'num_pages'  => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'categories' => FILTER_DEFAULT,
			'types'      => FILTER_DEFAULT,
			'sort'       => FILTER_DEFAULT,
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

		CustomField::make('types', Lang::$txt['lp_page_type'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new EntryTypeSelect(), [
				'id'    => 'types',
				'value' => $options['types'] ?? '',
			]);

		VirtualSelectField::make('sort', $this->txt['sort'])
			->setOptions(array_combine(self::SORTING_SET, $this->txt['sort_set']))
			->setValue($options['sort']);

		NumberField::make('num_pages', $this->txt['num_pages'])
			->setDescription($this->txt['num_pages_subtext'])
			->setAttribute('min', 0)
			->setAttribute('max', 999)
			->setValue($options['num_pages']);
	}

	public function getData(array $parameters): array
	{
		$titles = $this->getEntityData('title');

		$allCategories = $this->getEntityData('category');

		$categories = empty($parameters['categories']) ? null : explode(',', (string) $parameters['categories']);

		$type = empty($parameters['types']) ? EntryType::DEFAULT->name() : $parameters['types'];

		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.category_id, p.slug, p.type, p.num_views, p.num_comments, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
			WHERE p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type = {string:entry_type}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . ($categories ? '
				AND p.category_id IN ({array_int:categories})' : '') . '
			ORDER BY {raw:sort} DESC' . (empty($parameters['num_pages']) ? '' : '
			LIMIT {int:limit}'),
			[
				'guest'        => Lang::$txt['guest_title'],
				'status'       => Status::ACTIVE->value,
				'entry_type'   => $type,
				'current_time' => time(),
				'permissions'  => Permission::all(),
				'categories'   => $categories,
				'sort'         => $parameters['sort'],
				'limit'        => $parameters['num_pages'],
			]
		);

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			$pages[$row['page_id']] = [
				'id'            => $row['page_id'],
				'category_id'   => $row['category_id'],
				'category_name' => $allCategories[$row['category_id']]['title'],
				'category_link' => LP_BASE_URL . ';sa=categories;id=' . $row['category_id'],
				'title'         => $titles[$row['page_id']] ?? [],
				'author_id'     => $row['author_id'],
				'author_name'   => $row['author_name'],
				'slug'          => $row['slug'],
				'num_views'     => $row['num_views'],
				'num_comments'  => $row['num_comments'],
				'created_at'    => $row['created_at'],
				'updated_at'    => $row['updated_at']
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

		$pageList = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if ($pageList) {
			$ul = Str::html('ul', ['class' => 'normallist page_list']);

			foreach ($pageList as $page) {
				if (empty($title = Str::getTranslatedTitle($page['title']))) {
					continue;
				}

				$li = Str::html('li');
				$link = Str::html('a', [
					'href' => Config::$scripturl . '?' . LP_PAGE_PARAM . '=' . $page['slug'],
				])->setText($title);

				$author = empty($page['author_id']) ? $page['author_name'] : Str::html('a', [
					'href' => Config::$scripturl . '?action=profile;u=' . $page['author_id'],
				])->setText($page['author_name']);

				$li->addHtml($link)
					->addHtml(' ' . Lang::$txt['by'] . ' ' . $author . ', ' . DateTime::relative($page['created_at']) . ' (')
					->addHtml(Lang::getTxt('lp_views_set', ['views' => $page['num_views']]));

				if ($page['num_comments'] && Setting::getCommentBlock() === 'default') {
					$li->addHtml(', ' . Lang::getTxt('lp_comments_set', ['comments' => $page['num_comments']]));
				}

				$li->addHtml(')');
				$ul->addHtml($li);
			}

			echo $ul;
		} else {
			echo Str::html('div', ['class' => 'errorbox'])->setText($this->txt['no_items']);
		}
	}
}
