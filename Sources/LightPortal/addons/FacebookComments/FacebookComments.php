<?php

/**
 * FacebookComments
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\FacebookComments;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class FacebookComments extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'comment';

	/**
	 * Adding the new comment type
	 *
	 * Добавляем новый тип комментариев
	 *
	 * @return void
	 */
	public function init()
	{
		global $txt;

		$txt['lp_show_comment_block_set']['facebook'] = 'Facebook';
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(array &$config_vars)
	{
		global $modSettings;

		if (!isset($modSettings['lp_facebook_comments_addon_num_comments_per_page']))
			updateSettings(array('lp_facebook_comments_addon_num_comments_per_page' => 10));
		if (!isset($modSettings['lp_facebook_comments_addon_dark_themes']))
			updateSettings(array('lp_facebook_comments_addon_dark_themes' => ''));

		$config_vars['facebook_comments'][] = array('int', 'num_comments_per_page');
		$config_vars['facebook_comments'][] = array('multicheck', 'dark_themes', Helpers::getForumThemes());
	}

	/**
	 * Adding comment block
	 *
	 * Добавляем блок комментариев
	 *
	 * @return void
	 */
	public function comments()
	{
		global $modSettings, $context, $txt, $settings;

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'facebook') {
			$dark_themes = !empty($modSettings['lp_facebook_comments_addon_dark_themes']) ? json_decode($modSettings['lp_facebook_comments_addon_dark_themes'], true) : [];

			$context['lp_facebook_comment_block'] = '
				<div id="fb-root"></div>
				<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . $txt['lang_locale'] . '/sdk.js#xfbml=1&version=v6.0"></script>
				<div class="fb-comments" data-href="' . $context['canonical_url'] . '" data-numposts="' . ($modSettings['lp_facebook_comments_addon_num_comments_per_page'] ?? 10) . '" data-width="100%" data-colorscheme="' . (!empty($dark_themes) && !empty($dark_themes[$settings['theme_id']]) ? 'dark' : 'light') . '"></div>';
		}
	}
}
