<?php declare(strict_types=1);

/**
 * Block.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Entities;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class Block
{
	use Helper;

	public function show(): void
	{
		if ($this->isHideBlocksInAdmin() || $this->request()->is('devtools'))
			return;

		if (empty($this->context['allow_light_portal_view']) || empty($this->context['template_layers']) || empty($this->context['lp_active_blocks']))
			return;

		if (empty($blocks = $this->getFilteredByAreas()))
			return;

		// Block placement
		foreach ($blocks as $item => $data) {
			if ($this->canViewItem($data['permissions'], $data['user_id']) === false)
				continue;

			$data['can_edit'] = $this->context['user']['is_admin'] || ($this->context['allow_light_portal_manage_blocks'] && $data['user_id'] == $this->context['user']['id']);

			$data['content'] = empty($data['content'])
				? prepare_content($data['type'], $data['id'], LP_CACHE_TIME, $this->context['lp_active_blocks'][$data['id']]['parameters'])
				: parse_content($data['content'], $data['type']);

			if (empty($data['title'][$this->context['user']['language']]))
				$data['title'][$this->context['user']['language']] = $this->context['lp_active_blocks'][$data['id']]['title'][$this->context['user']['language']] ?? '';

			$this->context['lp_blocks'][$data['placement']][$item] = $data;

			$title = $this->getTranslatedTitle($data['title']);
			$icon  = $this->getIcon($this->context['lp_blocks'][$data['placement']][$item]['icon']);

			$this->context['lp_blocks'][$data['placement']][$item]['title'] = $icon . $title;
		}

		$this->loadTemplate('LightPortal/ViewBlock');

		$counter = 0;
		foreach ($this->context['template_layers'] as $layer) {
			$counter++;

			if ($layer === 'body')
				break;
		}

		$this->context['template_layers'] = array_merge(
			array_slice($this->context['template_layers'], 0, $counter, true),
			['lp_portal'],
			array_slice($this->context['template_layers'], $counter, null, true)
		);
	}

	public function getActive(): array
	{
		if ($this->isHideBlocksInAdmin())
			return [];

		if (($active_blocks = $this->cache()->get('active_blocks')) === null) {
			$request = $this->smcFunc['db_query']('', '
				SELECT
					b.block_id, b.user_id, b.icon, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
					bt.lang, bt.title, bp.name, bp.value
				FROM {db_prefix}lp_blocks AS b
					LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
					LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
				WHERE b.status = {int:status}
				ORDER BY b.placement, b.priority',
				[
					'status' => 1
				]
			);

			$active_blocks = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
				$this->censorText($row['content']);

				$active_blocks[$row['block_id']] ??= [
					'id'            => (int) $row['block_id'],
					'user_id'       => (int) $row['user_id'],
					'icon'          => $row['icon'],
					'type'          => $row['type'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => (int) $row['priority'],
					'permissions'   => (int) $row['permissions'],
					'areas'         => explode(',', $row['areas']),
					'title_class'   => $row['title_class'],
					'title_style'   => $row['title_style'],
					'content_class' => $row['content_class'],
					'content_style' => $row['content_style'],
				];

				$active_blocks[$row['block_id']]['title'][$row['lang']] = $row['title'];

				$active_blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
			}

			$this->smcFunc['db_free_result']($request);
			$this->context['lp_num_queries']++;

			$this->cache()->put('active_blocks', $active_blocks);
		}

		return $active_blocks;
	}

	private function getFilteredByAreas(): array
	{
		$area = $this->context['current_action'] ?: (empty($this->modSettings['lp_frontpage_mode']) ? 'forum' : LP_ACTION);

		if (! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url']))) {
			if ($this->modSettings['lp_standalone_url'] === $this->request()->url()) {
				$area = LP_ACTION;
			} elseif (empty($this->context['current_action'])) {
				$area = 'forum';
			}
		}

		if (isset($this->context['current_board']) || isset($this->context['lp_page']))
			$area = '';

		if (! empty($this->context['lp_page']['alias']) && $this->isFrontpage($this->context['lp_page']['alias']))
			$area = LP_ACTION;

		return array_filter($this->context['lp_active_blocks'], function ($block) use ($area) {
			$temp_areas     = $block['areas'];
			$block['areas'] = array_flip($block['areas']);

			if (isset($block['areas']['!' . $area]) && $temp_areas[0] === 'all')
				return false;

			if (isset($block['areas']['all']) || isset($block['areas'][$area]))
				return true;

			if ($area === LP_ACTION && isset($block['areas']['home']) && empty($this->context['lp_page']) && empty($this->context['current_action']))
				return true;

			if (isset($this->context['lp_page']) && isset($this->context['lp_page']['alias'])) {
				if (isset($block['areas']['!' . LP_PAGE_PARAM . '=' . $this->context['lp_page']['alias']]) && $temp_areas[0] === 'pages')
					return false;

				if (isset($block['areas']['pages']) || isset($block['areas'][LP_PAGE_PARAM . '=' . $this->context['lp_page']['alias']]))
					return true;
			}

			if (empty($this->context['current_board']))
				return false;

			if (isset($block['areas']['boards']) || (! empty($this->context['current_topic']) && isset($block['areas']['topics'])))
				return true;

			$boards = $topics = [];
			foreach ($temp_areas as $areas) {
				$entity = explode('=', $areas);

				if ($entity[0] === 'board')
					$boards = $this->getAllowedIds($entity[1]);

				if ($entity[0] === 'topic')
					$topics = $this->getAllowedIds($entity[1]);
			}

			return in_array($this->context['current_board'], $boards) || (isset($this->context['current_topic']) && in_array($this->context['current_topic'], $topics));
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
		return ! empty($this->modSettings['lp_hide_blocks_in_acp']) && $this->request()->is('admin');
	}
}
