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

namespace LightPortal\Repositories;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Utils;
use LightPortal\Enums\Status;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Str;
use Exception;
use Laminas\Db\Sql\Predicate\Expression;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryRepository extends AbstractRepository implements CategoryRepositoryInterface, DataManagerInterface
{
	protected string $entity = 'category';

	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $filter = '',
		array $whereConditions = []
	): array
	{
		$select = $this->sql->select()
			->from(['c' => 'lp_categories'])
			->order($sort)
			->limit($limit)
			->offset($start);

		$this->addTranslationJoins($select, [
			'primary' => 'c.category_id',
			'entity'  => $this->entity,
			'fields'  => ['title', 'description'],
		]);

		if ($filter === 'list') {
			$select
				->where(['c.status = ?' => Status::ACTIVE->value])
				->where($this->getTranslationFilter(
					'c',
					'category_id',
					['title', 'description'],
					'category'
				));
		}

		if ($whereConditions) {
			$select->where($whereConditions);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			Lang::censorText($row['title']);
			Lang::censorText($row['description']);

			$items[$row['category_id']] = [
				'id'          => $row['category_id'],
				'slug'        => $row['slug'],
				'icon'        => Icon::parse($row['icon']),
				'priority'    => $row['priority'],
				'status'      => $row['status'],
				'title'       => Str::decodeHtmlEntities($row['title']),
				'description' => $row['description'],
			];
		}

		return $items;
	}

	public function getTotalCount(string $filter = '', array $whereConditions = []): int
	{
		$select = $this->sql->select('lp_categories')
			->columns(['count' => new Expression('COUNT(category_id)')]);

		if ($whereConditions) {
			$select->where($whereConditions);
		}

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	public function getData(int $item): array
	{
		if ($item === 0)
			return [];

		$select = $this->sql->select()
			->from(['c' => 'lp_categories'])
			->where(['c.category_id = ?' => $item]);

		$this->addTranslationJoins($select, [
			'primary' => 'c.category_id',
			'entity'  => $this->entity,
			'fields'  => ['title', 'description'],
		]);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			$data ??= [
				'id'          => $row['category_id'],
				'slug'        => $row['slug'],
				'icon'        => $row['icon'],
				'priority'    => $row['priority'],
				'status'      => $row['status'],
				'title'       => $row['title'],
				'description' => $row['description'],
			];
		}

		return $data ?? [];
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || $this->request()->hasNot(['save', 'save_exit'])) {
			return;
		}

		Security::checkSubmitOnce('check');

		empty($item)
			? $item = $this->addData(Utils::$context['lp_category'])
			: $this->updateData($item, Utils::$context['lp_category']);

		$this->cache()->flush();

		$this->session('lp')->free('active_categories');

		if ($this->request()->has('save_exit')) {
			$this->response()->redirect('action=admin;area=lp_categories;sa=main');
		}

		if ($this->request()->has('save')) {
			$this->response()->redirect('action=admin;area=lp_categories;sa=edit;id=' . $item);
		}
	}

	public function remove(mixed $items): void
	{
		$items = (array) $items;

		if ($items === [])
			return;

		$delete = $this->sql->delete('lp_categories');
		$delete->where->in('category_id', $items);
		$this->sql->execute($delete);

		$deleteTranslations = $this->sql->delete('lp_translations');
		$deleteTranslations->where->in('item_id', $items);
		$deleteTranslations->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteTranslations);

		$updatePages = $this->sql->update('lp_pages');
		$updatePages->set(['category_id' => 0]);
		$updatePages->where->in('category_id', $items);
		$this->sql->execute($updatePages);

		$this->cache()->flush();

		$this->session('lp')->free('active_categories');
	}

	public function updatePriority(array $categories = []): void
	{
		if ($categories === [])
			return;

		$caseConditions = [];
		$ids = [];
		foreach ($categories as $priority => $item) {
			$caseConditions[] = 'WHEN category_id = ? THEN ?';
			$ids[] = $item;
			$ids[] = $priority;
		}

		if ($caseConditions === [])
			return;

		$caseSql = implode(' ', $caseConditions);

		$update = $this->sql->update('lp_categories');
		$update->set(['priority' => new Expression('CASE ' . $caseSql . ' ELSE priority END', $ids)]);
		$update->where->in('category_id', array_values($categories));

		$this->sql->execute($update);

		$this->cache()->forget('all_categories');
	}

	private function addData(array $data): int
	{
		try {
			$this->transaction->begin();

			$insert = $this->sql->insert('lp_categories', 'category_id')
				->values([
					'slug'     => $data['slug'],
					'icon'     => $data['icon'],
					'priority' => $this->getPriority(),
					'status'   => $data['status'],
				]);

			$result = $this->sql->execute($insert);

			$item = (int) $result->getGeneratedValue('category_id');

			if (empty($item)) {
				$this->transaction->rollback();

				return 0;
			}

			$data['id'] = $item;

			$this->saveTranslations($data);

			$this->transaction->commit();

			return $item;
		} catch (Exception $e) {
			$this->transaction->rollback();

			ErrorHandler::fatal($e->getMessage(), false);

			return 0;
		}
	}

	private function updateData(int $item, array $data): void
	{
		try {
			$this->transaction->begin();

			$update = $this->sql->update('lp_categories')
				->set([
					'slug'     => $data['slug'],
					'icon'     => $data['icon'],
					'priority' => $data['priority'],
					'status'   => $data['status'],
				])
				->where(['category_id = ?' => $item]);

			$this->sql->execute($update);

			$this->saveTranslations($data, true);

			$this->transaction->commit();
		} catch (Exception $e) {
			$this->transaction->rollback();

			ErrorHandler::fatal($e->getMessage(), false);
		}
	}

	private function getPriority(): int
	{
		$select = $this->sql->select('lp_categories')
			->columns(['priority' => new Expression('MAX(priority) + 1')]);

		$result = $this->sql->execute($select)->current();

		return $result['priority'] ?? 0;
	}
}
