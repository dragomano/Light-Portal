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

readonly class PageArticleService implements ArticleServiceInterface
{
	public function __construct(
		private PageArticleQuery $query,
		private EventDispatcherInterface $events,
		private PageRepositoryInterface $repository
	) {}

	public function init(): void
	{
		$params = [
			'lang'                => User::$me->language,
			'fallback_lang'       => Config::$language,
			'status'              => Status::ACTIVE->value,
			'entry_type'          => EntryType::DEFAULT->name(),
			'current_time'        => time(),
			'deleted_at'          => 0,
			'permissions'         => Permission::all(),
			'selected_categories' => Setting::get('lp_frontpage_categories', 'array', []),
		];

		$this->query->init($params);
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

	public function getData(int $start, int $limit, ?string $sortType): iterable
	{
		$this->query->setSorting($sortType);
		$this->query->prepareParams($start, $limit);

		foreach ($this->query->getRawData() as $row) {
			Lang::censorText($row['title']);
			Lang::censorText($row['content']);
			Lang::censorText($row['description']);

			$row['content'] = Content::parse($row['content'], $row['type']);

			$page = [
				'id'           => $row['page_id'],
				'section'      => $this->getSectionData($row),
				'author'       => $this->getAuthorData($row),
				'date'         => $this->getDate($row),
				'created'      => $row['created_at'],
				'updated'      => $row['updated_at'],
				'last_comment' => $row['comment_date'],
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

			$this->events->dispatch(PortalHook::frontPagesRow, ['articles' => &$articles, 'row' => $row]);

			$page = $articles[$row['page_id']];

			yield $row['page_id'] => Avatar::getWithItems([$page])[0] ?? [];
		}
	}

	public function getTotalCount(): int
	{
		return $this->query->getTotalCount();
	}

	public function prepareTags(array &$pages): void
	{
		if ($pages === []) {
			return;
		}

		foreach ($this->repository->fetchTags(array_keys($pages)) as $pageId => $tag) {
			$pages[$pageId]['tags'][] = $tag;
		}
	}

	protected function getSectionData(array $row): array
	{
		return [
			'icon' => Icon::parse($row['cat_icon']),
			'name' => empty($row['category_id']) ? '' : $row['cat_title'],
			'link' => empty($row['category_id']) ? '' : (PortalSubAction::CATEGORIES->url() . ';id=' . $row['category_id']),
		];
	}

	protected function getAuthorData(array $row): array
	{
		$authorId   = $row['author_id'];
		$authorName = $row['author_name'];

		if (str_contains($this->query->getSorting(), 'last_comment') && $row['num_comments']) {
			$authorId   = $row['comment_author_id'];
			$authorName = $row['comment_author_name'];
		}

		return [
			'id'   => $authorId,
			'link' => Config::$scripturl . '?action=profile;u=' . $authorId,
			'name' => $authorName,
		];
	}

	protected function getDate(array $row): int
	{
		if (str_contains($this->query->getSorting(), 'last_comment') && $row['comment_date']) {
			return $row['comment_date'];
		}

		if (str_contains($this->query->getSorting(), 'updated')) {
			return $row['date'];
		}

		return $row['created_at'];
	}

	protected function getViewsData(array $row): array
	{
		return [
			'num'   => $row['num_views'],
			'title' => Lang::$txt['lp_views'],
			'after' => '',
		];
	}

	protected function getRepliesData(array $row): array
	{
		return [
			'num'   => Setting::getCommentBlock() === 'default' ? $row['num_comments'] : 0,
			'title' => Lang::$txt['lp_comments'],
			'after' => '',
		];
	}

	protected function isNew(array $row): bool
	{
		return User::$me->last_login < $row['date'] && $row['author_id'] !== User::$me->id;
	}

	protected function getImage(array $row): string
	{
		if (empty(Config::$modSettings['lp_show_images_in_articles']))
			return '';

		return Str::getImageFromText($row['content']);
	}

	protected function canEdit(array $row): bool
	{
		return User::$me->is_admin
			|| User::$me->allowedTo('light_portal_manage_pages_any')
			|| (User::$me->allowedTo('light_portal_manage_pages_own') && $row['author_id'] === User::$me->id);
	}

	protected function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id'];
	}

	protected function prepareTeaser(array &$page, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']))
			return;

		$page['teaser'] = Str::getTeaser($row['description'] ?: $row['content']);
	}
}
