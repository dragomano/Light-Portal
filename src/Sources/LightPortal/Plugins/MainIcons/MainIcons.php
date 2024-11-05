<?php

/**
 * @package MainIcons (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\MainIcons;

use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class MainIcons extends Plugin
{
	public string $type = 'icons';

	private string $prefix = 'main_icons ';

	public function prepareIconList(Event $e): void
	{
		if (($mainIcons = $this->cache()->get('all_main_icons', 30 * 24 * 60 * 60)) === null) {
			$set = $this->getIconSet();

			$mainIcons = [];
			foreach ($set as $icon) {
				$mainIcons[] = $this->prefix . $icon;
			}

			$this->cache()->put('all_main_icons', $mainIcons, 30 * 24 * 60 * 60);
		}

		$e->args->icons = array_merge($e->args->icons, $mainIcons);
	}

	private function getIconSet(): array
	{
		return [
			'admin',
			'administration',
			'alerts',
			'approve',
			'attachment',
			'ban',
			'boards',
			'calendar_export',
			'calendar_modify',
			'calendar',
			'calendar',
			'change_menu',
			'change_menu2',
			'check',
			'close',
			'corefeatures',
			'current_theme',
			'delete',
			'details',
			'disable',
			'drafts',
			'engines',
			'error',
			'exit',
			'features',
			'filter',
			'folder',
			'frenemy',
			'gender_1',
			'gender_2',
			'general',
			'help',
			'history',
			'home',
			'ignore',
			'im_off',
			'im_on',
			'inbox',
			'invalid',
			'languages',
			'last_post',
			'left_arrow',
			'like',
			'liked_users',
			'lock',
			'logout',
			'logs',
			'mail_new',
			'mail',
			'maintain',
			'manlabels',
			'members_delete',
			'members_request',
			'members_watched',
			'members',
			'merge',
			'mlist',
			'moderate',
			'modifications',
			'modify_button',
			'move',
			'news',
			'next_page',
			'notify_button',
			'package_ops',
			'packages',
			'paid',
			'permissions',
			'personal_message',
			'plus',
			'poll',
			'post_moderation_allow',
			'post_moderation_attach',
			'post_moderation_deny',
			'post_moderation_moderate',
			'posters',
			'posts',
			'previous_page',
			'quick_edit_button',
			'quote_selected',
			'quote',
			'read_button',
			'regcenter',
			'remove_button',
			'replied',
			'replies',
			'reply_all_button',
			'reports',
			'restore_button',
			'right_arrow',
			'scheduled',
			'search',
			'security',
			'select_above',
			'select_below',
			'select_here',
			'send',
			'sent',
			'server',
			'signup',
			'smiley',
			'sort_down',
			'sort_up',
			'split_button',
			'split_desel',
			'split_sel',
			'starters',
			'stats',
			'sticky',
			'support',
			'switch',
			'themes',
			'topics_replies',
			'topics_views',
			'unapprove_button',
			'unlike',
			'unread_button',
			'valid',
			'views',
			'warning_moderate',
			'warning_mute',
			'warning_watch',
			'warning',
			'watch',
			'www'
		];
	}
}
