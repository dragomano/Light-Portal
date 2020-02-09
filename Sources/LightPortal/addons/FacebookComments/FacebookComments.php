<?php

namespace Bugo\LightPortal\Addons\FacebookComments;

/**
 * FacebookComments
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class FacebookComments
{
	/**
	 * Widget color scheme (light|dark)
	 *
	 * Цветовая схема виджета (light|dark)
	 *
	 * @var string
	 */
	private static $color_scheme = 'light';

	/**
	 * Adding the new comment type
	 *
	 * Добавляем новый тип комментариев
	 *
	 * @return void
	 */
	public static function init()
	{
		global $txt;

		$txt['lp_show_comment_block_set']['facebook'] = 'Facebook';
	}

	/**
	 * Добавляем настройки
	 *
	 * @param array $settings
	 * @return void
	 */
	public static function addSettings(&$settings)
	{
		global $modSettings, $txt;

		if (!isset($modSettings['lp_facebook_addon_color_scheme']))
			$modSettings['lp_facebook_addon_color_scheme'] = static::$color_scheme;

		$settings[] = array('select', 'lp_facebook_addon_color_scheme', $txt['lp_facebook_addon_color_scheme_set']);
	}

	/**
	 * Adding Facebook comment block
	 *
	 * Добавляем блок комментариев Facebook
	 *
	 * @return void
	 */
	public static function comments()
	{
		global $modSettings, $context, $txt;

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'facebook') {
			$context['lp_facebook_comment_block'] = '
		<div id="fb-root"></div>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . $txt['lang_locale'] . '/sdk.js#xfbml=1&version=v6.0"></script>
		<div class="fb-comments" data-href="' . $context['canonical_url'] . '" data-numposts="' . ($modSettings['lp_num_comments_per_page'] ?? 10) . '" data-width="100%" data-colorscheme="' . ($modSettings['lp_facebook_addon_color_scheme'] ?? static::$color_scheme) . '"></div>';
		}
	}
}
