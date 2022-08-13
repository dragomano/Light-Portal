<?php declare(strict_types=1);

/**
 * BlockRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Repositories;

if (! defined('SMF'))
	die('No direct access...');

final class BlockRepository extends AbstractRepository
{
	protected string $entity = 'block';

	public function getAll(bool $with_customs = false): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT b.block_id, b.user_id, b.icon, b.type, b.note, b.placement, b.priority, b.permissions, b.status, b.areas, bt.lang, bt.title
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})' . ($this->user_info['is_admin'] ? ($with_customs ? '' : '
			WHERE b.user_id = 0') : '
			WHERE b.user_id = {int:user_id}') . '
			ORDER BY b.placement DESC, b.priority',
			[
				'user_id' => $this->user_info['id']
			]
		);

		$currentBlocks = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$currentBlocks[$row['placement']][$row['block_id']] ??= [
				'user_id'     => $row['user_id'],
				'icon'        => $this->getIcon($row['icon']),
				'type'        => $row['type'],
				'note'        => $row['note'],
				'priority'    => $row['priority'],
				'permissions' => $row['permissions'],
				'status'      => $row['status'],
				'areas'       => str_replace(',', PHP_EOL, $row['areas'])
			];

			$currentBlocks[$row['placement']][$row['block_id']]['title'][$row['lang']] = $row['title'];

			$this->prepareMissingBlockTypes($row['type']);
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return array_merge(array_flip(array_keys($this->context['lp_block_placements'])), $currentBlocks);
	}

	public function getData(int $item): array
	{
		if (empty($item))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT
				b.block_id, b.user_id, b.icon, b.type, b.note, b.content, b.placement, b.priority, b.permissions, b.status, b.areas, b.title_class, b.title_style, b.content_class, b.content_style, bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
			WHERE b.block_id = {int:item}',
			[
				'item' => $item
			]
		);

		if (empty($this->smcFunc['db_num_rows']($request))) {
			$this->context['error_link'] = $this->scripturl . '?action=admin;area=lp_blocks';

			$this->fatalLangError('lp_block_not_found', false, null, 404);
		}

		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			if ($row['type'] === 'bbc') {
				$row['content'] = $this->unPreparseCode($row['content']);
			}

			$this->censorText($row['content']);

			$data ??= [
				'id'            => (int) $row['block_id'],
				'user_id'       => (int) $row['user_id'],
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
				'title_style'   => $row['title_style'],
				'content_class' => $row['content_class'],
				'content_style' => $row['content_style'],
			];

			$data['title'][$row['lang']] = $row['title'];

			if (! empty($row['value']))
				$data['options']['parameters'][$row['name']] = $row['value'];

			$this->prepareMissingBlockTypes($row['type']);
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $data ?? [];
	}

	/**
	 * @return int|void
	 */
	public function setData(int $item = 0)
	{
		if (isset($this->context['post_errors']) || (
			$this->request()->has('save') === false &&
			$this->request()->has('save_exit') === false &&
			$this->request()->has('clone') === false)
		)
			return 0;

		$this->checkSubmitOnce('check');

		$this->prepareBbcContent($this->context['lp_block']);

		$this->context['lp_block']['options'] = $this->context['lp_block']['options']['parameters'] ?? [];

		if (empty($item)) {
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
		$this->smcFunc['db_transaction']('begin');

		$item = (int) $this->smcFunc['db_insert']('',
			'{db_prefix}lp_blocks',
			[
				'user_id'       => 'int',
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
				'title_style'   => 'string',
				'content_class' => 'string',
				'content_style' => 'string',
			],
			[
				$this->context['lp_block']['user_id'],
				$this->context['lp_block']['icon'],
				$this->context['lp_block']['type'],
				$this->context['lp_block']['note'],
				$this->context['lp_block']['content'],
				$this->context['lp_block']['placement'],
				$this->context['lp_block']['priority'],
				$this->context['lp_block']['permissions'],
				$this->context['lp_block']['status'],
				$this->context['lp_block']['areas'],
				$this->context['lp_block']['title_class'],
				$this->context['lp_block']['title_style'],
				$this->context['lp_block']['content_class'],
				$this->context['lp_block']['content_style'],
			],
			['block_id'],
			1
		);

		$this->context['lp_num_queries']++;

		if (empty($item)) {
			$this->smcFunc['db_transaction']('rollback');
			return 0;
		}

		$this->hook('onBlockSaving', [$item]);

		$this->saveTitles($item);
		$this->saveOptions($item);

		$this->smcFunc['db_transaction']('commit');

		return $item;
	}

	private function updateData(int $item)
	{
		$this->smcFunc['db_transaction']('begin');

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_blocks
			SET icon = {string:icon}, type = {string:type}, note = {string:note}, content = {string:content}, placement = {string:placement}, permissions = {int:permissions}, areas = {string:areas}, title_class = {string:title_class}, title_style = {string:title_style}, content_class = {string:content_class}, content_style = {string:content_style}
			WHERE block_id = {int:block_id}',
			[
				'icon'          => $this->context['lp_block']['icon'],
				'type'          => $this->context['lp_block']['type'],
				'note'          => $this->context['lp_block']['note'],
				'content'       => $this->context['lp_block']['content'],
				'placement'     => $this->context['lp_block']['placement'],
				'permissions'   => $this->context['lp_block']['permissions'],
				'areas'         => $this->context['lp_block']['areas'],
				'title_class'   => $this->context['lp_block']['title_class'],
				'title_style'   => $this->context['lp_block']['title_style'],
				'content_class' => $this->context['lp_block']['content_class'],
				'content_style' => $this->context['lp_block']['content_style'],
				'block_id'      => $item,
			]
		);

		$this->context['lp_num_queries']++;

		$this->hook('onBlockSaving', [$item]);

		$this->saveTitles($item, 'replace');
		$this->saveOptions($item, 'replace');

		$this->smcFunc['db_transaction']('commit');

		$this->cache()->forget($this->context['lp_block']['type'] . '_addon_b' . $item);
		$this->cache()->forget($this->context['lp_block']['type'] . '_addon_u' . $this->context['user']['id']);
		$this->cache()->forget($this->context['lp_block']['type'] . '_addon_b' . $item . '_u' . $this->context['user']['id']);
	}

	/**
	 * Prepare plugins list that not installed
	 *
	 * Формируем список неустановленных плагинов
	 */
	private function prepareMissingBlockTypes(string $type)
	{
		if (isset($this->txt['lp_' . $type]['title']))
			return;

		$addon = $this->getCamelName($type);

		$message = in_array($addon, $this->getAllAddons()) ? $this->txt['lp_addon_not_activated'] : $this->txt['lp_addon_not_installed'];

		$this->context['lp_missing_block_types'][$type] = '<span class="error">' . sprintf($message, $addon) . '</span>';
	}
}
