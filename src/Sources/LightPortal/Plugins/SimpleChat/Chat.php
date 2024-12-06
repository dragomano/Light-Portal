<?php

/**
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\SimpleChat;

use Bugo\Compat\BBCodeParser;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Time;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\RequestTrait;

use function json_encode;
use function time;

if (! defined('LP_NAME'))
	die('No direct access...');

class Chat
{
	use CacheTrait;
	use RequestTrait;

	public function __construct(private readonly string $name) {}

	public function prepareTable(): void
	{
		$tables = [];

		if (! empty(Db::$db->list_tables(false, Config::$db_prefix . 'lp_simple_chat_messages')))
			return;

		$tables[] = [
			'name' => 'lp_simple_chat_messages',
			'columns' => [
				[
					'name'     => 'id',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true,
					'auto'     => true
				],
				[
					'name'     => 'block_id',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true
				],
				[
					'name'     => 'user_id',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true
				],
				[
					'name' => 'message',
					'type' => 'varchar',
					'size' => 255,
					'null' => false
				],
				[
					'name'     => 'created_at',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true,
					'default'  => 0
				]
			],
			'indexes' => [
				[
					'type'    => 'primary',
					'columns' => ['id']
				]
			]
		];

		foreach ($tables as $table) {
			Db::$db->create_table('{db_prefix}' . $table['name'], $table['columns'], $table['indexes']);
		}
	}

	public function getMessages(int $block_id = 0): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT chat.id, chat.block_id, chat.user_id, chat.message, chat.created_at,
				mem.real_name
			FROM {db_prefix}lp_simple_chat_messages AS chat
				INNER JOIN {db_prefix}members AS mem ON (chat.user_id = mem.id_member)' . ($block_id ? '
			WHERE chat.block_id = {int:id}' : '') . '
			ORDER BY chat.created_at DESC',
			[
				'id' => $block_id,
			]
		);

		$messages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$messages[$row['block_id']][] = [
				'id'         => $row['id'],
				'block_id'   => $row['block_id'],
				'message'    => BBCodeParser::load()->parse($row['message']),
				'created_at' => Time::timeformat($row['created_at']),
				'author'     => [
					'id'   => $row['user_id'],
					'name' => $row['real_name'],
				],
			];
		}

		Db::$db->free_result($result);

		return $messages[$block_id] ?? [];
	}

	public function addMessage(): void
	{
		$data = $this->request()->json();

		if (empty($data['message']))
			return;

		$id = Db::$db->insert('',
			'{db_prefix}lp_simple_chat_messages',
			[
				'block_id'   => 'int',
				'user_id'    => 'int',
				'message'    => 'string-255',
				'created_at' => 'int'
			],
			[
				'block_id'   => $data['block_id'],
				'user_id'    => User::$info['id'],
				'message'    => $message = Utils::htmlspecialchars($data['message']),
				'created_at' => $time = time(),
			],
			['id'],
			1
		);

		$this->cache()->forget($this->name . '_addon_b' . $data['block_id']);

		$result = [
			'id'         => $id,
			'message'    => BBCodeParser::load()->parse($message),
			'created_at' => Time::timeformat($time),
			'author'     => [
				'id'     => User::$info['id'],
				'name'   => User::$info['name'],
				'avatar' => Avatar::get(User::$info['id']),
			],
		];

		exit(json_encode($result));
	}

	public function deleteMessage(): void
	{
		$data = $this->request()->json();

		if (empty($data['id']))
			return;

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_simple_chat_messages
			WHERE id = {int:id}',
			[
				'id' => $data['id'],
			]
		);

		$this->cache()->forget($this->name . '_addon_b' . $data['block_id']);

		exit;
	}
}
