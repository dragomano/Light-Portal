<?php declare(strict_types=1);

/**
 * BlockRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\LightPortal\Utils\{Config, Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class BlockRepository extends AbstractRepository
{
	protected string $entity = 'block';

	public function getAll(): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT b.block_id, b.icon, b.type, b.note, b.placement, b.priority, b.permissions, b.status, b.areas, bt.lang, bt.title
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
			ORDER BY b.placement DESC, b.priority',
			[]
		);

		$currentBlocks = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$currentBlocks[$row['placement']][$row['block_id']] ??= [
				'icon'        => $this->getIcon($row['icon']),
				'type'        => $row['type'],
				'note'        => $row['note'],
				'priority'    => $row['priority'],
				'permissions' => $row['permissions'],
				'status'      => $row['status'],
				'areas'       => str_replace(',', PHP_EOL, $row['areas'])
			];

			$currentBlocks[$row['placement']][$row['block_id']]['titles'][$row['lang']] = $row['title'];

			$this->prepareMissingBlockTypes($row['type']);
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return array_merge(array_flip(array_keys(Utils::$context['lp_block_placements'])), $currentBlocks);
	}

	public function getData(int $item): array
	{
		if (empty($item))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.type, b.note, b.content, b.placement, b.priority, b.permissions, b.status, b.areas, b.title_class, b.content_class,
				bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
			WHERE b.block_id = {int:item}',
			[
				'item' => $item
			]
		);

		if (empty(Utils::$smcFunc['db_num_rows']($result))) {
			Utils::$context['error_link'] = Config::$scripturl . '?action=admin;area=lp_blocks';

			$this->fatalLangError('lp_block_not_found', 404);
		}

		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if ($row['type'] === 'bbc') {
				$row['content'] = $this->unPreparseCode($row['content']);
			}

			$this->censorText($row['content']);

			$data ??= [
				'id'            => (int) $row['block_id'],
				'icon'          => $row['icon'],
				'type'          => $row['type'],
				'note'          => $row['note'],
				'content'       => $row['content'],
				'placement'     => $row['placement'],
				'priority'      => (int) $row['priority'],
				'permissions'   => (int) $row['permissions'],
				'status'        => (int) $row['status'],
				'areas'         => $row['areas'],
				'title_class'   => $row['title_class'],
				'content_class' => $row['content_class'],
			];

			if (! empty($row['lang']))
				$data['titles'][$row['lang']] = $row['title'];

			if (! empty($row['name']))
				$data['options'][$row['name']] = $row['value'];

			$this->prepareMissingBlockTypes($row['type']);
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $data ?? [];
	}

	/**
	 * @return int|void
	 */
	public function setData(int $item = 0)
	{
		if (isset(Utils::$context['post_errors']) || (
			$this->request()->hasNot('save') &&
			$this->request()->hasNot('save_exit') &&
			$this->request()->hasNot('clone'))
		)
			return 0;

		$this->checkSubmitOnce('check');

		$this->prepareBbcContent(Utils::$context['lp_block']);

		if (empty($item)) {
			Utils::$context['lp_block']['titles'] = array_filter(Utils::$context['lp_block']['titles']);
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		if ($this->request()->isNotEmpty('clone'))
			return $item;

		$this->cache()->flush();

		if ($this->request()->has('save_exit'))
			$this->redirect('action=admin;area=lp_blocks;sa=main');

		if ($this->request()->has('save'))
			$this->redirect('action=admin;area=lp_blocks;sa=edit;id=' . $item);
	}

	private function addData(): int
	{
		Utils::$smcFunc['db_transaction']('begin');

		$item = (int) Utils::$smcFunc['db_insert']('',
			'{db_prefix}lp_blocks',
			[
				'icon'          => 'string',
				'type'          => 'string',
				'note'          => 'string',
				'content'       => 'string-65534',
				'placement'     => 'string-10',
				'priority'      => 'int',
				'permissions'   => 'int',
				'status'        => 'int',
				'areas'         => 'string',
				'title_class'   => 'string',
				'content_class' => 'string',
			],
			[
				Utils::$context['lp_block']['icon'],
				Utils::$context['lp_block']['type'],
				Utils::$context['lp_block']['note'],
				Utils::$context['lp_block']['content'],
				Utils::$context['lp_block']['placement'],
				Utils::$context['lp_block']['priority'],
				Utils::$context['lp_block']['permissions'],
				Utils::$context['lp_block']['status'],
				Utils::$context['lp_block']['areas'],
				Utils::$context['lp_block']['title_class'],
				Utils::$context['lp_block']['content_class'],
			],
			['block_id'],
			1
		);

		Utils::$context['lp_num_queries']++;

		if (empty($item)) {
			Utils::$smcFunc['db_transaction']('rollback');
			return 0;
		}

		$this->hook('onBlockSaving', [$item]);

		$this->saveTitles($item);
		$this->saveOptions($item);

		Utils::$smcFunc['db_transaction']('commit');

		return $item;
	}

	private function updateData(int $item): void
	{
		Utils::$smcFunc['db_transaction']('begin');

		Utils::$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_blocks
			SET icon = {string:icon}, type = {string:type}, note = {string:note}, content = {string:content}, placement = {string:placement}, permissions = {int:permissions}, areas = {string:areas}, title_class = {string:title_class}, content_class = {string:content_class}
			WHERE block_id = {int:block_id}',
			[
				'icon'          => Utils::$context['lp_block']['icon'],
				'type'          => Utils::$context['lp_block']['type'],
				'note'          => Utils::$context['lp_block']['note'],
				'content'       => Utils::$context['lp_block']['content'],
				'placement'     => Utils::$context['lp_block']['placement'],
				'permissions'   => Utils::$context['lp_block']['permissions'],
				'areas'         => Utils::$context['lp_block']['areas'],
				'title_class'   => Utils::$context['lp_block']['title_class'],
				'content_class' => Utils::$context['lp_block']['content_class'],
				'block_id'      => $item,
			]
		);

		Utils::$context['lp_num_queries']++;

		$this->hook('onBlockSaving', [$item]);

		$this->saveTitles($item, 'replace');
		$this->saveOptions($item, 'replace');

		Utils::$smcFunc['db_transaction']('commit');

		$this->cache()->forget(Utils::$context['lp_block']['type'] . '_addon_b' . $item);
		$this->cache()->forget(Utils::$context['lp_block']['type'] . '_addon_u' . Utils::$context['user']['id']);
		$this->cache()->forget(Utils::$context['lp_block']['type'] . '_addon_b' . $item . '_u' . Utils::$context['user']['id']);
	}

	/**
	 * Prepare plugins list that not installed
	 *
	 * Формируем список неустановленных плагинов
	 */
	private function prepareMissingBlockTypes(string $type): void
	{
		if (isset(Lang::$txt['lp_' . $type]['title']))
			return;

		$addon = $this->getCamelName($type);

		$message = in_array($addon, $this->getEntityList('plugin')) ? Lang::$txt['lp_addon_not_activated'] : Lang::$txt['lp_addon_not_installed'];

		Utils::$context['lp_missing_block_types'][$type] = '<span class="error">' . sprintf($message, $addon) . '</span>';
	}
}
