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

namespace LightPortal\Utils;

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Tasks\Notifier as NotifierTask;

if (! defined('SMF'))
	die('No direct access...');

readonly class Notifier implements NotifierInterface
{
	public function __construct(private PortalSqlInterface $sql) {}

	public function notify(string $type, string $action, array $options = []): void
	{
		if (empty($options))
			return;

		$insert = $this->sql->insert('background_tasks')
			->values([
				'task_file'  => '$sourcedir/LightPortal/Tasks/Notifier.php',
				'task_class' => '\\' . NotifierTask::class,
				'task_data'  => Utils::$smcFunc['json_encode']([
					'time'              => $options['time'],
					'sender_id'	        => User::$me->id,
					'sender_name'       => User::$me->name,
					'content_author_id' => $options['author_id'],
					'content_type'      => $type,
					'content_id'        => $options['item'],
					'content_action'    => $action,
					'extra'             => Utils::$smcFunc['json_encode']([
						'content_subject' => $options['title'],
						'content_link'    => $options['url'],
						'sender_gender'   => $this->getUserGender()
					], JSON_UNESCAPED_SLASHES)
				]),
			]);

		$this->sql->execute($insert);
	}

	protected function getUserGender(): string
	{
		if (empty(User::$profiles[User::$me->id]))
			return 'male';

		return isset(User::$profiles[User::$me->id]['options']['cust_gender'])
			&& User::$profiles[User::$me->id]['options']['cust_gender'] === '{gender_2}'
				? 'female' : 'male';
	}
}
