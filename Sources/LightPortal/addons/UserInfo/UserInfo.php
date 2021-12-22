<?php

/**
 * UserInfo.php
 *
 * @package UserInfo (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\UserInfo;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class UserInfo extends Plugin
{
	public string $icon = 'fas fa-user';

	public function blockOptions(array &$options)
	{
		$options['user_info']['parameters']['use_fa_icons'] = true;
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'user_info')
			return;

		$parameters['use_fa_icons'] = FILTER_VALIDATE_BOOLEAN;
	}

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
				'checked' => ! empty($context['lp_block']['options']['parameters']['use_fa_icons'])
			),
			'tab' => 'appearance'
		);
	}

	public function getData(): array
    {
        global $user_info, $memberContext;

        $loadedUserIds = loadMemberData($user_info['id']);

        if (! isset($memberContext[$user_info['id']]) && in_array($user_info['id'], $loadedUserIds)) {
            try {
                loadMemberContext($user_info['id']);
            } catch (\Exception $e) {
                log_error('[LP] UserInfo addon: ' . $e->getMessage(), 'user');
            }
        }

		return $memberContext[$user_info['id']];
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $context, $txt, $scripturl, $modSettings;

		if ($type !== 'user_info')
			return;

		$fa = ! empty($parameters['use_fa_icons']);

		if ($context['user']['is_logged']) {
			$userData = Helper::cache('user_info_addon_u' . $context['user']['id'])
				->setLifeTime($cache_time)
				->setFallback(__CLASS__, 'getData');

			echo '
			<ul class="centertext">
				<li>', $txt['hello_member'], ' <strong>', $userData['name_color'], '</strong></li>';

			if (! empty($userData['avatar'])) {
				echo '
				<li>', $userData['avatar']['image'], '</li>';
			}

			echo '
				<li>', $userData['primary_group'] ?: ($userData['post_group'] ?: ''), '</li>
				<li>', $userData['group_icons'], '</li>';

			if (! empty($context['allow_light_portal_manage_own_blocks'])) {
				echo '
				<li>
					<hr>
					', $fa ? '<i class="fas fa-plus-circle"></i>' : '<span class="main_icons post_moderation_allow"></span>', ' <a href="', $scripturl, '?action=admin;area=lp_blocks;sa=add;', $context['session_var'], '=', $context['session_id'], '">
						', $txt['lp_blocks_add'], '
					</a>
				</li>';
			}

			if (! empty($context['allow_light_portal_manage_own_pages'])) {
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
			echo '
			<ul class="centertext">
				<li>', $txt['hello_member'], ' ', $txt['guest'], '</li>
				<li><img alt="*" src="', $modSettings['avatar_url'], '/default.png', '"></li>
				<li>';

			if ($context['can_register']) {
				echo '
					<span class="floatleft">
						', $fa ? '<i class="fas fa-user-plus"></i>' : '<span class="main_icons signup"></span>', ' <a href="', $scripturl, '?action=signup">', $txt['register'], '</a>
					</span>';
			}

			echo '
					<span', $context['can_register'] ? ' class="floatright"' : '', '>
						', $fa ? '<i class="fas fa-sign-in-alt"></i>' : '<span class="main_icons login"></span>', ' <a href="', $scripturl, '?action=login" onclick="return reqOverlayDiv(this.href, ', JavaScriptEscape($txt['login']), ');">', $txt['login'], '</a>
					</span>
				</li>
			</ul>';
		}
	}
}
