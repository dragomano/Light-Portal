<?php

namespace Bugo\LightPortal;

/**
 * Block.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.0
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
	public static function show()
	{
		global $context, $modSettings;

		if (empty($context['template_layers']) || empty($context['lp_active_blocks']))
			return;

		$blocks = self::getFilteredByAreas();

		if (empty($blocks) || (!empty($modSettings['lp_hide_blocks_in_admin_section']) && $context['current_action'] == 'admin'))
			return;

		// Block placement
		foreach ($blocks as $item => $data) {
			if (Helpers::canViewItem($data['permissions']) === false)
				continue;

			if (empty($data['content']))
				Subs::prepareContent($data['content'], $data['type'], $data['id'], LP_CACHE_TIME);
			else
				Subs::parseContent($data['content'], $data['type']);

			if (empty($data['title'][$context['user']['language']]))
				$data['title'][$context['user']['language']] = $context['lp_active_blocks'][$data['id']]['title'][$context['user']['language']] ?? '';

			if (empty($title = Helpers::getPublicTitle($data)))
				$data['title_class'] = '';

			$context['lp_blocks'][$data['placement']][$item] = $data;
			$icon = Helpers::getIcon($context['lp_blocks'][$data['placement']][$item]['icon'], $context['lp_blocks'][$data['placement']][$item]['icon_type']);
			$context['lp_blocks'][$data['placement']][$item]['title'] = $icon . $title;
		}

		loadTemplate('LightPortal/ViewBlock');

		$counter = 0;
		foreach ($context['template_layers'] as $position => $name) {
			$counter++;
			if ($name == 'body')
				break;
		}

		$context['template_layers'] = array_merge(
			array_slice($context['template_layers'], 0, $counter, true),
			array('portal'),
			array_slice($context['template_layers'], $counter, null, true)
		);
	}

	/**
	 * Get blocks filtered by areas
	 *
	 * Получаем блоки, отфильтрованные по области
	 *
	 * @return array
	 */
	private static function getFilteredByAreas()
	{
		global $context, $modSettings;

		$area = $context['current_action'] ?: (!empty($modSettings['lp_frontpage_mode']) ? 'portal' : 'forum');

		if (!empty($modSettings['lp_standalone_mode']) && !empty($modSettings['lp_standalone_url'])) {
			if (empty($context['current_action']))
				$area = 'forum';
			if ($modSettings['lp_standalone_url'] == $_SERVER['REQUEST_URL'])
				$area = 'portal';
		}

		if (!empty($_REQUEST['board']) || !empty($_REQUEST['topic']))
			$area = 'forum';

		if (empty($context['current_action']) && !empty($_REQUEST['page']))
			$area = 'portal';

		return array_filter($context['lp_active_blocks'], function($block) use ($area) {
			global $context;

			$temp_areas     = $block['areas'];
			$block['areas'] = array_flip($block['areas']);

			if (isset($block['areas']['all']) || isset($block['areas'][$area]))
				return true;

			if ($area == 'portal' && !empty($_GET['page']) && isset($block['areas']['page=' . (string) $_GET['page']]))
				return true;

			if (empty($context['current_board']))
				return false;

			if (isset($block['areas']['boards']) || (!empty($context['current_topic']) && isset($block['areas']['topics'])))
				return true;

			$boards = $topics = [];
			foreach ($temp_areas as $areas) {
				$entity = explode('=', $areas);

				if ($entity[0] === 'board')
					$boards = self::getAllowedIds($entity[1]);
				if ($entity[0] === 'topic')
					$topics = self::getAllowedIds($entity[1]);
			}

			return in_array($context['current_board'], $boards) || (!empty($context['current_topic']) && in_array($context['current_topic'], $topics));
		});
	}

	/**
	 * Get allowed identifiers for $entity string
	 *
	 * Получаем разрешенные идентификаторы из строки $entity
	 *
	 * @param string $entity
	 * @return array
	 */
	private static function getAllowedIds(string $entity = '')
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
