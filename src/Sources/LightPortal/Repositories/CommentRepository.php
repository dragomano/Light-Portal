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

namespace LightPortal\Repositories;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Laminas\Db\Sql\Predicate\Expression;
use LightPortal\Enums\NotifyType;
use LightPortal\Utils\Avatar;
use LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

final class CommentRepository extends AbstractRepository implements CommentRepositoryInterface
{
	protected string $entity = 'comment';

	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $filter = '',
		array $whereConditions = []
	): array
	{
		return $this->getByPageId();
	}

	public function getTotalCount(string $filter = '', array $whereConditions = []): int
	{
		$select = $this->sql->select()
			->from(['com' => 'lp_comments'])
			->columns(['id']);

		$this->addTranslationJoins($select);

		if ($whereConditions) {
			$select->where($whereConditions);
		}

		$countSelect = $this->sql->select()
			->from(['sub' => $select])
			->columns(['count' => new Expression('COUNT(*)')]);

		$result = $this->sql->execute($countSelect)->current();

		return (int) $result['count'];
	}

	public function getData(int $item): array
	{
		if ($item === 0) {
			return [];
		}

		$select = $this->sql->select()
			->from(['com' => 'lp_comments'])
			->join(
				['mem' => 'members'],
				'com.author_id = mem.id_member',
				['author_name' => 'real_name']
			)
			->where(['com.id = ?' => $item]);

		$this->addParamJoins($select, [
			'primary' => 'com.id',
			'entity'  => $this->entity,
		]);

		$this->addTranslationJoins($select, [
			'primary' => 'com.id',
			'entity'  => $this->entity,
			'fields'  => ['content'],
		]);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			Lang::censorText($row['content']);

			$data ??= [
				'id'         => $row['id'],
				'page_id'    => $row['page_id'],
				'parent_id'  => $row['parent_id'],
				'message'    => htmlspecialchars_decode($row['content']),
				'created_at' => $row['created_at'],
				'updated_at' => $row['updated_at'],
				'can_edit'   => $this->isCanEdit($row['created_at']),
				'poster'     => [
					'id'     => $row['author_id'],
					'name'   => $row['author_name'],
					'avatar' => Avatar::get($row['author_id']),
				],
			];

			if (isset($row['name'])) {
				$data['params'][$row['name']] = $row['value'];
			}
		}

		return $data ?? [];
	}

	public function getByPageId(int $id = 0): array
	{
		$sorts = [
			'created_at',
			'created_at DESC',
			'updated_at',
			'updated_at DESC',
		];

		$select = $this->sql->select()
			->from(['com' => 'lp_comments'])
			->join(
				['mem' => 'members'],
				'com.author_id = mem.id_member',
				['author_name' => 'real_name']
			)
			->order($sorts[Config::$modSettings['lp_comment_sorting'] ?? 0]);

		if ($id > 0) {
			$select->where(['com.page_id = ?' => $id]);
		}

		$this->addParamJoins($select, [
			'primary' => 'com.id',
			'entity'  => $this->entity,
		]);

		$this->addTranslationJoins($select, [
			'primary' => 'com.id',
			'entity'  => $this->entity,
			'fields'  => ['content'],
		]);

		$select->where($this->getTranslationFilter('com', 'id', ['content'], 'comment'));

		$result = $this->sql->execute($select);

		$comments = [];
		foreach ($result as $row) {
			Lang::censorText($row['content']);

			$comments[$row['id']] = [
				'id'         => $row['id'],
				'page_id'    => $row['page_id'],
				'parent_id'  => $row['parent_id'],
				'message'    => htmlspecialchars_decode($row['content']),
				'created_at' => $row['created_at'],
				'updated_at' => $row['updated_at'],
				'can_edit'   => $this->isCanEdit($row['created_at']),
				'poster'     => [
					'id'   => $row['author_id'],
					'name' => $row['author_name'],
				],
			];

			if (isset($row['name'])) {
				$comments[$row['id']]['params'][$row['name']] = $row['value'];
			}
		}

		return Avatar::getWithItems($comments, 'poster');
	}

	public function save(array $data): int
	{
		$insert = $this->sql->insert('lp_comments', 'id')
			->values([
				'parent_id'  => $data['parent_id'],
				'page_id'    => $data['page_id'],
				'author_id'  => $data['author_id'],
				'created_at' => $data['created_at'],
			]);

		$result = $this->sql->execute($insert);

		$item = (int) $result->getGeneratedValue();

		if ($item && ! empty($data['message'])) {
			$data['content'] = $data['message'];
			$data['id'] = $item;

			$this->saveTranslations($data);
		}

		return $item;
	}

	public function update(array $data): void
	{
		$update = $this->sql->update('lp_comments')
			->set(['updated_at' => time()])
			->where([
				'id = ?'        => $data['id'],
				'author_id = ?' => $data['user']
			]);

		$this->sql->execute($update);

		if (! empty($data['message'])) {
			$data['content'] = $data['message'];

			$this->saveTranslations($data, true);
		}
	}

	public function remove(mixed $items, bool $withResponse = false): void
	{
		$items = (array) $items;

		if ($items === [])
			return;

		$select = $this->sql->select('lp_comments')->columns(['id', 'page_id']);
		$select->where->in('id', $items)->or->in('parent_id', $items);
		$result = $this->sql->execute($select);

		$allItems = $pageIds = [];
		foreach ($result as $row) {
			$allItems[] = $row['id'];
			$pageIds[]  = $row['page_id'];
		}

		if ($allItems === [])
			return;

		$pageIds = array_unique($pageIds);

		$this->executeInTransaction(function() use ($allItems, $pageIds, $withResponse) {
			$deleteComments = $this->sql->delete('lp_comments');
			$deleteComments->where->in('id', $allItems);
			$this->sql->execute($deleteComments);

			foreach ($pageIds as $pageId) {
				$update = $this->sql->update('lp_pages')
					->set([
						'num_comments' => new Expression(
							'CASE WHEN num_comments < ? THEN 0 ELSE num_comments - ? END',
							[count($allItems), count($allItems)]
						)
					])
					->where(['page_id = ?' => $pageId]);
				$this->sql->execute($update);

				$subSelect = $this->sql->select()
					->from(['com' => 'lp_comments'])
					->columns([new Expression('COALESCE(MAX(com.id), 0)')])
					->where(['com.page_id = ?' => $pageId]);

				$updateLast = $this->sql->update('lp_pages')
					->set(['last_comment_id' => $subSelect])
					->where(['page_id = ?' => $pageId]);
				$this->sql->execute($updateLast);
			}

			$this->deleteRelatedData($allItems);

			$deleteAlerts = $this->sql->delete('user_alerts');
			$deleteAlerts->where([
				'content_type = ?' => NotifyType::NEW_COMMENT->name(),
			]);
			$deleteAlerts->where->in('content_id', $allItems);
			$this->sql->execute($deleteAlerts);

			$withResponse && $this->response()->exit(['success' => true, 'items' => $allItems]);
		});
	}

	public function updateLastCommentId(int $item, int $pageId): void
	{
		$update = $this->sql->update('lp_pages')
			->set([
				'num_comments'    => new Expression('num_comments + 1'),
				'last_comment_id' => $item,
			])
			->where(['page_id = ?' => $pageId]);

		$this->sql->execute($update);
	}

	private function isCanEdit(int $date): bool
	{
		$timeToChange = Setting::get('lp_time_to_change_comments', 'int', 0);

		if (empty($timeToChange))
			return false;

		return time() - $date <= $timeToChange * 60;
	}
}
