<?php declare(strict_types=1);

/**
 * @package FacebookComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\FacebookComments;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasThemes;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::COMMENT)]
class FacebookComments extends Plugin
{
	use HasThemes;

	private array $sortOrder = ['reverse-time', 'time'];

	public function init(): void
	{
		Lang::$txt['lp_comment_block_set']['facebook'] = 'Facebook';
	}

	public function addSettings(Event $e): void
	{
		$this->addDefaultValues([
			'app_id'            => Config::$modSettings['optimus_fb_appid'] ?? '',
			'comments_per_page' => 10,
			'comment_order_by'  => 'reverse-time',
		]);

		$settings = &$e->args->settings;

		$settings[$this->name][] = [
			'text',
			'app_id',
			'subtext' => $this->txt['app_id_subtext']
		];
		$settings[$this->name][] = ['int', 'comments_per_page'];
		$settings[$this->name][] = [
			'select',
			'comment_order_by',
			array_combine($this->sortOrder, $this->txt['comment_order_by_set'])
		];
		$settings[$this->name][] = ['multiselect', 'dark_themes', $this->getForumThemes()];
	}

	public function comments(): void
	{
		if (Setting::getCommentBlock() !== 'facebook')
			return;

		Utils::$context['lp_facebook_comment_block'] = /** @lang text */ '
			<div id="fb-root"></div>
			<script>
				window.fbAsyncInit = function() {
					FB.init({
						appId: "'. ($this->context['app_id'] ?? '') . '",
						xfbml: true,
						version: "v18.0"
					});
				};
			</script>
			<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . Lang::$txt['lang_locale'] . '/sdk.js"></script>
			<div
				class="fb-comments"
				data-href="' . Utils::$context['canonical_url'] . '"
				data-numposts="' . ($this->context['comments_per_page'] ?? 10) . '"
				data-width="100%"
				data-colorscheme="' . ($this->isDarkTheme($this->context['dark_themes']) ? 'dark' : 'light') . '"' . (empty($this->context['comment_order_by']) ? '' : ('
				data-order-by="' . $this->context['comment_order_by'] . '"')) . '
				data-lazy="true"
			></div>';
	}

	public function frontAssets(): void
	{
		if (Setting::getCommentBlock() !== 'facebook' || empty(Utils::$context['lp_frontpage_articles']))
			return;

		foreach (Utils::$context['lp_frontpage_articles'] as $id => $page) {
			Utils::$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' ' .
				Str::html('i')->class('fas fa-comment') . ' ' .
				Str::html('span', [
					'class' => 'fb-comments-count',
					'data-href' => $page['link']
				]);
		}
	}
}
