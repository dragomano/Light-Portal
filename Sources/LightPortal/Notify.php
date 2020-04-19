<?php

namespace Bugo\LightPortal;

/**
 * Notify.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

class Notify extends \SMF_BackgroundTask
{
	/**
	 * Performing the task of notifying subscribers about new comments to portal pages
	 *
	 * Выполнение задачи оповещений подписчиков о новых комментариях к страницам портала
	 *
	 * @return bool
	 */
	public function execute()
	{
		global $sourcedir, $smcFunc;

		require_once($sourcedir . '/Subs-Members.php');
		$members = membersAllowedTo('light_portal_view');

		require_once($sourcedir . '/Subs-Notify.php');
		$prefs = getNotifyPrefs($members, $this->_details['content_type'] == 'new_comment' ? 'page_comment' : 'page_comment_reply', true);

		$alert_bits = array(
			'alert' => self::RECEIVE_NOTIFY_ALERT
		);
		$notifies = [];

		foreach ($prefs as $member => $pref_option) {
			foreach ($alert_bits as $type => $bitvalue) {
				if ($this->_details['content_type'] == 'new_comment') {
					if ($pref_option['page_comment'] & $bitvalue)
						$notifies[$type][] = $member;
				} elseif ($pref_option['page_comment_reply'] & $bitvalue)
					$notifies[$type][] = $member;
			}
		}

		if (!empty($notifies['alert'])) {
			$insert_rows = [];
			foreach ($notifies['alert'] as $member) {
				$insert_rows[] = array(
					'alert_time'        => $this->_details['time'],
					'id_member'         => $member,
					'id_member_started' => $this->_details['member_id'],
					'member_name'       => $this->_details['member_name'],
					'content_type'      => $this->_details['content_type'],
					'content_id'        => $this->_details['content_id'],
					'content_action'    => $this->_details['content_action'],
					'is_read'           => 0,
					'extra'             => $this->_details['extra']
				);
			}

			if (!empty($insert_rows)) {
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
