<?php

/**
 * @package Disqus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\Disqus;

use Bugo\Compat\{Lang, Theme, Utils};
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Setting;

if (! defined('LP_NAME'))
	die('No direct access...');

class Disqus extends Plugin
{
	public string $type = 'comment';

	public function init(): void
	{
		Lang::$txt['lp_comment_block_set']['disqus'] = 'Disqus';
	}

	public function addSettings(Event $e): void
	{
		$e->args->settings['disqus'][] = [
			'text',
			'shortname',
			'subtext' => Lang::$txt['lp_disqus']['shortname_subtext'],
			'required' => true
		];
	}

	public function comments(): void
	{
		if (Setting::getCommentBlock() !== 'disqus' || empty(Utils::$context['lp_disqus_plugin']['shortname']))
			return;

		Utils::$context['lp_disqus_comment_block'] = /** @lang text */ '
			<div id="disqus_thread" class="windowbg"></div>
			<script>
				let disqus_config = function () {
					this.page.url = "' . Utils::$context['canonical_url'] . '";
					this.page.identifier = "' . Utils::$context['lp_page']['id'] . '";
				};
				(function () {
					let d = document, s = d.createElement("script");
					s.src = "https://' . Utils::$context['lp_disqus_plugin']['shortname'] . '.disqus.com/embed.js";
					s.setAttribute("data-timestamp", +new Date());
					(d.head || d.body).appendChild(s);
				})();
			</script>';
	}

	public function frontAssets(): void
	{
		if (Setting::getCommentBlock() !== 'disqus' || empty(Utils::$context['lp_frontpage_articles']))
			return;

		if (empty(Utils::$context['lp_disqus_plugin']['shortname']))
			return;

		Theme::loadJavaScriptFile(
			'https://' . Utils::$context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js',
			[
				'async' => true,
				'external' => true,
				'attributes' => [
					'id' => 'dsq-count-scr'
				]
			]
		);

		foreach (Utils::$context['lp_frontpage_articles'] as $id => $page) {
			Utils::$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-comment"></i> <span class="disqus-comment-count" data-disqus-identifier="' . $id . '"></span>';
		}
	}
}
