<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Articles\Queries;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Enums\PortalHook;
use LightPortal\Utils\ForumPermissions;

if (! defined('SMF'))
	die('No direct access...');

class BoardArticleQuery extends AbstractArticleQuery
{
	public function getRawData(): iterable
	{
		if (empty($this->params['selected_boards'])) {
			return [];
		}

		return parent::getRawData();
	}

	public function getTotalCount(): int
	{
		if (empty($this->params['selected_boards'])) {
			return 0;
		}

		return parent::getTotalCount();
	}

	protected function getOrders(): array
	{
		return [
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
	}

	protected function getEventHook(): PortalHook
	{
		return PortalHook::frontBoards;
	}

	protected function buildDataSelect(): Select
	{
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
				]
			)
			->group('b.id_board');

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
			$columns['is_read']  = new Expression('"1"');
			$columns['new_from'] = new Expression('"0"');
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

		return $select;
	}

	protected function buildCountSelect(): Select
	{
		return $this->sql->select()
			->from(['b' => 'boards'])
			->columns(['id_board'])
			->join(
				['c' => 'categories'],
				'b.id_cat = c.id_cat',
				[]
			)
			->join(
				['m' => 'messages'],
				'b.id_last_msg = m.id_msg',
				[]
			);
	}

	protected function applyBaseConditions(Select $select): void
	{
		if (! empty($this->params['selected_boards'])) {
			$select->where(['b.id_board' => $this->params['selected_boards']]);
		}

		if (! empty($this->params['recycle_board'])) {
			$select->where(['b.id_board != ?' => $this->params['recycle_board']]);
		}

		if (ForumPermissions::shouldApplyBoardPermissionCheck()) {
			$select->where(ForumPermissions::canSeeBoard('b'));
		}
	}
}
