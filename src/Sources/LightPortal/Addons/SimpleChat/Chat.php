<?php

/**
 * Chat.php
 *
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 12.05.23
 */

namespace Bugo\LightPortal\Addons\SimpleChat;

use Bugo\LightPortal\Helper;

if (! defined('LP_NAME'))
	die('No direct access...');

class Chat
{
	use Helper;

	public function getMessages(int $block_id = 0): array
	{
		$result = $this->smcFunc['db_query']('', '
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
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			$messages[$row['block_id']][] = [
				'id'         => $row['id'],
				'block_id'   => $row['block_id'],
				'message'    => parse_bbc($row['message']),
				'created_at' => timeformat($row['created_at']),
				'author'     => [
					'id'   => $row['user_id'],
					'name' => $row['real_name'],
				],
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $messages[$block_id] ?? [];
	}

	public function addMessage(): void
	{
		$data = $this->request()->json();

		if (empty($data['message']))
			return;

		$id = $this->smcFunc['db_insert']('',
			'{db_prefix}lp_simple_chat_messages',
			[
				'block_id'   => 'int',
				'user_id'    => 'int',
				'message'    => 'string-255',
				'created_at' => 'int'
			],
			[
				'block_id'   => $data['block_id'],
				'user_id'    => $this->user_info['id'],
				'message'    => $message = $this->smcFunc['htmlspecialchars']($data['message']),
				'created_at' => $time = time()
			],
			['id'],
			1
		);

		$this->context['lp_num_queries']++;

		$this->cache()->forget('simple_chat_addon_b' . $data['block_id']);

		$result = [
			'id'         => $id,
			'message'    => parse_bbc($message),
			'created_at' => timeformat($time),
			'author'     => [
				'id'     => $this->user_info['id'],
				'name'   => $this->user_info['name'],
				'avatar' => $this->getUserAvatar($this->user_info['id']),
			],
		];

		exit(json_encode($result));
	}

	public function deleteMessage(): void
	{
		$data = $this->request()->json();

		if (empty($data['id']))
			return;

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_simple_chat_messages
			WHERE id = {int:id}',
			[
				'id' => $data['id'],
			]
		);

		$this->context['lp_num_queries']++;

		$this->cache()->forget('simple_chat_addon_b' . $data['block_id']);

		exit;
	}
}
