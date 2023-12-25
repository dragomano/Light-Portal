<?php declare(strict_types=1);

/**
 * Notifier.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Tasks;

use ErrorException;

final class Notifier extends BackgroundTask
{
	/**
	 * @throws ErrorException
	 */
	public function execute(): bool
	{
		$members = match ($this->_details['content_type']) {
			'new_page' => $this->membersAllowedTo('light_portal_manage_pages_any'),
			default    => array_intersect($this->membersAllowedTo('light_portal_view'), [$this->_details['content_author_id']])
		};

		// Let's not notify ourselves, okay?
		if ($this->_details['sender_id'])
			$members = array_diff($members, [$this->_details['sender_id']]);

		$prefs = $this->getNotifyPrefs($members, match ($this->_details['content_type']) {
			'new_comment' => 'page_comment',
			'new_reply'   => 'page_comment_reply',
			default       => 'page_unapproved'
		}, true);

		if ($this->_details['sender_id'] && empty($this->_details['sender_name'])) {
			$this->loadMemberData($this->_details['sender_id'], set: 'minimal');

			empty($this->user_profile[$this->_details['sender_id']])
				? $this->_details['sender_id']   = 0
				: $this->_details['sender_name'] = $this->user_profile[$this->_details['sender_id']]['real_name'];
		}

		$alert_bits = [
			'alert' => self::RECEIVE_NOTIFY_ALERT,
			'email' => self::RECEIVE_NOTIFY_EMAIL,
		];

		$notifies = [];
		foreach ($prefs as $member => $pref_option) {
			foreach ($alert_bits as $type => $bitvalue) {
				foreach (['page_comment', 'page_comment_reply', 'page_unapproved'] as $option) {
					if (isset($pref_option[$option]) && ($pref_option[$option] & $bitvalue)) {
						$notifies[$type][] = $member;
					}
				}
			}
		}

		if (! empty($notifies['alert'])) {
			$insert_rows = [];
			foreach ($notifies['alert'] as $member) {
				$insert_rows[] = [
					'alert_time'        => $this->_details['time'],
					'id_member'         => $member,
					'id_member_started' => $this->_details['sender_id'],
					'member_name'       => $this->_details['sender_name'],
					'content_type'      => $this->_details['content_type'],
					'content_id'        => $this->_details['content_id'],
					'content_action'    => $this->_details['content_action'],
					'is_read'           => 0,
					'extra'             => $this->_details['extra']
				];
			}

			if ($insert_rows) {
				$this->smcFunc['db_insert']('',
					'{db_prefix}user_alerts',
					[
						'alert_time'        => 'int',
						'id_member'         => 'int',
						'id_member_started' => 'int',
						'member_name'       => 'string',
						'content_type'      => 'string',
						'content_id'        => 'int',
						'content_action'    => 'string',
						'is_read'           => 'int',
						'extra'             => 'string'
					],
					$insert_rows,
					['id_alert']
				);

				$this->updateMemberData($notifies['alert'], ['alerts' => '+']);
			}
		}

		if (! empty($notifies['email'])) {
			$this->loadEssential();

			$emails = [];
			$result = $this->smcFunc['db_query']('', '
				SELECT id_member, lngfile, email_address
				FROM {db_prefix}members
				WHERE id_member IN ({array_int:members})',
				[
					'members' => $notifies['email'],
				]
			);

			while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
				if (empty($row['lngfile']))
					$row['lngfile'] = $this->language;

				$emails[$row['lngfile']][$row['id_member']] = $row['email_address'];
			}

			$this->smcFunc['db_free_result']($result);

			foreach ($emails as $this_lang => $recipients) {
				$replacements = [
					'MEMBERNAME'  => $this->_details['sender_name'],
					'PROFILELINK' => $this->scripturl . '?action=profile;u=' . $this->_details['sender_id'],
					'PAGELINK'    => $this->jsonDecode($this->_details['extra'], logIt: false)['content_link'],
				];

				$this->loadLanguage('LightPortal/LightPortal', $this_lang);

				$emaildata = $this->loadEmailTemplate('page_unapproved', $replacements, empty($this->modSettings['userLanguage']) ? $this->language : $this_lang, false);

				foreach ($recipients as $email_address)
					$this->sendmail($email_address, $emaildata['subject'], $emaildata['body'], null, 'page#' . $this->_details['content_id'], $emaildata['is_html'], 2);
			}
		}

		return true;
	}
}
