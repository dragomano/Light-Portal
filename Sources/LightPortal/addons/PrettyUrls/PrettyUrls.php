<?php

/**
 * PrettyUrls
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\PrettyUrls;

use Bugo\LightPortal\Addons\Plugin;

class PrettyUrls extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'other';

	/**
	 * Give a hint to PrettyUrls mod about "action=portal"
	 *
	 * Подсказываем PrettyUrls про "action=portal"
	 *
	 * @return void
	 */
	public function init()
	{
		global $sourcedir, $context, $modSettings;

		if (!is_file($sourcedir . '/Subs-PrettyUrls.php'))
			return;

		if (!empty($context['pretty']['action_array']) && !in_array(LP_ACTION, array_values($context['pretty']['action_array'])))
			$context['pretty']['action_array'][] = LP_ACTION;

		$prettyFilters = unserialize($modSettings['pretty_filters']);

		if (isset($prettyFilters['lp-pages']))
			return;

		require_once $sourcedir . '/Subs-PrettyUrls.php';

		$prettyFilters['lp-pages'] = array(
			'description' => 'Rewrite Light Portal pages URLs',
			'enabled' => 0,
			'filter' => array(
				'priority' => 30,
				'callback' => __CLASS__ . '::filter',
			),
			'rewrite' => array(
				'priority' => 30,
				'rule' => 'RewriteRule ^page/([^/]+)/?$ ./index.php?pretty;' . LP_PAGE_ACTION . '=$1 [L,QSA]',
			),
			'title' => '<a href="https://custom.simplemachines.org/mods/index.php?mod=4244" target="_blank" rel="noopener">Light Portal</a> pages',
		);

		updateSettings(array('pretty_filters' => serialize($prettyFilters)));

		pretty_update_filters();
	}

	/**
	 * Pretty Urls Light Portal pages Filter
	 *
	 * @param array $urls
	 * @return array
	 */
	public static function filter(array $urls): array
	{
		global $scripturl, $boardurl;

		$pattern = '`' . $scripturl . '(.*)' . LP_PAGE_ACTION . '=([^;]+)`S';
		$replacement = $boardurl . '/' . LP_PAGE_ACTION . '/$2/$1';

		foreach ($urls as $url_id => $url) {
			if (!isset($url['replacement'])) {
				if (preg_match($pattern, $url['url']))
					$urls[$url_id]['replacement'] = preg_replace($pattern, $replacement, $url['url']);
			}
		}

		return $urls;
	}
}
