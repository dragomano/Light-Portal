<?php

namespace Bugo\LightPortal;

/**
 * Block.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.10
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Block
{
	public const STATUS_ACTIVE = 1;
	public const STATUS_INACTIVE = 0;

	/**
	 * Display blocks in their designated areas
	 *
	 * Отображаем блоки в предназначенных им областях
	 *
	 * @return void
	 */
	public function show()
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_hide_blocks_in_admin_section']) && Helpers::request()->is('admin'))
			return;

		if (empty($context['allow_light_portal_view']) || empty($context['template_layers']) || empty($context['lp_active_blocks']))
			return;

		if (empty($blocks = $this->getFilteredByAreas()))
			return;

		// Block placement
		foreach ($blocks as $item => $data) {
			if (Helpers::canViewItem($data['permissions']) === false)
				continue;

			$data['can_edit'] = $context['user']['is_admin'] || ($context['allow_light_portal_manage_own_blocks'] && $data['user_id'] == $context['user']['id']);

			empty($data['content'])
				? Helpers::prepareContent($data['content'], $data['type'], $data['id'], LP_CACHE_TIME)
				: Helpers::parseContent($data['content'], $data['type']);

			if (empty($data['title'][$context['user']['language']]))
				$data['title'][$context['user']['language']] = $context['lp_active_blocks'][$data['id']]['title'][$context['user']['language']] ?? '';

			$context['lp_blocks'][$data['placement']][$item] = $data;

			$title = Helpers::getTranslatedTitle($data['title']);
			$icon  = Helpers::getIcon($context['lp_blocks'][$data['placement']][$item]['icon']);

			$context['lp_blocks'][$data['placement']][$item]['title'] = $icon . $title;
		}

		loadTemplate('LightPortal/ViewBlock');

		$counter = 0;
		foreach ($context['template_layers'] as $layer) {
			$counter++;

			if ($layer === 'body')
				break;
		}

		$context['template_layers'] = array_merge(
			array_slice($context['template_layers'], 0, $counter, true),
			array('lp_portal'),
			array_slice($context['template_layers'], $counter, null, true)
		);
	}

	/**
	 * @return array
	 */
	public static function getActive(): array
	{
		global $smcFunc;

		if (($active_blocks = Helpers::cache()->get('active_blocks')) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT
					b.block_id, b.user_id, b.icon, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
					bt.lang, bt.title, bp.name, bp.value
				FROM {db_prefix}lp_blocks AS b
					LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
					LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
				WHERE b.status = {int:status}
				ORDER BY b.placement, b.priority',
				array(
					'status' => self::STATUS_ACTIVE
				)
			);

			$active_blocks = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				censorText($row['content']);

				if (!isset($active_blocks[$row['block_id']]))
					$active_blocks[$row['block_id']] = array(
						'id'            => $row['block_id'],
						'user_id'       => $row['user_id'],
						'icon'          => $row['icon'],
						'type'          => $row['type'],
						'content'       => $row['content'],
						'placement'     => $row['placement'],
						'priority'      => $row['priority'],
						'permissions'   => $row['permissions'],
						'areas'         => explode(',', $row['areas']),
						'title_class'   => $row['title_class'],
						'title_style'   => $row['title_style'],
						'content_class' => $row['content_class'],
						'content_style' => $row['content_style'],
					);

				$active_blocks[$row['block_id']]['title'][$row['lang']] = $row['title'];

				if (!empty($row['name']))
					$active_blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('active_blocks', $active_blocks);
		}

		return $active_blocks;
	}

	/**
	 * @return array
	 */
	private function getFilteredByAreas(): array
	{
		global $context, $modSettings;

		$area = $context['current_action'] ?: (!empty($modSettings['lp_frontpage_mode']) ? LP_ACTION : 'forum');

		if (!empty($modSettings['lp_standalone_mode']) && !empty($modSettings['lp_standalone_url'])) {
			if ($modSettings['lp_standalone_url'] === Helpers::request()->url()) {
				$area = LP_ACTION;
			} elseif (empty($context['current_action'])) {
				$area = 'forum';
			}
		}

		if (!empty($context['current_board']) || !empty($context['lp_page']))
			$area = '';

		if (!empty($context['lp_page']) && Helpers::isFrontPage($context['lp_page']['alias']))
			$area = LP_ACTION;

		return array_filter($context['lp_active_blocks'], function($block) use ($context, $area) {
			$temp_areas     = $block['areas'];
			$block['areas'] = array_flip($block['areas']);

			if (isset($block['areas']['all']) || isset($block['areas'][$area]))
				return true;

			if (!empty($context['lp_page']) && (isset($block['areas']['pages']) || isset($block['areas'][LP_PAGE_PARAM . '=' . $context['lp_page']['alias']])))
				return true;

			if (empty($context['current_board']))
				return false;

			if (isset($block['areas']['boards']) || (!empty($context['current_topic']) && isset($block['areas']['topics'])))
				return true;

			$boards = $topics = [];
			foreach ($temp_areas as $areas) {
				$entity = explode('=', $areas);

				if ($entity[0] === 'board')
					$boards = $this->getAllowedIds($entity[1]);

				if ($entity[0] === 'topic')
					$topics = $this->getAllowedIds($entity[1]);
			}

			return in_array($context['current_board'], $boards) || (!empty($context['current_topic']) && in_array($context['current_topic'], $topics));
		});
	}

	/**
	 * @param string $entity
	 * @return array
	 */
	private function getAllowedIds(string $entity = ''): array
	{
		$ids = [];

		$items = explode('|', $entity);
		foreach ($items as $item) {
			if (strpos($item, '-') !== false) {
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
}
