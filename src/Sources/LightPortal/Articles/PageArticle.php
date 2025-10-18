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

namespace LightPortal\Articles;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Enums\Status;
use LightPortal\Utils\Avatar;
use LightPortal\Utils\Content;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasParamJoins;
use LightPortal\Utils\Traits\HasTranslationJoins;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

class PageArticle extends AbstractArticle implements PageArticleInterface
{
	use HasParamJoins;
	use HasTranslationJoins;

	protected array $selectedCategories = [];

	public function init(): void
	{
		$this->selectedCategories = Setting::get('lp_frontpage_categories', 'array', []);

		if (empty($this->selectedCategories) && Setting::isFrontpageMode('all_pages')) {
			$this->selectedCategories = [0];
		}

		$this->params = [
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
				'joins'   => &$this->joins,
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
		$this->setSorting($sortType);

		$this->prepareParams($start, $limit);

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->join(
				['cat' => 'lp_categories'],
				'cat.category_id = p.category_id',
				['cat_icon' => 'icon'],
				Select::JOIN_LEFT
			)
			->join(
				['mem' => 'members'],
				'p.author_id = mem.id_member',
				['author_name' => new Expression('COALESCE(mem.real_name, "")')],
				Select::JOIN_LEFT
			)
			->join(
				['com' => 'lp_comments'],
				'p.last_comment_id = com.id',
				[
					'comment_date'      => 'created_at',
					'comment_author_id' => 'author_id',
					'comment_message'   => 'message',
				],
				Select::JOIN_LEFT
			)
			->join(
				['com_mem' => 'members'],
				'com.author_id = com_mem.id_member',
				['comment_author_name' => new Expression('COALESCE(com_mem.real_name, "")')],
				Select::JOIN_LEFT
			);

		$this->addParamJoins($select, [
			'params' => [
				'allow_comments' => [
					'alias' => 'par',
					'columns' => [
						'num_comments' => new Expression(
							'CASE WHEN COALESCE(par.value, "0") != "0" THEN p.num_comments ELSE 0 END'
						)
					]
				]
			]
		]);

		$this->addTranslationJoins($select, ['fields' => ['title', 'content', 'description']]);
		$this->addTranslationJoins($select, [
			'primary' => 'cat.category_id',
			'entity'  => 'category',
			'fields'  => ['cat_title' => 'title'],
			'alias'   => 'cat_t',
		]);

		$columns = [
			Select::SQL_STAR,
			'date' => new Expression('GREATEST(p.created_at, p.updated_at)'),
		];

		$select->columns($columns);

		$this->applyColumns($select);
		$this->applyJoins($select);
		$this->applyWheres($select);

		$select
			->order($this->params['sort'])
			->limit($this->params['limit'])
			->offset($this->params['start']);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			Lang::censorText($row['title']);
			Lang::censorText($row['content']);
			Lang::censorText($row['description']);

			if ($row['title'] === '')
				continue;

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

			$this->events()->dispatch(PortalHook::frontPagesRow, ['articles' => &$articles, 'row' => $row]);

			$page = $articles[$row['page_id']];

			yield $row['page_id'] => Avatar::getWithItems([$page])[0] ?? [];
		}
	}

	public function getTotalCount(): int
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['count' => new Expression('COUNT(p.page_id)')])
			->join(
				['cat' => 'lp_categories'],
				'cat.category_id = p.category_id',
				[],
				Select::JOIN_LEFT
			);

		$this->applyJoins($select);
		$this->applyWheres($select);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	public function prepareTags(array &$pages): void
	{
		if ($pages === [])
			return;

		$select = $this->sql->select()
			->from(['tag' => 'lp_tags'])
			->join(
				['pt' => 'lp_page_tag'],
				'tag.tag_id = pt.tag_id',
				['page_id'],
				Select::JOIN_LEFT
			)
			->where(['pt.page_id' => array_keys($pages)])
			->where(['tag.status' => Status::ACTIVE->value])
			->order('title');

		$this->addTranslationJoins($select, ['primary' => 'pt.tag_id', 'entity' => 'tag']);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			if ($row['title'] === '')
				continue;

			Lang::censorText($row['title']);

			$pages[$row['page_id']]['tags'][] = [
				'slug' => $row['slug'],
				'icon' => Icon::parse($row['icon']),
				'href' => PortalSubAction::TAGS->url() . ';id=' . $row['tag_id'],
				'name' => $row['title'],
			];
		}
	}

	protected function applyBaseConditions(Select $select): void
	{
		$select->where([
			'p.status'          => $this->params['status'],
			'p.deleted_at'      => 0,
			'p.entry_type'      => $this->params['entry_type'],
			'p.created_at <= ?' => $this->params['current_time'],
		]);

		$select->where(new Expression('(cat.status = ? OR cat.category_id IS NULL)', $this->params['status']));

		if (! empty($this->selectedCategories)) {
			$select->where(['p.category_id' => $this->params['selected_categories']]);
		}

		$select->where(['p.permissions' => $this->params['permissions']]);
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
			'id'   => $authorId,
			'link' => Config::$scripturl . '?action=profile;u=' . $authorId,
			'name' => $authorName,
		];
	}

	private function getDate(array $row): int
	{
		if (str_contains($this->sorting, 'last_comment') && $row['comment_date']) {
			return $row['comment_date'];
		}

		if (str_contains($this->sorting, 'updated')) {
			return $row['date'];
		}

		return $row['created_at'];
	}

	private function getViewsData(array $row): array
	{
		return [
			'num'   => $row['num_views'],
			'title' => Lang::$txt['lp_views'],
			'after' => '',
		];
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => Setting::getCommentBlock() === 'default' ? $row['num_comments'] : 0,
			'title' => Lang::$txt['lp_comments'],
			'after' => '',
		];
	}

	private function isNew(array $row): bool
	{
		return User::$me->last_login < $row['date'] && $row['author_id'] !== User::$me->id;
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
			|| (User::$me->allowedTo('light_portal_manage_pages_own') && $row['author_id'] === User::$me->id);
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
