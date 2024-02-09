<?php declare(strict_types=1);

/**
 * Block.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Actions;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{Config, Content, Lang, Theme, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class Block implements BlockInterface
{
	use Helper;

	public function show(): void
	{
		if ($this->isHideBlocksInAdmin() || $this->request()->is('devtools') || $this->request()->has('preview'))
			return;

		if (empty(Utils::$context['allow_light_portal_view']))
			return;

		if (empty(Utils::$context['template_layers']) || empty(Utils::$context['lp_active_blocks']))
			return;

		if (empty($blocks = $this->getFilteredByAreas()))
			return;

		// Block placement
		foreach ($blocks as $item => $data) {
			if ($this->canViewItem($data['permissions']) === false)
				continue;

			$data['can_edit'] = Utils::$context['user']['is_admin'];

			$data['content'] = empty($data['content'])
				? Content::prepare(
					$data['type'],
					$data['id'],
					LP_CACHE_TIME,
					Utils::$context['lp_active_blocks'][$data['id']]['parameters'] ?? []
				)
				: Content::parse($data['content'], $data['type']);

			Utils::$context['lp_blocks'][$data['placement']][$item] = $data;

			if (empty($data['parameters']['hide_header'])) {
				$title = $this->getTranslatedTitle($data['titles']);
				$icon  = $this->getIcon(Utils::$context['lp_blocks'][$data['placement']][$item]['icon']);
			} else {
				$title = $icon = '';
			}

			Utils::$context['lp_blocks'][$data['placement']][$item]['title'] = $icon . $title;
		}

		Theme::loadTemplate('LightPortal/ViewBlocks');

		$counter = 0;
		foreach (Utils::$context['template_layers'] as $layer) {
			$counter++;

			if ($layer === 'body')
				break;
		}

		Utils::$context['template_layers'] = array_merge(
			array_slice(Utils::$context['template_layers'], 0, $counter, true),
			['lp_portal'],
			array_slice(Utils::$context['template_layers'], $counter, null, true)
		);
	}

	public function getActive(): array
	{
		if ($this->isHideBlocksInAdmin())
			return [];

		if (($blocks = $this->cache()->get('active_blocks')) === null) {
			$result = Utils::$smcFunc['db_query']('', '
				SELECT
					b.block_id, b.icon, b.type, b.content, b.placement, b.priority,
					b.permissions, b.areas, b.title_class, b.content_class,
					bt.lang, bt.title, bp.name, bp.value
				FROM {db_prefix}lp_blocks AS b
					LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
					LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
				WHERE b.status = {int:status}
				ORDER BY b.placement, b.priority',
				[
					'status' => self::STATUS_ACTIVE
				]
			);

			$blocks = [];
			while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
				Lang::censorText($row['content']);

				$blocks[$row['block_id']] ??= [
					'id'            => (int) $row['block_id'],
					'icon'          => $row['icon'],
					'type'          => $row['type'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => (int) $row['priority'],
					'permissions'   => (int) $row['permissions'],
					'areas'         => explode(',', $row['areas']),
					'title_class'   => $row['title_class'],
					'content_class' => $row['content_class'],
				];

				$blocks[$row['block_id']]['titles'][$row['lang']] = $row['title'];
				$blocks[$row['block_id']]['titles'] = array_filter($blocks[$row['block_id']]['titles']);

				$blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
			}

			Utils::$smcFunc['db_free_result']($result);
			Utils::$context['lp_num_queries']++;

			$this->cache()->put('active_blocks', $blocks);
		}

		return $blocks;
	}

	private function getFilteredByAreas(): array
	{
		$area = Utils::$context['current_action'] ?: (
			empty(Config::$modSettings['lp_frontpage_mode']) ? 'forum' : LP_ACTION
		);

		if (! (empty(Config::$modSettings['lp_standalone_mode']) || empty(Config::$modSettings['lp_standalone_url']))) {
			if (Config::$modSettings['lp_standalone_url'] === $this->request()->url()) {
				$area = LP_ACTION;
			} elseif (empty(Utils::$context['current_action'])) {
				$area = 'forum';
			}
		}

		if (isset(Utils::$context['current_board']) || isset(Utils::$context['lp_page']))
			$area = '';

		if (! empty(Utils::$context['lp_page']['alias']) && $this->isFrontpage(Utils::$context['lp_page']['alias']))
			$area = LP_ACTION;

		return array_filter(Utils::$context['lp_active_blocks'], function ($block) use ($area) {
			$tempAreas = $block['areas'];
			$block['areas'] = array_flip($block['areas']);

			if (isset($block['areas']['!' . $area]) && $tempAreas[0] === 'all')
				return false;

			if (isset($block['areas']['all']) || isset($block['areas'][$area]))
				return true;

			if (
				$area === LP_ACTION
				&& isset($block['areas']['home'])
				&& empty(Utils::$context['lp_page'])
				&& empty(Utils::$context['current_action'])
			) {
				return true;
			}

			if (isset(Utils::$context['lp_page']['alias'])) {
				if (
					isset($block['areas']['!' . LP_PAGE_PARAM . '=' . Utils::$context['lp_page']['alias']])
					&& $tempAreas[0] === 'pages'
				) {
					return false;
				}

				if (
					isset($block['areas']['pages'])
					|| isset($block['areas'][LP_PAGE_PARAM . '=' . Utils::$context['lp_page']['alias']])
				) {
					return true;
				}
			}

			if (empty(Utils::$context['current_board']))
				return false;

			if (isset($block['areas']['boards']) && empty(Utils::$context['current_topic']))
				return true;

			if (isset($block['areas']['topics']) && ! empty(Utils::$context['current_topic']))
				return true;

			$boards = $topics = [];
			foreach ($tempAreas as $areas) {
				$entity = explode('=', $areas);

				if ($entity[0] === 'board')
					$boards = $this->getAllowedIds($entity[1]);

				if ($entity[0] === 'topic')
					$topics = $this->getAllowedIds($entity[1]);
			}

			return in_array(Utils::$context['current_board'], $boards)
				|| (isset(Utils::$context['current_topic']) && in_array(Utils::$context['current_topic'], $topics));
		});
	}

	private function getAllowedIds(string $entity = ''): array
	{
		$ids = [];

		$items = explode('|', $entity);
		foreach ($items as $item) {
			if (str_contains($item, '-')) {
				$range = explode('-', $item);
				for ($i = $range[0]; $i <= $range[1]; $i++) {
					$ids[] = $i;
				}
			} else {
				$ids[] = $item;
			}
		}

		return $ids;
	}

	private function isHideBlocksInAdmin(): bool
	{
		return ! empty(Config::$modSettings['lp_hide_blocks_in_acp']) && $this->request()->is('admin');
	}
}
