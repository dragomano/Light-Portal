<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Tasks;

use Bugo\Compat\Actions\Notify;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Mail;
use Bugo\Compat\Tasks\BackgroundTask;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\AlertAction;
use Bugo\LightPortal\Enums\NotifyType;
use ErrorException;

use function array_diff;
use function array_intersect;

final class Notifier extends BackgroundTask
{
	public function execute(): bool
	{
		$members = match ($this->_details['content_type']) {
			NotifyType::NEW_PAGE->name() => User::getAllowedTo('light_portal_manage_pages_any'),
			default => array_intersect(
				User::getAllowedTo('light_portal_view'), [$this->_details['content_author_id']]
			)
		};

		// Let's not notify ourselves, okay?
		if ($this->_details['sender_id']) {
			$members = array_diff($members, [$this->_details['sender_id']]);
		}

		$prefs = Notify::getNotifyPrefs($members, match ($this->_details['content_type']) {
			NotifyType::NEW_COMMENT->name() => AlertAction::PAGE_COMMENT->name(),
			NotifyType::NEW_MENTION->name() => AlertAction::PAGE_COMMENT_MENTION->name(),
			NotifyType::NEW_REPLY->name()   => AlertAction::PAGE_COMMENT_REPLY->name(),
			default                         => AlertAction::PAGE_UNAPPROVED->name()
		}, true);

		if ($this->_details['sender_id'] && empty($this->_details['sender_name'])) {
			User::load($this->_details['sender_id'], dataset: 'minimal');

			empty(User::$profiles[$this->_details['sender_id']])
				? $this->_details['sender_id']   = 0
				: $this->_details['sender_name'] = User::$profiles[$this->_details['sender_id']]['real_name'];
		}

		$notifies = $this->getNotifies($prefs);

		$this->addAlerts($notifies);

		try {
			$this->sendEmails($notifies);
		} catch (ErrorException $e) {
			ErrorHandler::log("[LP] notifications: {$e->getMessage()}");
		}

		return true;
	}

	protected function getNotifies(array $prefs): array
	{
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

		return $notifies;
	}

	protected function addAlerts(array $notifies): void
	{
		if (empty($notifies['alert']))
			return;

		$rows = [];
		foreach ($notifies['alert'] as $member) {
			$rows[] = [
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

		if ($rows) {
			$this->insertRows($rows);

			User::updateMemberData($notifies['alert'], ['alerts' => '+']);
		}
	}

	/**
	 * @throws ErrorException
	 */
	protected function sendEmails(array $notifies): void
	{
		if (empty($notifies['email']))
			return;

		Theme::loadEssential();

		$emails = $this->getMemberEmails($notifies);

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

	private function insertRows(array $rows): void
	{
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
			$rows,
			['id_alert']
		);
	}

	private function getMemberEmails(array $notifies): array
	{
		$result = Db::$db->query('
			SELECT id_member, lngfile, email_address
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:members})',
			[
				'members' => $notifies['email'],
			]
		);

		$emails = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if (empty($row['lngfile'])) {
				$row['lngfile'] = Config::$language;
			}

			$emails[$row['lngfile']][$row['id_member']] = $row['email_address'];
		}

		Db::$db->free_result($result);

		return $emails;
	}
}
