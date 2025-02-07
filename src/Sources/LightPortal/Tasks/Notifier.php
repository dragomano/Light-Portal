<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Tasks;

use Bugo\Compat\Actions\Notify;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Mail;
use Bugo\Compat\Tasks\BackgroundTask;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\AlertAction;
use ErrorException;

use function array_diff;
use function array_intersect;

final class Notifier extends BackgroundTask
{
	/**
	 * @throws ErrorException
	 */
	public function execute(): bool
	{
		$members = match ($this->_details['content_type']) {
			'new_page' => User::membersAllowedTo('light_portal_manage_pages_any'),
			default    => array_intersect(
				User::membersAllowedTo('light_portal_view'), [$this->_details['content_author_id']]
			)
		};

		// Let's not notify ourselves, okay?
		if ($this->_details['sender_id']) {
			$members = array_diff($members, [$this->_details['sender_id']]);
		}

		$prefs = Notify::getNotifyPrefs($members, match ($this->_details['content_type']) {
			'new_comment' => AlertAction::PAGE_COMMENT->name(),
			'new_reply'   => AlertAction::PAGE_COMMENT_REPLY->name(),
			default       => AlertAction::PAGE_UNAPPROVED->name()
		}, true);

		if ($this->_details['sender_id'] && empty($this->_details['sender_name'])) {
			User::load($this->_details['sender_id'], dataset: 'minimal');

			empty(User::$profiles[$this->_details['sender_id']])
				? $this->_details['sender_id']   = 0
				: $this->_details['sender_name'] = User::$profiles[$this->_details['sender_id']]['real_name'];
		}

		$alertBits = [
			'alert' => self::RECEIVE_NOTIFY_ALERT,
			'email' => self::RECEIVE_NOTIFY_EMAIL,
		];

		$notifies = [];
		foreach ($prefs as $member => $prefOption) {
			foreach ($alertBits as $type => $bitvalue) {
				foreach (AlertAction::names() as $action) {
					if (isset($prefOption[$action]) && ($prefOption[$action] & $bitvalue)) {
						$notifies[$type][] = $member;
					}
				}
			}
		}

		if (! empty($notifies['alert'])) {
			$insertRows = [];
			foreach ($notifies['alert'] as $member) {
				$insertRows[] = [
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

			if ($insertRows) {
				Db::$db->insert('',
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
					$insertRows,
					['id_alert']
				);

				User::updateMemberData($notifies['alert'], ['alerts' => '+']);
			}
		}

		if (! empty($notifies['email'])) {
			Theme::loadEssential();

			$emails = [];
			$result = Db::$db->query('', '
				SELECT id_member, lngfile, email_address
				FROM {db_prefix}members
				WHERE id_member IN ({array_int:members})',
				[
					'members' => $notifies['email'],
				]
			);

			while ($row = Db::$db->fetch_assoc($result)) {
				if (empty($row['lngfile'])) {
					$row['lngfile'] = Config::$language;
				}

				$emails[$row['lngfile']][$row['id_member']] = $row['email_address'];
			}

			Db::$db->free_result($result);

			foreach ($emails as $lang => $recipients) {
				$replacements = [
					'MEMBERNAME'  => $this->_details['sender_name'],
					'PROFILELINK' => Config::$scripturl . '?action=profile;u=' . $this->_details['sender_id'],
					'PAGELINK'    => Utils::jsonDecode($this->_details['extra'], true)['content_link'],
				];

				Lang::load('LightPortal/LightPortal', $lang);

				$emaildata = Mail::loadEmailTemplate(
					AlertAction::PAGE_UNAPPROVED->name(),
					$replacements,
					empty(Config::$modSettings['userLanguage']) ? Config::$language : $lang,
					false
				);

				foreach ($recipients as $email) {
					Mail::send(
						$email,
						$emaildata['subject'],
						$emaildata['body'],
						null,
						'page#' . $this->_details['content_id'],
						$emaildata['is_html'],
						2
					);
				}
			}
		}

		return true;
	}
}
