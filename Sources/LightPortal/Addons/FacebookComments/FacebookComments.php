<?php

/**
 * FacebookComments.php
 *
 * @package FacebookComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\FacebookComments;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class FacebookComments extends Plugin
{
	public string $type = 'comment';

	private array $sort_order = ['reverse-time', 'time'];

	public function init()
	{
		global $txt;

		$txt['lp_show_comment_block_set']['facebook'] = 'Facebook';
	}

	public function addSettings(array &$config_vars)
	{
		global $modSettings, $txt;

		$addSettings = [];
		if (! isset($modSettings['lp_facebook_comments_addon_num_comments_per_page']))
			$addSettings['lp_facebook_comments_addon_num_comments_per_page'] = 10;
		if (! isset($modSettings['lp_facebook_comments_addon_comment_order_by']))
			$addSettings['lp_facebook_comments_addon_comment_order_by'] = 'reverse-time';
		if (! isset($modSettings['lp_facebook_comments_addon_dark_themes']))
			$addSettings['lp_facebook_comments_addon_dark_themes'] = '';
		if (! empty($addSettings))
			updateSettings($addSettings);

		$config_vars['facebook_comments'][] = array('int', 'num_comments_per_page');
		$config_vars['facebook_comments'][] = array('select', 'comment_order_by', array_combine($this->sort_order, $txt['lp_facebook_comments']['comment_order_by_set']));
		$config_vars['facebook_comments'][] = array('multicheck', 'dark_themes', Helper::getForumThemes());
	}

	public function comments()
	{
		global $modSettings, $context, $txt, $settings;

		if (! empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] === 'facebook') {
			$dark_themes = empty($modSettings['lp_facebook_comments_addon_dark_themes']) ? [] : json_decode($modSettings['lp_facebook_comments_addon_dark_themes'], true);

			$context['lp_facebook_comment_block'] = '
				<div id="fb-root"></div>
				<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . $txt['lang_locale'] . '/sdk.js#xfbml=1"></script>
				<div class="fb-comments" data-href="' . $context['canonical_url'] . '" data-numposts="' . ($modSettings['lp_facebook_comments_addon_num_comments_per_page'] ?? 10) . '" data-width="100%" data-colorscheme="' . (! empty($dark_themes) && ! empty($dark_themes[$settings['theme_id']]) ? 'dark' : 'light') . '"' . (! empty($modSettings['lp_facebook_comments_addon_comment_order_by']) ? ' data-order-by="' . $modSettings['lp_facebook_comments_addon_comment_order_by'] . '"' : '') . ' data-lazy="true"></div>';
		}
	}

	public function frontAssets()
	{
		global $context, $modSettings;

		if (empty($context['lp_frontpage_articles']) || empty($modSettings['lp_show_comment_block']) || $modSettings['lp_show_comment_block'] !== 'facebook')
			return;

		foreach ($context['lp_frontpage_articles'] as $id => $page) {
			$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-comment"></i> <span class="fb-comments-count" data-href="' . $page['link'] . '"></span>';
		}
	}
}
