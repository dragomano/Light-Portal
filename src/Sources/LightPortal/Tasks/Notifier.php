<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Tasks;

use Bugo\Compat\Actions\Notify;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Mail;
use Bugo\Compat\Tasks\BackgroundTask;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\AlertAction;
use LightPortal\Enums\NotifyType;

use function LightPortal\app;

final class Notifier extends BackgroundTask
{
	private PortalSqlInterface $sql;

	public function __construct(array $details)
	{
		parent::__construct($details);

		$this->sql = app(PortalSqlInterface::class);
	}

	public function execute(): bool
	{
			$members = match ($this->_details['content_type']) {
				NotifyType::NEW_PAGE->value => User::getAllowedTo('light_portal_manage_pages_any'),
			default => array_intersect(
				User::getAllowedTo('light_portal_view'), [$this->_details['content_author_id']]
			)
		};

		// Let's not notify ourselves, okay?
		if ($this->_details['sender_id']) {
			$members = array_diff($members, [$this->_details['sender_id']]);
		}

			$prefs = Notify::getNotifyPrefs($members, match ($this->_details['content_type']) {
				NotifyType::NEW_COMMENT->value => AlertAction::PAGE_COMMENT->value,
				NotifyType::NEW_MENTION->value => AlertAction::PAGE_COMMENT_MENTION->value,
				NotifyType::NEW_REPLY->value   => AlertAction::PAGE_COMMENT_REPLY->value,
				default                        => AlertAction::PAGE_UNAPPROVED->value
			}, true);

		if ($this->_details['sender_id'] && empty($this->_details['sender_name'])) {
			User::load($this->_details['sender_id'], dataset: 'minimal');

			empty(User::$profiles[$this->_details['sender_id']])
				? $this->_details['sender_id']   = 0
				: $this->_details['sender_name'] = User::$profiles[$this->_details['sender_id']]['real_name'];
		}

		$notifies = $this->getNotifies($prefs);

		$this->addAlerts($notifies);
		$this->sendEmails($notifies);

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
			$combinedMask = array_reduce(
				AlertAction::values(),
				static fn(int $mask, $action): int => $mask | ($prefOption[$action] ?? 0),
				0
			);

			foreach ($alertBits as $type => $bitValue) {
				if ($combinedMask & $bitValue) {
					$notifies[$type][] = $member;
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
			$insert = $this->sql->insert('user_alerts')->batch($rows);
			$this->sql->execute($insert);

			User::updateMemberData($notifies['alert'], ['alerts' => '+']);
		}
	}

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
				AlertAction::PAGE_UNAPPROVED->value,
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

	private function getMemberEmails(array $notifies): array
	{
		$select = $this->sql->select()
			->from('members')
			->columns(['id_member', 'lngfile', 'email_address']);

		$select->where->in('id_member', $notifies['email']);

		$result = $this->sql->execute($select);

		$emails = [];
		foreach ($result as $row) {
			if (empty($row['lngfile'])) {
				$row['lngfile'] = Config::$language;
			}

			$emails[$row['lngfile']][$row['id_member']] = $row['email_address'];
		}

		return $emails;
	}
}
