<?php

namespace Bugo\LightPortal;

/**
 * DebugInfo.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class DebugInfo
{
	/**
	 * Show the script execution time and the number of the portal queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 *
	 * @return void
	 */
	public function __invoke()
	{
		global $modSettings, $context, $txt, $smcFunc;

		if (empty($modSettings['lp_show_debug_info']) || empty($context['user']['is_admin']) || empty($context['template_layers']))
			return;

		$context['lp_load_page_stats'] = sprintf($txt['lp_load_page_stats'], round(microtime(true) - $context['lp_load_time'], 3), $smcFunc['lp_num_queries']);

		loadTemplate('LightPortal/ViewDebug');

		if (empty($key = array_search('lp_portal', $context['template_layers']))) {
			$context['template_layers'][] = 'debug';
			return;
		}

		$context['template_layers'] = array_merge(
			array_slice($context['template_layers'], 0, $key, true),
			array('debug'),
			array_slice($context['template_layers'], $key, null, true)
		);
	}
}
