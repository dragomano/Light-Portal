<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Msg;
use Bugo\Compat\Security;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;

use function array_filter;
use function array_flip;
use function array_keys;
use function array_merge;
use function explode;
use function in_array;
use function sprintf;
use function str_replace;

if (! defined('SMF'))
	die('No direct access...');

final class BlockRepository extends AbstractRepository
{
	use HasCache;
	use HasEvents;
	use HasRequest;
	use HasResponse;

	protected string $entity = 'block';

	public function getAll(): array
	{
		$result = Db::$db->query('', '
			SELECT b.block_id, b.icon, b.type, b.note, b.placement, b.priority, b.permissions, b.status, b.areas,
				bt.lang, bt.value AS title
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
			ORDER BY b.placement DESC, b.priority',
		);

		$currentBlocks = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$currentBlocks[$row['placement']][$row['block_id']] ??= [
				'icon'        => Icon::parse($row['icon']),
				'type'        => $row['type'],
				'note'        => $row['note'],
				'priority'    => $row['priority'],
				'permissions' => $row['permissions'],
				'status'      => $row['status'],
				'areas'       => str_replace(',', PHP_EOL, (string) $row['areas']),
			];

			if (! empty($row['lang'])) {
				$currentBlocks[$row['placement']][$row['block_id']]['titles'][$row['lang']] = $row['title'];
			}

			$this->prepareMissingBlockTypes($row['type']);
		}

		Db::$db->free_result($result);

		return array_merge(array_flip(array_keys(Utils::$context['lp_block_placements'])), $currentBlocks);
	}

	public function getData(int $item): array
	{
		if ($item === 0)
			return [];

		$result = Db::$db->query('', '
			SELECT
				b.block_id, b.icon, b.type, b.note, b.content, b.placement, b.priority,
				b.permissions, b.status, b.areas, b.title_class, b.content_class,
				bt.lang, bt.value AS title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
			WHERE b.block_id = {int:item}',
			[
				'item' => $item,
			]
		);

		if (empty(Db::$db->num_rows($result))) {
			Utils::$context['error_link'] = Config::$scripturl . '?action=admin;area=lp_blocks';

			ErrorHandler::fatalLang('lp_block_not_found', false, status: 404);
		}

		while ($row = Db::$db->fetch_assoc($result)) {
			if ($row['type'] === 'bbc') {
				$row['content'] = Msg::un_preparsecode($row['content']);
			}

			Lang::censorText($row['content']);

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

			if (! empty($row['lang'])) {
				$data['titles'][$row['lang']] = $row['title'];
			}

			if (! empty($row['name'])) {
				$data['options'][$row['name']] = $row['value'];
			}

			$this->prepareMissingBlockTypes($row['type']);
		}

		Db::$db->free_result($result);

		return $data ?? [];
	}

	/**
	 * @return int|void
	 */
	public function setData(int $item = 0)
	{
		if (isset(Utils::$context['post_errors']) || $this->request()->hasNot(['save', 'save_exit', 'clone'])) {
			return 0;
		}

		Security::checkSubmitOnce('check');

		$this->prepareBbcContent(Utils::$context['lp_block']);

		if (empty($item)) {
			Utils::$context['lp_block']['titles'] = array_filter(Utils::$context['lp_block']['titles'] ?? []);
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		if ($this->request()->isNotEmpty('clone'))
			return $item;

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

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_blocks
			WHERE block_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			[
				'items' => $items,
			]
		);

		$this->session('lp')->free('active_blocks');
	}

	public function updatePriority(array $blocks = [], string $placement = ''): void
	{
		if ($blocks === [])
			return;

		$conditions = '';
		foreach ($blocks as $priority => $item) {
			$conditions .= ' WHEN block_id = ' . $item . ' THEN ' . $priority;
		}

		if ($conditions === '')
			return;

		Db::$db->query('', /** @lang text */ '
			UPDATE {db_prefix}lp_blocks
			SET priority = CASE ' . $conditions . ' ELSE priority END
			WHERE block_id IN ({array_int:blocks})',
			[
				'blocks' => $blocks,
			]
		);

		if ($placement) {
			Db::$db->query('', '
				UPDATE {db_prefix}lp_blocks
				SET placement = {string:placement}
				WHERE block_id IN ({array_int:blocks})',
				[
					'placement' => $placement,
					'blocks'    => $blocks,
				]
			);
		}
	}

	public function getActive(): array
	{
		if (Setting::hideBlocksInACP())
			return [];

		if (($blocks = $this->cache()->get('active_blocks')) === null) {
			$result = Db::$db->query('', '
				SELECT
					b.block_id, b.icon, b.type, b.content, b.placement, b.priority,
					b.permissions, b.areas, b.title_class, b.content_class,
					bt.lang, bt.value AS title, bp.name, bp.value
				FROM {db_prefix}lp_blocks AS b
					LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
					LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
				WHERE b.status = {int:status}
				ORDER BY b.placement, b.priority',
				[
					'status' => Status::ACTIVE->value,
				]
			);

			$blocks = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				Lang::censorText($row['content']);

				$blocks[$row['block_id']] ??= [
					'id'            => (int) $row['block_id'],
					'icon'          => $row['icon'],
					'type'          => $row['type'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => (int) $row['priority'],
					'permissions'   => (int) $row['permissions'],
					'areas'         => explode(',', (string) $row['areas']),
					'title_class'   => $row['title_class'],
					'content_class' => $row['content_class'],
				];

				$blocks[$row['block_id']]['titles'][$row['lang']] = $row['title'];
				$blocks[$row['block_id']]['titles'] = array_filter($blocks[$row['block_id']]['titles']);

				if ($row['name']) {
					$blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
				}
			}

			Db::$db->free_result($result);

			$this->cache()->put('active_blocks', $blocks);
		}

		return $blocks;
	}

	private function addData(): int
	{
		Db::$db->transaction('begin');

		$item = (int) Db::$db->insert('',
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
				$this->getPriority(),
				Utils::$context['lp_block']['permissions'],
				Utils::$context['lp_block']['status'],
				Utils::$context['lp_block']['areas'],
				Utils::$context['lp_block']['title_class'],
				Utils::$context['lp_block']['content_class'],
			],
			['block_id'],
			1
		);

		if (empty($item)) {
			Db::$db->transaction('rollback');
			return 0;
		}

		$this->events()->dispatch(PortalHook::onBlockSaving, ['item' => $item]);

		$this->saveTitles($item);
		$this->saveOptions($item);

		Db::$db->transaction();

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('', '
			UPDATE {db_prefix}lp_blocks
			SET icon = {string:icon}, type = {string:type}, note = {string:note}, content = {string:content},
				placement = {string:placement}, permissions = {int:permissions}, areas = {string:areas},
				title_class = {string:title_class}, content_class = {string:content_class}
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

		$this->events()->dispatch(PortalHook::onBlockSaving, ['item' => $item]);

		$this->saveTitles($item, 'replace');
		$this->saveOptions($item, 'replace');

		Db::$db->transaction();
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

	private function getPriority(): int
	{
		if (empty(Utils::$context['lp_block']['placement']))
			return 0;

		$result = Db::$db->query('', '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_blocks
			WHERE placement = {string:placement}',
			[
				'placement' => Utils::$context['lp_block']['placement'],
			]
		);

		[$priority] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $priority;
	}
}
