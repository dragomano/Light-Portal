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

namespace LightPortal\Articles\Queries;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Enums\PortalHook;
use LightPortal\Utils\ForumPermissions;

if (! defined('SMF'))
	die('No direct access...');

class TopicArticleQuery extends AbstractArticleQuery
{
	public function init(array $params): void
	{
		parent::init($params);

		$this->orders = [
			'created;desc'      => 'mf.poster_time DESC',
			'created'           => 'mf.poster_time',
			'updated;desc'      => 'GREATEST(mf.poster_time, mf.modified_time) DESC',
			'updated'           => 'GREATEST(mf.poster_time, mf.modified_time)',
			'last_comment;desc' => 't.id_last_msg DESC',
			'last_comment'      => 't.id_last_msg',
			'title;desc'        => 'mf.subject DESC',
			'title'             => 'mf.subject',
			'author_name;desc'  => 'poster_name DESC',
			'author_name'       => 'poster_name',
			'num_views;desc'    => 't.num_views DESC',
			'num_views'         => 't.num_views',
			'num_replies;desc'  => 't.num_replies DESC',
			'num_replies'       => 't.num_replies',
		];

		$this->events->dispatch(
			PortalHook::frontTopics,
			[
				'columns' => &$this->columns,
				'joins'   => &$this->joins,
				'params'  => &$this->params,
				'wheres'  => &$this->wheres,
				'orders'  => &$this->orders,
			]
		);
	}

	protected function buildDataSelect(): Select
	{
		$select = $this->sql->select()
			->from(['t' => 'topics'])
			->join(
				['ml' => 'messages'],
				't.id_last_msg = ml.id_msg',
				[
					'id_msg', 'last_poster_id' => 'id_member', 'last_poster_name' => 'poster_name',
					'last_body' => 'body', 'last_msg_time' => 'poster_time', 'id_msg_modified'
				]
			)
			->join(
				['mf' => 'messages'],
				't.id_first_msg = mf.id_msg',
				[
					'subject', 'body', 'smileys_enabled', 'poster_time', 'id_member',
					'date' => new Expression('GREATEST(mf.poster_time, mf.modified_time)')
				]
			)
			->join(
				['b' => 'boards'],
				't.id_board = b.id_board',
				['name']
			)
			->join(
				['mem' => 'members'],
				'mf.id_member = mem.id_member',
				['poster_name' => new Expression('COALESCE(mem.real_name, mf.poster_name)')],
				Select::JOIN_LEFT
			);

		$columns = [
			'id_topic', 'id_board', 'num_views', 'num_replies', 'is_sticky', 'id_first_msg', 'id_member_started',
		];

		if (! User::$me->is_guest) {
			$select->join(
				['lt' => 'log_topics'],
				new Expression(
					't.id_topic = lt.id_topic AND lt.id_member = ?',
					[$this->params['current_member']]
				),
				[],
				Select::JOIN_LEFT
			)
				->join(
					['lmr' => 'log_mark_read'],
					new Expression(
						't.id_board = lmr.id_board AND lmr.id_member = ?',
						[$this->params['current_member']]
					),
					[],
					Select::JOIN_LEFT
				);
			$columns['new_from'] = new Expression('COALESCE(lt.id_msg, lmr.id_msg, -1) + 1');
		} else {
			$columns['new_from'] = new Expression('"0"');
		}

		if (! empty(Config::$modSettings['lp_show_images_in_articles'])) {
			$select->join(
				['a' => 'attachments'],
				new Expression(
					'a.id_msg = t.id_first_msg AND a.width <> 0 AND a.height <> 0 AND a.approved = ? AND a.attachment_type = ?',
					[$this->params['is_approved'], $this->params['attachment_type']]
				),
				[],
				Select::JOIN_LEFT
			);
			$columns['id_attach'] = new Expression('MIN(a.id_attach)');
			$select->group('t.id_topic');
		}

		$select->columns($columns);

		return $select;
	}

	protected function buildCountSelect(): Select
	{
		return $this->sql->select()
			->from(['t' => 'topics'])
			->columns(['count' => new Expression('COUNT(t.id_topic)')])
			->join(
				['b' => 'boards'],
				't.id_board = b.id_board',
				[]
			);
	}


	protected function applyBaseConditions(Select $select): void
	{
		$select->where([
			't.id_poll'           => $this->params['id_poll'],
			't.approved'          => $this->params['is_approved'],
			't.id_redirect_topic' => $this->params['id_redirect_topic'],
		]);

		if (! empty($this->params['selected_boards'])) {
			$select->where(['t.id_board' => $this->params['selected_boards']]);
		}

		if (! empty($this->params['recycle_board'])) {
			$select->where(['t.id_board != ?' => $this->params['recycle_board']]);
		}

		if (ForumPermissions::shouldApplyBoardPermissionCheck()) {
			$select->where(ForumPermissions::canSeeBoard());
		}
	}
}
