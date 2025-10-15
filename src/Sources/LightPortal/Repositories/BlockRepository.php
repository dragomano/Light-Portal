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

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Msg;
use Bugo\Compat\Security;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Str;
use Exception;
use Laminas\Db\Sql\Predicate\Expression;

use function Bugo\LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

final class BlockRepository extends AbstractRepository implements BlockRepositoryInterface, DataManagerInterface
{
	use HasEvents;

	protected string $entity = 'block';

	public function getAll(int $start, int $limit, string $sort, string $filter = ''): array
	{
		$grouped = $filter !== 'list';

		$columns = ['title', 'content'];

		if ($grouped) {
			$columns[] = 'description';
		}

		$select = $this->sql->select()->from(['b' => 'lp_blocks']);

		$this->addTranslationJoins($select, [
			'primary' => 'b.block_id',
			'entity'  => $this->entity,
			'fields'  => $columns,
		]);

		if ($filter === 'list') {
			$select->where(['status = ?' => Status::ACTIVE->value]);

			$this->addParamJoins($select, [
				'primary' => 'b.block_id',
				'entity'  => $this->entity,
			]);
		}

		if ($limit > 0) {
			$select->limit($limit)->offset($start);
		}

		$select->order($sort ?: 'placement DESC, priority');

		$result = $this->sql->execute($select);

		$currentBlocks = [];
		foreach ($result as $row) {
			Lang::censorText($row['title']);

			if ($grouped) {
				Lang::censorText($row['description']);
			}

			if ($grouped) {
				$currentBlocks[$row['placement']][$row['block_id']] ??= [
					'icon'        => Icon::parse($row['icon']),
					'type'        => $row['type'],
					'priority'    => $row['priority'],
					'permissions' => $row['permissions'],
					'status'      => $row['status'],
					'areas'       => str_replace(',', PHP_EOL, $row['areas']),
					'title'       => $row['title'],
					'description' => $row['description'],
				];

				if (! empty($row['name'])) {
					$currentBlocks[$row['placement']][$row['block_id']]['parameters'][$row['name']] = $row['value'];
				}

				$this->prepareMissingBlockTypes($row['type']);
			} else {
				$currentBlocks[$row['block_id']] ??= [
					'id'            => $row['block_id'],
					'icon'          => $row['icon'],
					'type'          => $row['type'],
					'placement'     => $row['placement'],
					'priority'      => $row['priority'],
					'permissions'   => $row['permissions'],
					'areas'         => explode(',', $row['areas']),
					'title_class'   => $row['title_class'],
					'content_class' => $row['content_class'],
					'title'         => $row['title'],
					'content'       => $row['content'],
				];

				if (! empty($row['name'])) {
					$currentBlocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
				}
			}
		}

		if ($grouped) {
			return array_merge(array_flip(array_keys(Utils::$context['lp_block_placements'])), $currentBlocks);
		}

		return $currentBlocks;
	}

	public function getData(int $item): array
	{
		if ($item === 0)
			return [];

		$select = $this->sql->select()
			->from(['b' => 'lp_blocks'])
			->where(['b.block_id = ?' => $item]);

		$this->addParamJoins($select, [
			'primary' => 'b.block_id',
			'entity'  => $this->entity,
		]);

		$this->addTranslationJoins($select, [
			'primary' => 'b.block_id',
			'entity'  => $this->entity,
			'fields'  => ['title', 'content', 'description'],
		]);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			if ($row['type'] === ContentType::BBC->name()) {
				$row['content'] = Msg::un_preparsecode($row['content'] ?? '');
			}

			$data ??= [
				'id'            => $row['block_id'],
				'icon'          => $row['icon'],
				'type'          => $row['type'],
				'placement'     => $row['placement'],
				'priority'      => $row['priority'],
				'permissions'   => $row['permissions'],
				'status'        => $row['status'],
				'areas'         => $row['areas'],
				'title_class'   => $row['title_class'],
				'content_class' => $row['content_class'],
				'title'         => $row['title'] ?? '',
				'content'       => $row['content'] ?? '',
				'description'   => $row['description'] ?? '',
			];

			if (! empty($row['name'])) {
				$data['options'][$row['name']] = $row['value'];
			}

			$this->prepareMissingBlockTypes($row['type']);
		}

		return $data ?? [];
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || $this->request()->hasNot(['save', 'save_exit', 'clone'])) {
			Utils::$context['lp_block']['id'] = 0;
			return;
		}

		Security::checkSubmitOnce('check');

		$this->prepareBbcContent(Utils::$context['lp_block']);

		empty($item)
			? $item = $this->addData(Utils::$context['lp_block'])
			: $this->updateData($item, Utils::$context['lp_block']);

		if ($this->request()->isNotEmpty('clone')) {
			Utils::$context['lp_block']['id'] = $item;
			return;
		}

		$this->cache()->flush();

		$this->session('lp')->free('active_blocks');

		if ($this->request()->has('save_exit')) {
			$this->response()->redirect('action=admin;area=lp_blocks;sa=main');
		}

		if ($this->request()->has('save')) {
			$this->response()->redirect('action=admin;area=lp_blocks;sa=edit;id=' . $item);
		}
	}

	public function remove(array $items): void
	{
		if ($items === [])
			return;

		$this->events()->dispatch(PortalHook::onBlockRemoving, ['items' => $items]);

		$delete = $this->sql->delete('lp_blocks');
		$delete->where->in('block_id', $items);
		$this->sql->execute($delete);

		$deleteTranslations = $this->sql->delete('lp_translations');
		$deleteTranslations->where->in('item_id', $items);
		$deleteTranslations->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteTranslations);

		$deleteParams = $this->sql->delete('lp_params');
		$deleteParams->where->in('item_id', $items);
		$deleteParams->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteParams);

		$this->session('lp')->free('active_blocks');
	}

	public function updatePriority(array $blocks = [], string $placement = ''): void
	{
		if ($blocks === [])
			return;

		$caseConditions = [];
		$ids = [];
		foreach ($blocks as $priority => $item) {
			$caseConditions[] = 'WHEN block_id = ? THEN ?';
			$ids[] = $item;
			$ids[] = $priority;
		}

		$caseSql = implode(' ', $caseConditions);

		$update = $this->sql->update('lp_blocks');
		$update->set(['priority' => new Expression('CASE ' . $caseSql . ' ELSE priority END', $ids)]);
		$update->where->in('block_id', array_values($blocks));
		$this->sql->execute($update);

		if ($placement) {
			$updatePlacement = $this->sql->update('lp_blocks');
			$updatePlacement->set(['placement' => $placement]);
			$updatePlacement->where->in('block_id', array_values($blocks));
			$this->sql->execute($updatePlacement);
		}
	}

	private function addData(array $data): int
	{
		try {
			$this->transaction->begin();

			$insert = $this->sql->insert('lp_blocks')
				->values([
					'icon'          => $data['icon'],
					'type'          => $data['type'],
					'placement'     => $data['placement'],
					'priority'      => $this->getPriority($data['placement']),
					'permissions'   => $data['permissions'],
					'status'        => $data['status'],
					'areas'         => $data['areas'],
					'title_class'   => $data['title_class'],
					'content_class' => $data['content_class'],
				]);

			$result = $this->sql->execute($insert);

			$item = (int) $result->getGeneratedValue();

			if (empty($item)) {
				$this->transaction->rollback();

				return 0;
			}

			$this->events()->dispatch(PortalHook::onBlockSaving, ['item' => $item]);

			$this->saveTranslations($item);
			$this->saveOptions($item);

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

			$update = $this->sql->update('lp_blocks')
				->set([
					'icon'          => $data['icon'],
					'type'          => $data['type'],
					'placement'     => $data['placement'],
					'permissions'   => $data['permissions'],
					'areas'         => $data['areas'],
					'title_class'   => $data['title_class'],
					'content_class' => $data['content_class'],
				])
				->where(['block_id = ?' => $item]);

			$this->sql->execute($update);

			$this->events()->dispatch(PortalHook::onBlockSaving, ['item' => $item]);

			$this->saveTranslations($item, true);
			$this->saveOptions($item, true);

			$this->transaction->commit();
		} catch (Exception $e) {
			$this->transaction->rollback();

			ErrorHandler::fatal($e->getMessage(), false);
		}
	}

	private function prepareMissingBlockTypes(string $type): void
	{
		if (isset(Lang::$txt['lp_' . $type]['title']))
			return;

		$plugin = Str::getCamelName($type);

		$message = in_array($plugin, app(PluginList::class)())
			? Lang::$txt['lp_addon_not_activated']
			: Lang::$txt['lp_addon_not_installed'];

		Utils::$context['lp_missing_block_types'][$type] = Str::html('span')->class('error')
			->setText(sprintf($message, $plugin));
	}

	private function getPriority(string $placement): int
	{
		if (empty($placement)) {
			return 0;
		}

		$select = $this->sql->select('lp_blocks')
			->columns(['priority' => new Expression('MAX(priority) + 1')])
			->where(['placement = ?' => $placement]);

		$result = $this->sql->execute($select)->current();

		return $result['priority'] ?? 1;
	}
}
