<?php declare(strict_types=1);

/**
 * @package Disqus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace Bugo\LightPortal\Plugins\Disqus;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::COMMENT)]
class Disqus extends Plugin
{
	public function init(): void
	{
		Lang::$txt['lp_comment_block_set'][$this->name] = 'Disqus';
	}

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = [
			'text',
			'shortname',
			'subtext' => $this->txt['shortname_subtext'],
			'required' => true,
		];
	}

	public function comments(): void
	{
		if (Setting::getCommentBlock() !== $this->name || empty($this->context['shortname']))
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
					s.src = "https://' . $this->context['shortname'] . '.disqus.com/embed.js";
					s.setAttribute("data-timestamp", +new Date());
					(d.head || d.body).appendChild(s);
				})();
			</script>';
	}

	public function frontAssets(): void
	{
		if (Setting::getCommentBlock() !== $this->name || empty(Utils::$context['lp_frontpage_articles']))
			return;

		if (empty($this->context['shortname']))
			return;

		Theme::loadJavaScriptFile(
			'https://' . $this->context['shortname'] . '.disqus.com/count.js',
			[
				'async' => true,
				'external' => true,
				'attributes' => [
					'id' => 'dsq-count-scr'
				]
			]
		);

		foreach (Utils::$context['lp_frontpage_articles'] as $id => $page) {
			Utils::$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' ' .
				Str::html('i', ['class' => 'fas fa-comment']) . ' ' .
				Str::html('span', [
					'class' => 'disqus-comment-count',
					'data-disqus-identifier' => $id,
				]);
		}
	}
}
