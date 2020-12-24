<?php

namespace Bugo\LightPortal\Addons\FacebookComments;

use Bugo\LightPortal\Helpers;

/**
 * FacebookComments
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class FacebookComments
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public $addon_type = 'comment';

	/**
	 * The IDs list of dark themes
	 *
	 * Список идентификаторов тёмных тем оформления
	 *
	 * @var string
	 */
	private $dark_themes = '';

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
	public function addSettings(&$config_vars)
	{
		global $modSettings, $context;

		if (!isset($modSettings['lp_facebook_comments_addon_dark_themes']))
			updateSettings(array('lp_facebook_comments_addon_dark_themes' => $this->dark_themes));

		$context['lp_facebook_comments_addon_dark_themes_options'] = Helpers::getForumThemes();

		$config_vars[] = array('multicheck', 'lp_facebook_comments_addon_dark_themes');
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
				<div class="fb-comments" data-href="' . $context['canonical_url'] . '" data-numposts="' . ($modSettings['lp_num_comments_per_page'] ?? 10) . '" data-width="100%" data-colorscheme="' . (!empty($dark_themes) && !empty($dark_themes[$settings['theme_id']]) ? 'dark' : 'light') . '"></div>';
		}
	}
}
