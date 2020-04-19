<?php

namespace Bugo\LightPortal\Addons\UserInfo;

use Bugo\LightPortal\Helpers;

/**
 * UserInfo
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

class UserInfo
{
	/**
	 * Get the current user info
	 *
	 * Получаем информацию о пользователе
	 *
	 * @return array
	 */
	public static function getUserInfo()
	{
		global $memberContext, $user_info;

		if (!isset($memberContext[$user_info['id']])) {
			loadMemberData($user_info['id']);
			loadMemberContext($user_info['id'], true);
		}

		return $memberContext[$user_info['id']];
	}

	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time)
	{
		global $context, $txt, $scripturl, $boarddir;

		if ($type !== 'user_info')
			return;

		ob_start();

		if ($context['user']['is_logged']) {
			$userData = Helpers::getFromCache('user_info_addon_u' . $context['user']['id'], 'getUserInfo', __CLASS__, $cache_time);

			echo '
			<ul class="centertext">
				<li>', $txt['hello_member'], ' <strong>', $userData['name_color'], '</strong></li>';

			if (!empty($userData['avatar'])) {
				echo '
				<li style="margin: 1em">', $userData['avatar']['image'], '</li>';
			}

			$fa = false;

			echo '
				<li>', $userData['primary_group'] ?: ($userData['post_group'] ?: ''), '</li>
				<li>', $userData['group_icons'], '</li>
				<li>
					<hr>
					<span class="floatleft">
						', $fa ? '<i class="fas fa-user"></i>' : '<span class="main_icons members"></span>', ' <a href="', $userData['href'], '">', $txt['profile'], '</a>
					</span>
					<span class="floatright">
					', $fa ? '<i class="fas fa-sign-out-alt"></i>' : '<span class="main_icons logout"></span>', ' <a href="', $scripturl, '?action=logout;', $context['session_var'], '=', $context['session_id'], '">', $txt['logout'], '</a>
					</span>
				</li>
			</ul>';
		} else {
			require_once($boarddir . '/SSI.php');
			ssi_welcome();
		}

		$content = ob_get_clean();
	}
}
