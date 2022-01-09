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
 * @version 09.01.22
 */

namespace Bugo\LightPortal\Addons\UserInfo;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

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
		if ($this->context['lp_block']['type'] !== 'user_info')
			return;

		$this->context['posting_fields']['use_fa_icons']['label']['text'] = $this->txt['lp_user_info']['use_fa_icons'];
		$this->context['posting_fields']['use_fa_icons']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'use_fa_icons',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['use_fa_icons']
			],
			'tab' => 'appearance'
		];
	}

	public function changeIconSet(array &$set)
	{
		$set['plus_circle']  = 'fas fa-plus-circle';
		$set['sign_out_alt'] = 'fas fa-sign-out-alt';
		$set['sign_in_alt']  = 'fas fa-sign-in-alt';
		$set['user_plus']    = 'fas fa-user_plus';
	}

	public function getData(): array
	{
		$loadedUserIds = loadMemberData($this->user_info['id']);

		if (! isset($this->memberContext[$this->user_info['id']]) && in_array($this->user_info['id'], $loadedUserIds)) {
			try {
				loadMemberContext($this->user_info['id']);
			} catch (\Exception $e) {
				log_error('[LP] UserInfo addon: ' . $e->getMessage(), 'user');
			}
		}

		return $this->memberContext[$this->user_info['id']];
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'user_info')
			return;

		if ($this->context['user']['is_logged']) {
			$userData = $this->cache('user_info_addon_u' . $this->context['user']['id'])
				->setLifeTime($cache_time)
				->setFallback(__CLASS__, 'getData');

			echo '
			<ul class="centertext">
				<li>', $this->txt['hello_member'], ' <strong>', $userData['name_color'], '</strong></li>';

			if ($userData['avatar']) {
				echo '
				<li>', $userData['avatar']['image'], '</li>';
			}

			echo '
				<li>', $userData['primary_group'] ?: ($userData['post_group'] ?: ''), '</li>
				<li>', $userData['group_icons'], '</li>';

			if ($this->context['allow_light_portal_manage_own_blocks']) {
				echo '
				<li>
					<hr>
					', $parameters['use_fa_icons'] ? $this->context['lp_icon_set']['plus_circle'] : '<span class="main_icons post_moderation_allow"></span>', ' <a href="', $this->scripturl, '?action=admin;area=lp_blocks;sa=add;', $this->context['session_var'], '=', $this->context['session_id'], '">
						', $this->txt['lp_blocks_add'], '
					</a>
				</li>';
			}

			if ($this->context['allow_light_portal_manage_own_pages']) {
				echo '
				<li>
					<hr>
					', $parameters['use_fa_icons'] ? $this->context['lp_icon_set']['plus_circle'] : '<span class="main_icons post_moderation_allow"></span>', ' <a href="', $this->scripturl, '?action=admin;area=lp_pages;sa=add;', $this->context['session_var'], '=', $this->context['session_id'], '">
						', $this->txt['lp_pages_add'], '
					</a>
				</li>';
			}

			echo '
				<li>
					<hr>
					<span class="floatleft">
						', $parameters['use_fa_icons'] ? $this->context['lp_icon_set']['user'] : '<span class="main_icons members"></span>', ' <a href="', $userData['href'], '">', $this->txt['profile'], '</a>
					</span>
					<span class="floatright">
						', $parameters['use_fa_icons'] ? $this->context['lp_icon_set']['sign_out_alt'] : '<span class="main_icons logout"></span>', ' <a href="', $this->scripturl, '?action=logout;', $this->context['session_var'], '=', $this->context['session_id'], '">', $this->txt['logout'], '</a>
					</span>
				</li>
			</ul>';
		} else {
			echo '
			<ul class="centertext">
				<li>', $this->txt['hello_member'], ' ', $this->txt['guest'], '</li>
				<li><img alt="*" src="', $this->modSettings['avatar_url'], '/default.png', '"></li>
				<li>';

			if ($this->context['can_register']) {
				echo '
					<span class="floatleft">
						', $parameters['use_fa_icons'] ? $this->context['lp_icon_set']['user_plus'] : '<span class="main_icons signup"></span>', ' <a href="', $this->scripturl, '?action=signup">', $this->txt['register'], '</a>
					</span>';
			}

			echo '
					<span', $this->context['can_register'] ? ' class="floatright"' : '', '>
						', $parameters['use_fa_icons'] ? $this->context['lp_icon_set']['sign_in_alt'] : '<span class="main_icons login"></span>', ' <a href="', $this->scripturl, '?action=login" onclick="return reqOverlayDiv(this.href, ', JavaScriptEscape($this->txt['login']), ');">', $this->txt['login'], '</a>
					</span>
				</li>
			</ul>';
		}
	}
}
