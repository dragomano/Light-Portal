<?php

/**
 * @package PrettyUrls (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Addons\PrettyUrls;

use Bugo\Compat\{Config, Utils};
use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class PrettyUrls extends Plugin
{
	public string $type = 'seo';

	public function init(): void
	{
		if (! is_file($file = Config::$sourcedir . '/Subs-PrettyUrls.php'))
			return;

		if (
			! empty(Utils::$context['pretty']['action_array'])
			&& ! in_array(LP_ACTION, array_values(Utils::$context['pretty']['action_array']))
		) {
			Utils::$context['pretty']['action_array'][] = LP_ACTION;
		}

		$prettyFilters = unserialize(Config::$modSettings['pretty_filters']);

		if (isset($prettyFilters['lp-pages']))
			return;

		require_once $file;

		$prettyFilters['lp-pages'] = [
			'description' => 'Rewrite URLs for Light Portal pages',
			'enabled' => 0,
			'filter' => [
				'priority' => 30,
				'callback' => $this->filter(...),
			],
			'rewrite' => [
				'priority' => 30,
				'rule' => 'RewriteRule ^' . LP_PAGE_PARAM . '/([^/]+)/?$ ./index.php?pretty;' . LP_PAGE_PARAM . '=$1 [L,QSA]',
			],
			'title' => '<a href="https://custom.simplemachines.org/mods/index.php?mod=4244" target="_blank" rel="noopener">Light Portal</a> pages',
		];

		Config::updateModSettings(['pretty_filters' => serialize($prettyFilters)]);

		if (function_exists('pretty_update_filters'))
			\pretty_update_filters();
	}

	public function filter(array $urls): array
	{
		$pattern = '`' . Config::$scripturl . '(.*)' . LP_PAGE_PARAM . '=([^;]+)`S';
		$replacement = Config::$boardurl . '/' . LP_PAGE_PARAM . '/$2/$1';

		foreach ($urls as $url_id => $url) {
			if (! isset($url['replacement']) && preg_match($pattern, (string) $url['url'])) {
				$urls[$url_id]['replacement'] = preg_replace($pattern, $replacement, (string) $url['url']);
			}
		}

		return $urls;
	}
}
