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

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Enums\PortalHook;
use LightPortal\Utils\Traits\HasParamJoins;
use LightPortal\Utils\Traits\HasTranslationJoins;

if (! defined('SMF'))
	die('No direct access...');

class PageArticleQuery extends AbstractArticleQuery
{
	use HasParamJoins;
	use HasTranslationJoins;

	protected function getOrders(): array
	{
		return [
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
	}

	protected function getEventHook(): PortalHook
	{
		return PortalHook::frontPages;
	}

	protected function buildDataSelect(): Select
	{
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
				['author_name' => new Expression("COALESCE(mem.real_name, '')")],
				Select::JOIN_LEFT
			)
			->join(
				['com' => 'lp_comments'],
				'p.last_comment_id = com.id',
				[
					'comment_date'      => 'created_at',
					'comment_author_id' => 'author_id',
				],
				Select::JOIN_LEFT
			)
			->join(
				['com_mem' => 'members'],
				'com.author_id = com_mem.id_member',
				['comment_author_name' => new Expression("COALESCE(com_mem.real_name, '')")],
				Select::JOIN_LEFT
			);

		$this->addParamJoins($select, [
			'params' => [
				'allow_comments' => [
					'alias' => 'par',
					'columns' => [
						'num_comments' => new Expression(
							"CASE WHEN COALESCE(par.value, '0') != '0' THEN p.num_comments ELSE 0 END"
						)
					]
				]
			]
		]);

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

		return $select;
	}

	protected function buildCountSelect(): Select
	{
		return $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['page_id'])
			->join(
				['cat' => 'lp_categories'],
				'cat.category_id = p.category_id',
				[],
				Select::JOIN_LEFT
			);
	}

	protected function applyBaseConditions(Select $select): void
	{
		$this->addTranslationJoins($select, ['fields' => ['title', 'content', 'description']]);

		$select->where([
			'p.status'          => $this->params['status'],
			'p.deleted_at'      => $this->params['deleted_at'],
			'p.entry_type'      => $this->params['entry_type'],
			'p.created_at <= ?' => $this->params['current_time'],
		]);

		if (! empty($this->params['selected_categories'])) {
			$select->where(['p.category_id' => $this->params['selected_categories']]);
		}

		$select->where(['p.permissions' => $this->params['permissions']]);
		$select->where(new Expression('(cat.status = ? OR p.category_id = 0)', $this->params['status']));
		$select->where(new Expression("COALESCE(NULLIF(t.title, ''), tf.title, '') <> ''"));
	}
}
