<?php

/**
 * FacebookComments.php
 *
 * @package FacebookComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.12.23
 */

namespace Bugo\LightPortal\Addons\FacebookComments;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class FacebookComments extends Plugin
{
	public string $type = 'comment';

	private array $sort_order = ['reverse-time', 'time'];

	public function init(): void
	{
		$this->txt['lp_show_comment_block_set']['facebook'] = 'Facebook';
	}

	public function addSettings(array &$config_vars): void
	{
		$this->addDefaultValues([
			'app_id'            => $this->modSettings['optimus_fb_appid'] ?? '',
			'comments_per_page' => 10,
			'comment_order_by'  => 'reverse-time',
		]);

		$config_vars['facebook_comments'][] = ['text', 'app_id', 'subtext' => $this->txt['lp_facebook_comments']['app_id_subtext']];
		$config_vars['facebook_comments'][] = ['int', 'comments_per_page'];
		$config_vars['facebook_comments'][] = ['select', 'comment_order_by', array_combine($this->sort_order, $this->txt['lp_facebook_comments']['comment_order_by_set'])];
		$config_vars['facebook_comments'][] = ['multiselect', 'dark_themes', $this->getForumThemes()];
	}

	public function comments(): void
	{
		if (! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'facebook') {
			$this->context['lp_facebook_comment_block'] = /** @lang text */ '
				<div id="fb-root"></div>
				<script>
					window.fbAsyncInit = function() {
						FB.init({
							appId: "'. ($this->context['lp_facebook_comments_plugin']['app_id'] ?? '') . '",
							xfbml: true,
							version: "v18.0"
						});
					};
				</script>
				<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . $this->txt['lang_locale'] . '/sdk.js"></script>
				<div class="fb-comments" data-href="' . $this->context['canonical_url'] . '" data-numposts="' . ($this->context['lp_facebook_comments_plugin']['comments_per_page'] ?? 10) . '" data-width="100%" data-colorscheme="' . ($this->isDarkTheme($this->context['lp_facebook_comments_plugin']['dark_themes']) ? 'dark' : 'light') . '"' . (empty($this->context['lp_facebook_comments_plugin']['comment_order_by']) ? '' : (' data-order-by="' . $this->context['lp_facebook_comments_plugin']['comment_order_by'] . '"')) . ' data-lazy="true"></div>';
		}
	}

	public function frontAssets(): void
	{
		if (empty($this->context['lp_frontpage_articles']) || empty($this->modSettings['lp_show_comment_block']) || $this->modSettings['lp_show_comment_block'] !== 'facebook')
			return;

		foreach ($this->context['lp_frontpage_articles'] as $id => $page) {
			$this->context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-comment"></i> <span class="fb-comments-count" data-href="' . $page['link'] . '"></span>';
		}
	}
}
