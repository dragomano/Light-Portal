<?php

namespace Bugo\LightPortal;

/**
 * Block.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
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
	 * @param string $area
	 * @return void
	 */
	public static function show(string $area = 'portal')
	{
		global $context, $modSettings;

		if (empty($context['template_layers']))
			return;

		$blocks = array_filter($context['lp_active_blocks'], function($block) use ($area) {
			$block['areas'] = array_flip($block['areas']);
			return isset($block['areas']['all']) || isset($block['areas'][$area]) || !empty($_GET['page']) && isset($block['areas']['page=' . (string) $_GET['page']]);
		});

		if (empty($blocks) || (!empty($modSettings['lp_hide_blocks_in_admin_section']) && $context['current_action'] == 'admin'))
			return;

		foreach ($blocks as $item => $data) {
			if (Helpers::canShowItem($data['permissions']) === false)
				continue;

			if (empty($data['title'][Helpers::getUserLanguage()]))
				$data['title_class'] = '';

			if (empty($data['content']))
				Subs::prepareContent($data['content'], $data['type'], $data['id'], 3600);
			else
				Subs::parseContent($data['content'], $data['type']);

			$context['lp_blocks'][$data['placement']][$item] = $data;
			$icon = Helpers::getIcon($context['lp_blocks'][$data['placement']][$item]['icon'], $context['lp_blocks'][$data['placement']][$item]['icon_type']);
			$context['lp_blocks'][$data['placement']][$item]['title'] = $icon . $context['lp_blocks'][$data['placement']][$item]['title'][Helpers::getUserLanguage()];
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
}
