<?php

/**
 * Disqus.php
 *
 * @package Disqus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\Disqus;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Disqus extends Plugin
{
	public string $type = 'comment';

	public function init(): void
	{
		$this->txt['lp_show_comment_block_set']['disqus'] = 'Disqus';
	}

	public function addSettings(array &$config_vars): void
	{
		$config_vars['disqus'][] = ['text', 'shortname', 'subtext' => $this->txt['lp_disqus']['shortname_subtext'], 'required' => true];
	}

	public function comments(): void
	{
		if (! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'disqus' && ! empty($this->context['lp_disqus_plugin']['shortname'])) {
			$this->context['lp_disqus_comment_block'] = /** @lang text */
				'
				<div id="disqus_thread" class="windowbg"></div>
				<script>
					let disqus_config = function () {
						this.page.url = "' . $this->context['canonical_url'] . '";
						this.page.identifier = "' . $this->context['lp_page']['id'] . '";
					};
					(function () {
						let d = document, s = d.createElement("script");
						s.src = "https://' . $this->context['lp_disqus_plugin']['shortname'] . '.disqus.com/embed.js";
						s.setAttribute("data-timestamp", +new Date());
						(d.head || d.body).appendChild(s);
					})();
				</script>';
		}
	}

	public function frontAssets(): void
	{
		if (empty($this->context['lp_frontpage_articles']))
			return;

		if (empty($this->modSettings['lp_show_comment_block']) || $this->modSettings['lp_show_comment_block'] !== 'disqus' || empty($this->context['lp_disqus_plugin']['shortname']))
			return;

		$this->loadExtJS(
			'https://' . $this->context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js',
			[
				'async' => true,
				'attributes' => [
					'id' => 'dsq-count-scr'
				]
			]
		);

		foreach ($this->context['lp_frontpage_articles'] as $id => $page) {
			$this->context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-comment"></i> <span class="disqus-comment-count" data-disqus-identifier="' . $id . '"></span>';
		}
	}
}
