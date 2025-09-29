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

namespace Bugo\LightPortal\Articles;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

class PageArticle extends AbstractArticle
{
	protected array $selectedCategories = [];

	public function init(): void
	{
		$this->selectedCategories = Setting::get('lp_frontpage_categories', 'array', []);

		if (empty($this->selectedCategories) && Setting::isFrontpageMode('all_pages')) {
			$this->selectedCategories = [0];
		}

		$this->params = [
			'empty_string'        => '',
			'lang'                => User::$me->language,
			'fallback_lang'       => Config::$language,
			'status'              => Status::ACTIVE->value,
			'entry_type'          => EntryType::DEFAULT->name(),
			'current_time'        => time(),
			'permissions'         => Permission::all(),
			'selected_categories' => $this->selectedCategories,
		];

		$this->orders = [
			'created;desc'      => 'p.created_at DESC',
			'created'           => 'p.created_at',
			'updated;desc'      => 'GREATEST(p.created_at, p.updated_at) DESC',
			'updated'           => 'GREATEST(p.created_at, p.updated_at)',
			'last_comment;desc' => 'comment_date DESC',
			'last_comment'      => 'comment_date',
			'title;desc'        => 'title DESC',
			'title'             => 'title',
			'author_name;desc'  => 'author_name DESC',
			'author_name'       => 'author_name',
			'num_views;desc'    => 'p.num_views DESC',
			'num_views'         => 'p.num_views',
			'num_replies;desc'  => 'p.num_comments DESC',
			'num_replies'       => 'p.num_comments',
		];

		$this->events()->dispatch(
			PortalHook::frontPages,
			[
				'columns' => &$this->columns,
				'tables'  => &$this->tables,
				'params'  => &$this->params,
				'wheres'  => &$this->wheres,
				'orders'  => &$this->orders,
			]
		);
	}

	public function getSortingOptions(): array
	{
		return [
			'created;desc'      => Lang::$txt['lp_sort_by_created_desc'],
			'created'           => Lang::$txt['lp_sort_by_created'],
			'updated;desc'      => Lang::$txt['lp_sort_by_updated_desc'],
			'updated'           => Lang::$txt['lp_sort_by_updated'],
			'last_comment;desc' => Lang::$txt['lp_sort_by_last_comment_desc'],
			'last_comment'      => Lang::$txt['lp_sort_by_last_comment'],
			'title;desc'        => Lang::$txt['lp_sort_by_title_desc'],
			'title'             => Lang::$txt['lp_sort_by_title'],
			'author_name;desc'  => Lang::$txt['lp_sort_by_author_desc'],
			'author_name'       => Lang::$txt['lp_sort_by_author'],
			'num_views;desc'    => Lang::$txt['lp_sort_by_num_views_desc'],
			'num_views'         => Lang::$txt['lp_sort_by_num_views'],
			'num_replies;desc'  => Lang::$txt['lp_sort_by_num_comments_desc'],
			'num_replies'       => Lang::$txt['lp_sort_by_num_comments'],
		];
	}

	public function getData(int $start, int $limit, string $sortType = null): iterable
	{
		$this->sorting = $sortType;

		$this->params += [
			'start' => $start,
			'limit' => $limit,
			'sort'  => $this->orders[$this->sorting],
		];

		$result = Db::$db->query(/** @lang text */ '
			SELECT
				p.*, GREATEST(p.created_at, p.updated_at) AS date,
				CASE WHEN COALESCE(par.value, "0") != "0" THEN p.num_comments ELSE 0 END AS num_comments,
				COALESCE(t.title, tf.title, {string:empty_string}) AS title,
				COALESCE(t.content, tf.content, {string:empty_string}) AS content,
				COALESCE(t.description, tf.description, {string:empty_string}) AS description,
				(
					SELECT title
					FROM {db_prefix}lp_translations
					WHERE item_id = cat.category_id
						AND type = {literal:category}
						AND lang IN ({string:lang}, {string:fallback_lang})
					ORDER BY lang = {string:lang} DESC
					LIMIT 1
				) AS cat_title,
				mem.real_name AS author_name, cat.icon as cat_icon, COALESCE(com.created_at, p.created_at) AS comment_date,
				com.author_id AS comment_author_id, mem2.real_name AS comment_author_name,
				com.message AS comment_message' . (empty($this->columns) ? '' : ', ' . implode(', ', $this->columns)) . '
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS cat ON (cat.category_id = p.category_id)
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_comments AS com ON (p.last_comment_id = com.id)
				LEFT JOIN {db_prefix}members AS mem2 ON (com.author_id = mem2.id_member)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
				LEFT JOIN {db_prefix}lp_params AS par ON (
					par.item_id = p.page_id AND par.type = {literal:page} AND par.name = {literal:allow_comments}
				)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type = {string:entry_type}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . (empty($this->selectedCategories) ? '' : '
				AND p.category_id IN ({array_int:selected_categories})') . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			$this->params,
		);

		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);
			Lang::censorText($row['content']);
			Lang::censorText($row['description']);

			if ($row['title'] === '')
				continue;

			$row['content'] = Content::parse($row['content'], $row['type']);

			$page = [
				'id'           => (int) $row['page_id'],
				'section'      => $this->getSectionData($row),
				'author'       => $this->getAuthorData($row),
				'date'         => $this->getDate($row),
				'created'      => (int) $row['created_at'],
				'updated'      => (int) $row['updated_at'],
				'last_comment' => (int) $row['comment_date'],
				'link'         => LP_PAGE_URL . $row['slug'],
				'views'        => $this->getViewsData($row),
				'replies'      => $this->getRepliesData($row),
				'is_new'       => $this->isNew($row),
				'image'        => $this->getImage($row),
				'can_edit'     => $this->canEdit($row),
				'edit_link'    => $this->getEditLink($row),
				'title'        => $row['title'],
			];

			$this->prepareTeaser($page, $row);

			$articles = [$row['page_id'] => $page];

			$this->events()->dispatch(PortalHook::frontPagesRow, ['articles' => &$articles, 'row' => $row]);

			$page = $articles[$row['page_id']];

			yield $row['page_id'] => Avatar::getWithItems([$page])[0] ?? [];
		}

		Db::$db->free_result($result);
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE p.status = {int:status}
				AND p.deleted_at = 0
				AND p.entry_type = {string:entry_type}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . (empty($this->selectedCategories) ? '' : '
				AND p.category_id IN ({array_int:selected_categories})') . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)),
			$this->params,
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	public function prepareTags(array &$pages): void
	{
		if ($pages === [])
			return;

		$result = Db::$db->query('
			SELECT tag.*, pt.page_id, COALESCE(t.title, tf.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_tags AS tag
				LEFT JOIN {db_prefix}lp_page_tag AS pt ON (tag.tag_id = pt.tag_id)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					pt.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					pt.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE pt.page_id IN ({array_int:pages})
				AND tag.status = {int:status}
			ORDER BY title',
			[
				'empty_string'  => '',
				'lang'          => User::$me->language,
				'fallback_lang' => Config::$language,
				'pages'         => array_keys($pages),
				'status'        => Status::ACTIVE->value,
			]
		);

		while ($row = Db::$db->fetch_assoc($result)) {
			if ($row['title'] === '')
				continue;

			$pages[$row['page_id']]['tags'][] = [
				'slug' => $row['slug'],
				'icon' => Icon::parse($row['icon']),
				'href' => PortalSubAction::TAGS->url() . ';id=' . $row['tag_id'],
				'name' => $row['title'],
			];
		}

		Db::$db->free_result($result);
	}

	private function getSectionData(array $row): array
	{
		return [
			'icon' => Icon::parse($row['cat_icon']),
			'name' => empty($row['category_id']) ? '' : $row['cat_title'],
			'link' => empty($row['category_id']) ? '' : (PortalSubAction::CATEGORIES->url() . ';id=' . $row['category_id']),
		];
	}

	private function getAuthorData(array $row): array
	{
		$authorId   = $row['author_id'];
		$authorName = $row['author_name'];

		if (str_contains($this->sorting, 'last_comment') && $row['num_comments']) {
			$authorId   = $row['comment_author_id'];
			$authorName = $row['comment_author_name'];
		}

		return [
			'id'   => (int) $authorId,
			'link' => Config::$scripturl . '?action=profile;u=' . $authorId,
			'name' => $authorName,
		];
	}

	private function getDate(array $row): int
	{
		if (str_contains($this->sorting, 'last_comment') && $row['comment_date']) {
			return (int) $row['comment_date'];
		}

		if (str_contains($this->sorting, 'updated')) {
			return (int) $row['date'];
		}

		return (int) $row['created_at'];
	}

	private function getViewsData(array $row): array
	{
		return [
			'num'   => (int) $row['num_views'],
			'title' => Lang::$txt['lp_views'],
			'after' => '',
		];
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => Setting::getCommentBlock() === 'default' ? (int) $row['num_comments'] : 0,
			'title' => Lang::$txt['lp_comments'],
			'after' => '',
		];
	}

	private function isNew(array $row): bool
	{
		return User::$me->last_login < $row['date'] && (int) $row['author_id'] !== User::$me->id;
	}

	private function getImage(array $row): string
	{
		if (empty(Config::$modSettings['lp_show_images_in_articles']))
			return '';

		return Str::getImageFromText($row['content']);
	}

	private function canEdit(array $row): bool
	{
		return User::$me->is_admin
			|| User::$me->allowedTo('light_portal_manage_pages_any')
			|| (User::$me->allowedTo('light_portal_manage_pages_own') && (int) $row['author_id'] === User::$me->id);
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id'];
	}

	private function prepareTeaser(array &$page, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']))
			return;

		$page['teaser'] = Str::getTeaser($row['description'] ?: $row['content']);
	}
}
