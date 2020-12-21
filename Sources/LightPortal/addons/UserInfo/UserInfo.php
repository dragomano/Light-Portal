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
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class UserInfo
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public $addon_icon = 'fas fa-user';

	/**
	 * Use Font Awesome icons for decoration
	 *
	 * Использовать иконки Font Awesome для оформления
	 *
	 * @var bool
	 */
	private $use_fa_icons = true;

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['user_info']['parameters']['use_fa_icons'] = $this->use_fa_icons;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'user_info')
			return;

		$parameters['use_fa_icons'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'user_info')
			return;

		$context['posting_fields']['use_fa_icons']['label']['text'] = $txt['lp_user_info_addon_use_fa_icons'];
		$context['posting_fields']['use_fa_icons']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'use_fa_icons',
				'checked' => !empty($context['lp_block']['options']['parameters']['use_fa_icons'])
			),
			'tab' => 'appearance'
		);
	}

	/**
	 * Get the current user info
	 *
	 * Получаем информацию о пользователе
	 *
	 * @return array
	 */
	public function getData()
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
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $txt, $scripturl, $boarddir;

		if ($type !== 'user_info')
			return;

		ob_start();

		if ($context['user']['is_logged']) {
			$userData = Helpers::cache('user_info_addon_u' . $context['user']['id'], 'getData', __CLASS__, $cache_time);

			echo '
			<ul class="centertext">
				<li>', $txt['hello_member'], ' <strong style="word-break: break-all">', $userData['name_color'], '</strong></li>';

			if (!empty($userData['avatar'])) {
				echo '
				<li>', $userData['avatar']['image'], '</li>';
			}

			$fa = !empty($parameters['use_fa_icons']);

			echo '
				<li>', $userData['primary_group'] ?: ($userData['post_group'] ?: ''), '</li>
				<li>', $userData['group_icons'], '</li>';

			if ($context['allow_light_portal_manage_own_pages']) {
				echo '
				<li>
					<hr>
					', $fa ? '<i class="fas fa-plus-circle"></i>' : '<span class="main_icons post_moderation_allow"></span>', ' <a href="', $scripturl, '?action=admin;area=lp_pages;sa=add;', $context['session_var'], '=', $context['session_id'], '">
						', $txt['lp_pages_add'], '
					</a>
				</li>';
			}

			echo '
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
