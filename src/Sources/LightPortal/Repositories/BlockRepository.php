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
	use HasEvents;

	protected string $entity = 'block';

	public function getAll(): array
	{
		$result = Db::$db->query('', '
			SELECT
				b.*,
				COALESCE(NULLIF(t.title, {string:empty_string}), tf.title, {string:empty_string}) AS title,
				COALESCE(NULLIF(t.description, {string:empty_string}), tf.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					b.block_id = t.item_id AND t.type = {literal:block} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					b.block_id = tf.item_id AND tf.type = {literal:block} AND tf.lang = {string:fallback_lang}
				)
			ORDER BY b.placement DESC, b.priority',
			$this->getLangQueryParams()
		);

		$currentBlocks = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);
			Lang::censorText($row['description']);

			$currentBlocks[$row['placement']][$row['block_id']] ??= [
				'icon'        => Icon::parse($row['icon']),
				'type'        => $row['type'],
				'priority'    => $row['priority'],
				'permissions' => $row['permissions'],
				'status'      => $row['status'],
				'areas'       => str_replace(',', PHP_EOL, (string) $row['areas']),
				'title'       => $row['title'],
				'description' => $row['description'],
			];

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
				b.*, bp.name, bp.value,
				COALESCE(t.title, {string:empty_string}) AS title,
				COALESCE(NULLIF(t.content, {string:empty_string}), tf.content, {string:empty_string}) AS content,
				COALESCE(t.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					b.block_id = t.item_id AND t.type = {literal:block} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					b.block_id = tf.item_id AND tf.type = {literal:block} AND tf.lang = {string:fallback_lang}
				)
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
			WHERE b.block_id = {int:item}',
			array_merge($this->getLangQueryParams(), compact('item'))
		);

		if (empty(Db::$db->num_rows($result))) {
			Utils::$context['error_link'] = Config::$scripturl . '?action=admin;area=lp_blocks';

			ErrorHandler::fatalLang('lp_block_not_found', false, status: 404);
		}

		while ($row = Db::$db->fetch_assoc($result)) {
			if ($row['type'] === 'bbc') {
				$row['content'] = Msg::un_preparsecode($row['content']);
			}

			$data ??= [
				'id'            => (int) $row['block_id'],
				'icon'          => $row['icon'],
				'type'          => $row['type'],
				'placement'     => $row['placement'],
				'priority'      => (int) $row['priority'],
				'permissions'   => (int) $row['permissions'],
				'status'        => (int) $row['status'],
				'areas'         => $row['areas'],
				'title_class'   => $row['title_class'],
				'content_class' => $row['content_class'],
				'title'         => $row['title'],
				'content'       => $row['content'],
				'description'   => $row['description'],
			];

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
			DELETE FROM {db_prefix}lp_translations
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

		return $this->langCache('active_blocks')
			->setFallback(function () {
				$result = Db::$db->query('', '
				SELECT
					b.*, bp.name, bp.value,
					COALESCE(t.title, tf.title, {string:empty_string}) AS title,
					COALESCE(NULLIF(t.content, {string:empty_string}), tf.content, {string:empty_string}) AS content
				FROM {db_prefix}lp_blocks AS b
					LEFT JOIN {db_prefix}lp_translations AS t ON (
						b.block_id = t.item_id AND t.type = {literal:block} AND t.lang = {string:lang}
					)
					LEFT JOIN {db_prefix}lp_translations AS tf ON (
						b.block_id = tf.item_id AND tf.type = {literal:block} AND tf.lang = {string:fallback_lang}
					)
					LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
				WHERE b.status = {int:status}
				ORDER BY b.placement, b.priority',
					array_merge($this->getLangQueryParams(), ['status' => Status::ACTIVE->value])
				);

				$blocks = [];
				while ($row = Db::$db->fetch_assoc($result)) {
					Lang::censorText($row['title']);
					Lang::censorText($row['content']);

					$blocks[$row['block_id']] ??= [
						'id'            => (int) $row['block_id'],
						'icon'          => $row['icon'],
						'type'          => $row['type'],
						'placement'     => $row['placement'],
						'priority'      => (int) $row['priority'],
						'permissions'   => (int) $row['permissions'],
						'areas'         => explode(',', (string) $row['areas']),
						'title_class'   => $row['title_class'],
						'content_class' => $row['content_class'],
						'title'         => $row['title'],
						'content'       => $row['content'],
					];

					if ($row['name']) {
						$blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
					}
				}

				Db::$db->free_result($result);

				return $blocks;
			});
	}

	private function addData(): int
	{
		Db::$db->transaction('begin');

		$item = (int) Db::$db->insert('',
			'{db_prefix}lp_blocks',
			[
				'icon'          => 'string',
				'type'          => 'string',
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

		$this->saveTranslations($item);
		$this->saveOptions($item);

		Db::$db->transaction();

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('', '
			UPDATE {db_prefix}lp_blocks
			SET icon = {string:icon}, type = {string:type}, placement = {string:placement},
				permissions = {int:permissions}, areas = {string:areas}, title_class = {string:title_class},
				content_class = {string:content_class}
			WHERE block_id = {int:block_id}',
			[
				'icon'          => Utils::$context['lp_block']['icon'],
				'type'          => Utils::$context['lp_block']['type'],
				'placement'     => Utils::$context['lp_block']['placement'],
				'permissions'   => Utils::$context['lp_block']['permissions'],
				'areas'         => Utils::$context['lp_block']['areas'],
				'title_class'   => Utils::$context['lp_block']['title_class'],
				'content_class' => Utils::$context['lp_block']['content_class'],
				'block_id'      => $item,
			]
		);

		$this->events()->dispatch(PortalHook::onBlockSaving, ['item' => $item]);

		$this->saveTranslations($item, 'replace');
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
