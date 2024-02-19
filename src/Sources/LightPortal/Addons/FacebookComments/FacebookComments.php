<?php

/**
 * FacebookComments.php
 *
 * @package FacebookComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Addons\FacebookComments;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class FacebookComments extends Plugin
{
	public string $type = 'comment';

	private array $sortOrder = ['reverse-time', 'time'];

	public function init(): void
	{
		Lang::$txt['lp_show_comment_block_set']['facebook'] = 'Facebook';
	}

	public function addSettings(array &$config_vars): void
	{
		$this->addDefaultValues([
			'app_id'            => Config::$modSettings['optimus_fb_appid'] ?? '',
			'comments_per_page' => 10,
			'comment_order_by'  => 'reverse-time',
		]);

		$config_vars['facebook_comments'][] = [
			'text',
			'app_id',
			'subtext' => Lang::$txt['lp_facebook_comments']['app_id_subtext']
		];
		$config_vars['facebook_comments'][] = ['int', 'comments_per_page'];
		$config_vars['facebook_comments'][] = [
			'select',
			'comment_order_by',
			array_combine($this->sortOrder, Lang::$txt['lp_facebook_comments']['comment_order_by_set'])
		];
		$config_vars['facebook_comments'][] = ['multiselect', 'dark_themes', $this->getForumThemes()];
	}

	public function comments(): void
	{
		if (! empty(Config::$modSettings['lp_show_comment_block']) && Config::$modSettings['lp_show_comment_block'] === 'facebook') {
			Utils::$context['lp_facebook_comment_block'] = /** @lang text */ '
				<div id="fb-root"></div>
				<script>
					window.fbAsyncInit = function() {
						FB.init({
							appId: "'. (Utils::$context['lp_facebook_comments_plugin']['app_id'] ?? '') . '",
							xfbml: true,
							version: "v18.0"
						});
					};
				</script>
				<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . Lang::$txt['lang_locale'] . '/sdk.js"></script>
				<div
					class="fb-comments"
					data-href="' . Utils::$context['canonical_url'] . '"
					data-numposts="' . (Utils::$context['lp_facebook_comments_plugin']['comments_per_page'] ?? 10) . '"
					data-width="100%"
					data-colorscheme="' . ($this->isDarkTheme(Utils::$context['lp_facebook_comments_plugin']['dark_themes']) ? 'dark' : 'light') . '"' . (empty(Utils::$context['lp_facebook_comments_plugin']['comment_order_by']) ? '' : ('
					data-order-by="' . Utils::$context['lp_facebook_comments_plugin']['comment_order_by'] . '"')) . '
					data-lazy="true"
				></div>';
		}
	}

	public function frontAssets(): void
	{
		if (empty(Utils::$context['lp_frontpage_articles']) || empty(Config::$modSettings['lp_show_comment_block']) || Config::$modSettings['lp_show_comment_block'] !== 'facebook')
			return;

		foreach (Utils::$context['lp_frontpage_articles'] as $id => $page) {
			Utils::$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-comment"></i> <span class="fb-comments-count" data-href="' . $page['link'] . '"></span>';
		}
	}
}
