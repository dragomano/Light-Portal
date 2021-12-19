<?php

/**
 * Disqus.php
 *
 * @package Disqus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\Disqus;

use Bugo\LightPortal\Addons\Plugin;

class Disqus extends Plugin
{
	public string $type = 'comment';

	public function init()
	{
		global $txt;

		$txt['lp_show_comment_block_set']['disqus'] = 'Disqus';
	}

	public function addSettings(array &$config_vars)
	{
		global $txt;

		$config_vars['disqus'][] = array('text', 'shortname', 'subtext' => $txt['lp_disqus']['shortname_subtext']);
	}

	public function comments()
	{
		global $modSettings, $context;

		if (! empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] === 'disqus' && ! empty($modSettings['lp_disqus_addon_shortname'])) {
			$context['lp_disqus_comment_block'] = '
				<div id="disqus_thread" class="windowbg"></div>
				<script>
					let disqus_config = function () {
						this.page.url = "' . $context['canonical_url'] . '";
						this.page.identifier = "' . $context['lp_page']['id'] . '";
					};
					(function () {
						let d = document, s = d.createElement("script");
						s.src = "https://' . $modSettings['lp_disqus_addon_shortname'] . '.disqus.com/embed.js";
						s.setAttribute("data-timestamp", +new Date());
						(d.head || d.body).appendChild(s);
					})();
				</script>';
		}
	}

	public function frontAssets()
	{
		global $context, $modSettings;

		if (empty($context['lp_frontpage_articles']))
			return;

		if (empty($modSettings['lp_show_comment_block']) || $modSettings['lp_show_comment_block'] !== 'disqus' || empty($modSettings['lp_disqus_addon_shortname']))
			return;

		loadJavaScriptFile(
			'https://' . $modSettings['lp_disqus_addon_shortname'] . '.disqus.com/count.js',
			array(
				'external' => true,
				'async' => true,
				'attributes' => array(
					'id' => 'dsq-count-scr'
				)
			)
		);

		foreach ($context['lp_frontpage_articles'] as $id => $page) {
			$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-comment"></i> <span class="disqus-comment-count" data-disqus-identifier="' . $id . '"></span>';
		}
	}
}
