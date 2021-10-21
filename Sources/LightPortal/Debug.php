<?php

namespace Bugo\LightPortal;

/**
 * Debug.php
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

class Debug
{
	/**
	 * Show script execution time and num queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 *
	 * @return void
	 */
	public function showInfo()
	{
		global $modSettings, $context, $txt, $smcFunc;

		if (empty($modSettings['lp_show_debug_info']) || empty($context['user']['is_admin']) || empty($context['template_layers']))
			return;

		$context['lp_load_page_stats'] = sprintf($txt['lp_load_page_stats'], round(microtime(true) - $context['lp_load_time'], 3), $smcFunc['lp_num_queries']);

		loadTemplate('LightPortal/ViewDebug');

		$key = array_search('lp_portal', $context['template_layers']);
		if (empty($key)) {
			$context['template_layers'][] = 'debug';
		} else {
			$context['template_layers'] = array_merge(
				array_slice($context['template_layers'], 0, $key, true),
				array('debug'),
				array_slice($context['template_layers'], $key, null, true)
			);
		}
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int $ttl
	 * @return void
	 */
	public function cachePutData(&$key, &$value, &$ttl)
	{
		global $modSettings, $context, $txt;

		if (empty($modSettings['lp_show_cache_info']) || empty($modSettings['cache_enable']) || strpos($key, 'lp_') !== 0)
			return;

		$context['lp_detail_cache_info'][] = array(
			'title'   => sprintf($txt['lp_cache_saving'], $key, $ttl),
			'details' => json_encode(json_decode($value, true), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
			'level'   => 'notice'
		);
	}

	/**
	 * @param string $key
	 * @param int $ttl
	 * @param mixed $value
	 * @return void
	 */
	public function cacheGetData(&$key, &$ttl, &$value)
	{
		global $modSettings, $context, $txt;

		if (empty($modSettings['lp_show_cache_info']) || empty($modSettings['cache_enable']) || strpos($key, 'lp_') !== 0)
			return;

		$context['lp_detail_cache_info'][] = array(
			'title'   => sprintf($txt['lp_cache_loading'], $key),
			'details' => json_encode(json_decode($value, true), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
			'level'   => 'info'
		);
	}
}
