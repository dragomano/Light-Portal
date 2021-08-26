<?php

/**
 * UserInfo
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\UserInfo;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class UserInfo extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-user';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['user_info']['parameters']['use_fa_icons'] = true;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'user_info')
			return;

		$parameters['use_fa_icons'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'user_info')
			return;

		$context['posting_fields']['use_fa_icons']['label']['text'] = $txt['lp_user_info']['use_fa_icons'];
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
	 * @throws \Exception
	 */
	public function getData(): array
	{
		global $memberContext, $user_info;

		if (!isset($memberContext[$user_info['id']])) {
			loadMemberData($user_info['id']);
			loadMemberContext($user_info['id']);
		}

		return $memberContext[$user_info['id']];
	}

	/**
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $context, $txt, $scripturl, $boarddir;

		if ($type !== 'user_info')
			return;

		if ($context['user']['is_logged']) {
			$userData = Helpers::cache('user_info_addon_u' . $context['user']['id'])
				->setLifeTime($cache_time)
				->setFallback(__CLASS__, 'getData');

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

			if (!empty($context['allow_light_portal_manage_own_blocks'])) {
				echo '
				<li>
					<hr>
					', $fa ? '<i class="fas fa-plus-circle"></i>' : '<span class="main_icons post_moderation_allow"></span>', ' <a href="', $scripturl, '?action=admin;area=lp_blocks;sa=add;', $context['session_var'], '=', $context['session_id'], '">
						', $txt['lp_blocks_add'], '
					</a>
				</li>';
			}

			if (!empty($context['allow_light_portal_manage_own_pages'])) {
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
			require_once $boarddir . '/SSI.php';
			ssi_welcome();
		}
	}
}
