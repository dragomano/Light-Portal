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
use Bugo\Compat\Lang;
use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Utils\ForumPermissions;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

if (! defined('SMF'))
	die('No direct access...');

class BoardArticle extends AbstractArticle
{
	protected array $selectedBoards = [];

	public function init(): void
	{
		$this->selectedBoards = Setting::get('lp_frontpage_boards', 'array', []);

		$this->params = [
			'current_member'  => User::$me->id,
			'selected_boards' => $this->selectedBoards,
		];

		$this->orders = [
			'created;desc'      => 'm.poster_time DESC',
			'created'           => 'm.poster_time',
			'updated;desc'      => 'GREATEST(m.poster_time, m.modified_time) DESC',
			'updated'           => 'GREATEST(m.poster_time, m.modified_time)',
			'last_comment;desc' => 'b.id_last_msg DESC',
			'last_comment'      => 'b.id_last_msg',
			'title;desc'        => 'b.name DESC',
			'title'             => 'b.name',
			'num_replies;desc'  => 'b.num_posts DESC',
			'num_replies'       => 'b.num_posts',
		];

		$this->events()->dispatch(
			PortalHook::frontBoards,
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
			'last_comment;desc' => Lang::$txt['lp_sort_by_last_reply_desc'],
			'last_comment'      => Lang::$txt['lp_sort_by_last_reply'],
			'title;desc'        => Lang::$txt['lp_sort_by_title_desc'],
			'title'             => Lang::$txt['lp_sort_by_title'],
			'num_replies;desc'  => Lang::$txt['lp_sort_by_num_replies_desc'],
			'num_replies'       => Lang::$txt['lp_sort_by_num_replies'],
		];
	}

	public function getData(int $start, int $limit, string $sortType = null): iterable
	{
		$this->setSorting($sortType);

		if (empty($this->selectedBoards))
			return;

		$this->prepareParams($start, $limit);

		$select = $this->sql->select()
			->from(['b' => 'boards'])
			->join(
				['c' => 'categories'],
				'b.id_cat = c.id_cat',
				['cat_name' => 'name']
			)
			->join(
				['m' => 'messages'],
				'b.id_last_msg = m.id_msg',
				[
					'id_msg', 'id_topic', 'poster_time', 'modified_time',
					'last_updated' => new Expression('GREATEST(m.poster_time, m.modified_time)')
				],
				Select::JOIN_LEFT
			);

		$columns = [
			'id_board', 'name', 'description', 'redirect', 'id_last_msg', 'num_posts',
			'is_redirect' => new Expression("CASE WHEN b.redirect != '' THEN 1 ELSE 0 END"),
		];

		if (! User::$me->is_guest) {
			$select->join(
				['lb' => 'log_boards'],
				new Expression(
					'b.id_board = lb.id_board AND lb.id_member = ?',
					[$this->params['current_member']]
				),
				[],
				Select::JOIN_LEFT
			);
			$columns['is_read']  = new Expression('CASE WHEN COALESCE(lb.id_msg, 0) >= b.id_last_msg THEN 1 ELSE 0 END');
			$columns['new_from'] = new Expression('COALESCE(lb.id_msg, -1) + 1');
		} else {
			$columns['is_read']  = new Expression('1');
			$columns['new_from'] = new Expression('0');
		}

		if (! empty(Config::$modSettings['lp_show_images_in_articles'])) {
			$select->join(
				['a' => 'attachments'],
				new Expression(
					'b.id_last_msg = a.id_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0'
				),
				[],
				Select::JOIN_LEFT
			);
			$columns['attach_id'] = new Expression('COALESCE(a.id_attach, 0)');
		}

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
			$row['description'] = BBCodeParser::load()->parse(
				$row['description'], false, '', Utils::$context['description_allowed_tags']
			);

			$board = [
				'id'           => $row['id_board'],
				'date'         => $this->getDate($row),
				'last_comment' => $row['id_last_msg'],
				'title'        => $this->getTitle($row),
				'link'         => $this->getLink($row),
				'is_new'       => empty($row['is_read']),
				'replies'      => $this->getRepliesData($row),
				'image'        => $this->getImage($row),
				'can_edit'     => $this->canEdit(),
				'edit_link'    => $this->getEditLink($row),
				'category'     => $this->getCategory($row),
				'is_redirect'  => $row['is_redirect'],
			];

			$this->prepareTeaser($board, $row);

			$articles = [$row['id_board'] => $board];

			$this->events()->dispatch(PortalHook::frontBoardsRow, ['articles' => &$articles, 'row' => $row]);

			$board = $articles[$row['id_board']];

			yield $row['id_board'] => $board;
		}
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedBoards))
			return 0;

		$select = $this->sql->select()
			->from(['b' => 'boards'])
			->join(
				['c' => 'categories'],
				'b.id_cat = c.id_cat',
				[]
			)
			->columns(['count' => new Expression('COUNT(b.id_board)')]);

		$this->applyJoins($select);
		$this->applyWheres($select);

		$result = $this->sql->execute($select)->current();

		return ($result['count'] ?? 0);
	}

	protected function applyBaseConditions(Select $select): void
	{
		if (! empty($this->selectedBoards)) {
			$select->where(['b.id_board' => $this->selectedBoards]);
		}

		if (ForumPermissions::shouldApplyBoardPermissionCheck()) {
			$select->where(ForumPermissions::canSeeBoard('b'));
		}
	}

	private function getDate(array $row): int
	{
		return str_contains($this->sorting, 'updated') && $row['last_updated']
			? $row['last_updated']
			: $row['poster_time'];
	}

	private function getTitle(array $row): string
	{
		return BBCodeParser::load()->parse($row['name'], false, '', Utils::$context['description_allowed_tags']);
	}

	private function getLink(array $row): string
	{
		return $row['is_redirect']
			? $row['redirect'] . '" rel="nofollow noopener'
			: (Config::$scripturl . '?board=' . $row['id_board'] . '.0');
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => $row['num_posts'],
			'title' => Lang::$txt['lp_replies'],
			'after' => '',
		];
	}

	private function getImage(array $row): string
	{
		if (empty(Config::$modSettings['lp_show_images_in_articles']))
			return '';

		$image = Str::getImageFromText($row['description']);

		if ($row['attach_id'] && empty($image)) {
			$image = Config::$scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach='
				. $row['attach_id'] . ';image';
		}

		if ($row['is_redirect'] && empty($image)) {
			$image = 'https://mini.s-shot.ru/300x200/JPEG/300/Z100/?' . urlencode(trim($row['redirect']));
		}

		return $image;
	}

	private function canEdit(): bool
	{
		return User::$me->is_admin || User::$me->allowedTo('manage_boards');
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=admin;area=manageboards;sa=board;boardid=' . $row['id_board'];
	}

	private function getCategory(array $row): string
	{
		return BBCodeParser::load()->parse($row['cat_name'], false, '', Utils::$context['description_allowed_tags']);
	}

	private function prepareTeaser(array &$board, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']) || empty($row['description']))
			return;

		$board['teaser'] = Str::getTeaser($row['description']);
	}
}
