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

namespace LightPortal\Articles\Services;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Articles\Queries\PageArticleQuery;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Utils\Avatar;
use LightPortal\Utils\Content;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class PageArticleService extends AbstractArticleService
{
	public function __construct(
		PageArticleQuery $query,
		EventDispatcherInterface $dispatcher,
		protected PageRepositoryInterface $repository
	)
	{
		parent::__construct($query, $dispatcher);
	}

	public function getParams(): array
	{
		return [
			'lang'                => User::$me->language,
			'fallback_lang'       => Config::$language,
			'status'              => Status::ACTIVE->value,
			'entry_type'          => EntryType::DEFAULT->name(),
			'current_time'        => time(),
			'deleted_at'          => 0,
			'permissions'         => Permission::all(),
			'selected_categories' => Setting::get('lp_frontpage_categories', 'array', []),
		];
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

	public function prepareTags(array &$pages): void {
		if ($pages === [])
			return;

		foreach ($this->repository->fetchTags(array_keys($pages)) as $pageId => $tag) {
			$pages[$pageId]['tags'][] = $tag;
		}
	}

	protected function getRules(array $row): array
	{
		Lang::censorText($row['title']);
		Lang::censorText($row['content']);
		Lang::censorText($row['description']);

		$content = Content::parse($row['content'], $row['type']);

		return [
			'id' => fn($row) => $row['page_id'],

			'section' => fn($row) => [
				'icon' => Icon::parse($row['cat_icon']),
				'name' => empty($row['category_id']) ? '' : Str::decodeHtmlEntities($row['cat_title']),
				'link' => empty($row['category_id']) ? '' : (PortalSubAction::CATEGORIES->url() . ';id=' . $row['category_id']),
			],

			'author' => fn($row) => [
				'id'   => $row['author_id'],
				'link' => Config::$scripturl . '?action=profile;u=' . $row['author_id'],
				'name' => $row['author_name'],
			],

			'date' => fn($row) => str_contains($this->query->getSorting(), 'updated') ? $row['date'] : $row['created_at'],

			'created' => fn($row) => $row['created_at'],

			'updated' => fn($row) => $row['updated_at'],

			'last_comment' => fn($row) => $row['comment_date'],

			'link' => fn($row) => LP_PAGE_URL . $row['slug'],

			'views' => fn($row) => [
				'num'   => $row['num_views'],
				'title' => Lang::$txt['lp_views'],
				'after' => '',
			],

			'replies' => fn($row) => [
				'num'   => Setting::getCommentBlock() === 'default' ? $row['num_comments'] : 0,
				'title' => Lang::$txt['lp_comments'],
				'after' => '',
			],

			'is_new' => fn($row) => User::$me->last_login < $row['date'] && $row['author_id'] !== User::$me->id,

			'image' => function () use ($content) {
				if (empty(Config::$modSettings['lp_show_images_in_articles'])) {
					return '';
				}

				return Str::getImageFromText($content);
			},

			'can_edit' => fn($row) => User::$me->is_admin
				|| User::$me->allowedTo('light_portal_manage_pages_any')
				|| (User::$me->allowedTo('light_portal_manage_pages_own') && $row['author_id'] === User::$me->id),

			'edit_link' => fn($row) => Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id'],

			'title' => fn($row) => Str::decodeHtmlEntities($row['title']),

			'teaser' => function ($row) use ($content) {
				if (empty(Config::$modSettings['lp_show_teaser'])) {
					return '';
				}

				return Str::getTeaser($row['description'] ?: $content);
			},
		];
	}

	protected function getEventHook(): PortalHook
	{
		return PortalHook::frontPagesRow;
	}

	protected function finalizeItem(array $item): array
	{
		return Avatar::getWithItems([$item])[0] ?? $item;
	}
}
