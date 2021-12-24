<?php

declare(strict_types = 1);

/**
 * Notify.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Tasks;

final class Notify extends \SMF_BackgroundTask
{
	/**
	 * Performing the task of notifying subscribers about new comments to portal pages
	 *
	 * Выполнение задачи оповещений подписчиков о новых комментариях к страницам портала
	 */
	public function execute(): bool
	{
		global $sourcedir, $user_profile, $smcFunc;

		require_once $sourcedir . '/Subs-Members.php';
		$members = membersAllowedTo('light_portal_view');

		$this->_details['content_type'] === 'new_comment'
			? $members = array_intersect($members, [$this->_details['author_id']])
			: $members = array_intersect($members, [$this->_details['commentator_id']]);

		// Don't alert the comment author | Не будем уведомлять сами себя, ок?
		if (! empty($this->_details['sender_id']))
			$members = array_diff($members, array($this->_details['sender_id']));

		require_once $sourcedir . '/Subs-Notify.php';
		$prefs = getNotifyPrefs($members, $this->_details['content_type'] === 'new_comment' ? 'page_comment' : 'page_comment_reply', true);

		if (! empty($this->_details['sender_id']) && empty($this->_details['sender_name'])) {
			loadMemberData($this->_details['sender_id'], false, 'minimal');

			empty($user_profile[$this->_details['sender_id']])
				? $this->_details['sender_id'] = 0
				: $this->_details['sender_name'] = $user_profile[$this->_details['sender_id']]['real_name'];
		}

		$alert_bits = array(
			'alert' => self::RECEIVE_NOTIFY_ALERT
		);

		$notifies = [];
		foreach ($prefs as $member => $pref_option) {
			foreach ($alert_bits as $type => $bitvalue) {
				if ($this->_details['content_type'] == 'new_comment') {
					if ($pref_option['page_comment'] & $bitvalue) {
						$notifies[$type][] = $member;
					}
				} elseif ($pref_option['page_comment_reply'] & $bitvalue) {
					$notifies[$type][] = $member;
				}
			}
		}

		if (! empty($notifies['alert'])) {
			$insert_rows = [];
			foreach ($notifies['alert'] as $member) {
				$insert_rows[] = array(
					'alert_time'        => $this->_details['time'],
					'id_member'         => $member,
					'id_member_started' => $this->_details['sender_id'],
					'member_name'       => $this->_details['sender_name'],
					'content_type'      => $this->_details['content_type'],
					'content_id'        => $this->_details['content_id'],
					'content_action'    => $this->_details['content_action'],
					'is_read'           => 0,
					'extra'             => $this->_details['extra']
				);
			}

			if (! empty($insert_rows)) {
				$smcFunc['db_insert']('',
					'{db_prefix}user_alerts',
					array(
						'alert_time'        => 'int',
						'id_member'         => 'int',
						'id_member_started' => 'int',
						'member_name'       => 'string',
						'content_type'      => 'string',
						'content_id'        => 'int',
						'content_action'    => 'string',
						'is_read'           => 'int',
						'extra'             => 'string'
					),
					$insert_rows,
					array('id_alert')
				);

				updateMemberData($notifies['alert'], array('alerts' => '+'));
			}
		}

		return true;
	}
}
