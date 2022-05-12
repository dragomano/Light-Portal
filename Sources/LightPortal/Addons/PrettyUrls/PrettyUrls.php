<?php

/**
 * PrettyUrls.php
 *
 * @package PrettyUrls (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 11.05.22
 */

namespace Bugo\LightPortal\Addons\PrettyUrls;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class PrettyUrls extends Plugin
{
	public string $type = 'seo';

	public function init()
	{
		if (! is_file($file = $this->sourcedir . '/Subs-PrettyUrls.php'))
			return;

		if (! empty($this->context['pretty']['action_array']) && ! in_array(LP_ACTION, array_values($this->context['pretty']['action_array'])))
			$this->context['pretty']['action_array'][] = LP_ACTION;

		$prettyFilters = unserialize($this->modSettings['pretty_filters']);

		if (isset($prettyFilters['lp-pages']))
			return;

		require_once $file;

		$prettyFilters['lp-pages'] = [
			'description' => 'Rewrite Light Portal pages URLs',
			'enabled' => 0,
			'filter' => [
				'priority' => 30,
				'callback' => [$this, 'filter'],
			],
			'rewrite' => [
				'priority' => 30,
				'rule' => 'RewriteRule ^page/([^/]+)/?$ ./index.php?pretty;' . LP_PAGE_PARAM . '=$1 [L,QSA]',
			],
			'title' => '<a href="https://custom.simplemachines.org/mods/index.php?mod=4244" target="_blank" rel="noopener">Light Portal</a> pages',
		];

		$this->updateSettings(['pretty_filters' => serialize($prettyFilters)]);

		if (function_exists('pretty_update_filters'))
			pretty_update_filters();
	}

	public function filter(array $urls): array
	{
		$pattern = '`' . $this->scripturl . '(.*)' . LP_PAGE_PARAM . '=([^;]+)`S';
		$replacement = $this->boardurl . '/' . LP_PAGE_PARAM . '/$2/$1';

		foreach ($urls as $url_id => $url) {
			if (! isset($url['replacement']) && preg_match($pattern, $url['url'])) {
				$urls[$url_id]['replacement'] = preg_replace($pattern, $replacement, $url['url']);
			}
		}

		return $urls;
	}
}
