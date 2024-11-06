<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\{Db, User, Utils};
use Bugo\LightPortal\Tasks\Notifier;

if (! defined('SMF'))
	die('No direct access...');

class Notify
{
	public static function send(string $type, string $action, array $options = []): void
	{
		if (empty($options))
			return;

		Db::$db->insert('',
			'{db_prefix}background_tasks',
			[
				'task_file'  => 'string',
				'task_class' => 'string',
				'task_data'  => 'string'
			],
			[
				'task_file'  => '$sourcedir/LightPortal/Tasks/Notifier.php',
				'task_class' => '\\' . Notifier::class,
				'task_data'  => Utils::$smcFunc['json_encode']([
					'time'              => $options['time'],
					'sender_id'	        => User::$info['id'],
					'sender_name'       => User::$info['name'],
					'content_author_id' => $options['author_id'],
					'content_type'      => $type,
					'content_id'        => $options['item'],
					'content_action'    => $action,
					'extra'             => Utils::$smcFunc['json_encode']([
						'content_subject' => $options['title'],
						'content_link'    => $options['url'],
						'sender_gender'   => self::getUserGender()
					], JSON_UNESCAPED_SLASHES)
				]),
			],
			['id_task']
		);
	}

	protected static function getUserGender(): string
	{
		if (empty(User::$profiles[User::$info['id']]))
			return 'male';

		return isset(User::$profiles[User::$info['id']]['options']['cust_gender'])
			&& User::$profiles[User::$info['id']]['options']['cust_gender'] === '{gender_2}'
				? 'female' : 'male';
	}
}
