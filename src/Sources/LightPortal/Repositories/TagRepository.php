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

final class TagRepository extends AbstractRepository implements TagRepositoryInterface
{
	protected string $entity = 'tag';

	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $filter = '',
		array $whereConditions = []
	): array
	{
		$select = $this->sql->select()
			->from(['tag' => 'lp_tags'])
			->order($sort)
			->limit($limit)
			->offset($start);

		$this->addTranslationJoins($select, [
			'primary' => 'tag.tag_id',
			'entity'  => $this->entity,
		]);

		if ($filter === 'list') {
			$select
				->where(['tag.status = ?' => Status::ACTIVE->value])
				->where($this->getTranslationFilter('tag', 'tag_id', ['title'], 'tag'));
		}

		if ($whereConditions) {
			$select->where($whereConditions);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			Lang::censorText($row['title']);

			$items[$row['tag_id']] = [
				'id'     => $row['tag_id'],
				'slug'   => $row['slug'],
				'icon'   => Icon::parse($row['icon']),
				'status' => $row['status'],
				'title'  => Str::decodeHtmlEntities($row['title']),
			];
		}

		return $items;
	}

	public function getTotalCount(string $filter = '', array $whereConditions = []): int
	{
		$select = $this->sql->select('lp_tags')
			->columns(['count' => new Expression('COUNT(tag_id)')]);

		if ($whereConditions) {
			$select->where($whereConditions);
		}

		$result = $this->sql->execute($select)->current();

		return (int) $result['count'];
	}

	public function getData(int $item): array
	{
		if ($item === 0) {
			return [];
		}

		$select = $this->sql->select()
			->from(['tag' => 'lp_tags'])
			->where(['tag.tag_id = ?' => $item]);

		$this->addTranslationJoins($select, [
			'primary' => 'tag.tag_id',
			'entity'  => $this->entity,
		]);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			$data ??= [
				'id'     => $row['tag_id'],
				'slug'   => $row['slug'],
				'icon'   => $row['icon'],
				'status' => $row['status'],
				'title'  => $row['title'],
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
			? $item = $this->addData(Utils::$context['lp_tag'])
			: $this->updateData($item, Utils::$context['lp_tag']);

		$this->cache()->flush();

		$this->session('lp')->free('active_tags');

		if ($this->request()->has('save_exit')) {
			$this->response()->redirect('action=admin;area=lp_tags;sa=main');
		}

		if ($this->request()->has('save')) {
			$this->response()->redirect('action=admin;area=lp_tags;sa=edit;id=' . $item);
		}
	}

	public function remove(mixed $items): void
	{
		$items = (array) $items;

		if ($items === [])
			return;

		$delete = $this->sql->delete('lp_tags');
		$delete->where->in('tag_id', $items);
		$this->sql->execute($delete);

		$deleteTranslations = $this->sql->delete('lp_translations');
		$deleteTranslations->where->in('item_id', $items);
		$deleteTranslations->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteTranslations);

		$deletePageTag = $this->sql->delete('lp_page_tag');
		$deletePageTag->where->in('tag_id', $items);
		$this->sql->execute($deletePageTag);

		$this->cache()->flush();

		$this->session('lp')->free('active_tags');
	}

	private function addData(array $data): int
	{
		try {
			$this->transaction->begin();

			$insert = $this->sql->insert('lp_tags', 'tag_id')
				->values([
					'slug'   => $data['slug'],
					'icon'   => $data['icon'],
					'status' => $data['status'],
				]);

			$result = $this->sql->execute($insert);

			$item = (int) $result->getGeneratedValue('tag_id');

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

			$update = $this->sql->update('lp_tags')
				->set([
					'slug'   => $data['slug'],
					'icon'   => $data['icon'],
					'status' => $data['status'],
				])
				->where(['tag_id = ?' => $item]);

			$this->sql->execute($update);

			$this->saveTranslations($data, true);

			$this->transaction->commit();
		} catch (Exception $e) {
			$this->transaction->rollback();

			ErrorHandler::fatal($e->getMessage(), false);
		}
	}
}
