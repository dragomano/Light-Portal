<?php declare(strict_types=1);

/**
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 24.09.25
 */

namespace Bugo\LightPortal\Plugins\SimpleChat;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\Compat\Time;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;
use Bugo\LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
	die('No direct access...');

class Chat
{
	use HasCache;
	use HasRequest;
	use HasResponse;
	use HasView;

	private bool $inSidebar = false;

	private ParamWrapper $parameters;

	public function __construct(private readonly string $name) {}

	public function setInSidebar(bool $inSidebar): self
	{
		$this->inSidebar = $inSidebar;

		return $this;
	}

	public function setParameters(ParamWrapper $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}

	public function prepareTable(): void
	{
		if (! empty(Db::$db->list_tables(false, Config::$db_prefix . 'lp_simple_chat_messages')))
			return;

		$this->createChatTable();
	}

	public function getMessages(int $blockId): array
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT chat.id, chat.block_id, chat.user_id, chat.message, chat.created_at, mem.real_name
			FROM {db_prefix}lp_simple_chat_messages AS chat
				INNER JOIN {db_prefix}members AS mem ON (chat.user_id = mem.id_member)' . ($blockId ? '
			WHERE chat.block_id = {int:id}' : '') . '
			ORDER BY chat.created_at DESC',
			[
				'id' => $blockId,
			]
		);

		$messages = $this->processMessages($result, $blockId);

		Db::$db->free_result($result);

		return $messages;
	}

	public function addMessage(): void
	{
		$data = $this->request()->all();

		if (empty($data['message']))
			return;

		$message = $this->createMessage($data);

		$this->cache()->forget($this->name . '_addon_b' . $data['block_id']);
		$this->renderMessage($message, (int) $data['block_id']);

		http_response_code(200);

		exit();
	}

	public function deleteMessage(): void
	{
		$data = $this->request()->all();

		if (empty($data['id']))
			return;

		$this->removeMessageFromDatabase((int) $data['id']);
		$this->cache()->forget($this->name . '_addon_b' . $data['block_id']);

		$messages = $this->getMessages((int) $data['block_id']);
		$this->renderMessages($messages, (int) $data['block_id']);
	}

	public function renderMessages(array $messages, int $blockId): never
	{
		foreach ($messages as $message) {
			$this->renderMessage($message, $blockId);
		}

		http_response_code(200);

		exit();
	}

	public function renderMessage(array $message, int $blockId): void
	{
		echo $this->view('message', [
			'id'          => $blockId,
			'message'     => $message,
			'baseUrl'     => LP_BASE_URL,
			'isInSidebar' => $this->inSidebar,
			'parameters'  => $this->parameters
		]);
	}

	private function createChatTable(): void
	{
		$table = [
			'name' => 'lp_simple_chat_messages',
			'columns' => [
				[
					'name'     => 'id',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true,
					'auto'     => true,
				],
				[
					'name'     => 'block_id',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true,
				],
				[
					'name'     => 'user_id',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true,
				],
				[
					'name' => 'message',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
				],
				[
					'name'     => 'created_at',
					'type'     => 'int',
					'size'     => 10,
					'unsigned' => true,
					'default'  => 0,
				]
			],
			'indexes' => [
				[
					'type'    => 'primary',
					'columns' => ['id'],
				]
			]
		];

		Db::$db->create_table('{db_prefix}' . $table['name'], $table['columns'], $table['indexes']);
	}

	private function processMessages($result, int $blockId): array
	{
		$messages = [];

		while ($row = Db::$db->fetch_assoc($result)) {
			$messages[$row['block_id']][] = [
				'id'         => $row['id'],
				'block_id'   => $row['block_id'],
				'message'    => BBCodeParser::load()->parse($row['message']),
				'created_at' => Time::stringFromUnix($row['created_at']),
				'author'     => [
					'id'   => (int) $row['user_id'],
					'name' => str_replace('&#39;', '\'', $row['real_name']),
				],
			];
		}

		if ($this->parameters['show_avatars']) {
			if ($messages[$blockId]) {
				$messages[$blockId] = Avatar::getWithItems($messages[$blockId]);
			} else {
				$messages = Avatar::getWithItems($messages);
			}
		}

		return $messages[$blockId] ?? [];
	}

	private function createMessage(array $data): array
	{
		$id = Db::$db->insert('',
			'{db_prefix}lp_simple_chat_messages',
			[
				'block_id'   => 'int',
				'user_id'    => 'int',
				'message'    => 'string-255',
				'created_at' => 'int',
			],
			[
				'block_id'   => $data['block_id'],
				'user_id'    => User::$me->id,
				'message'    => $message = Utils::htmlspecialchars($data['message']),
				'created_at' => $time = time(),
			],
			['id'],
			1
		);

		return [
			'id'         => $id,
			'message'    => BBCodeParser::load()->parse($message),
			'created_at' => Time::stringFromUnix($time),
			'author'     => [
				'id'     => User::$me->id,
				'name'   => str_replace('&#39;', '\'', User::$me->name),
				'avatar' => Avatar::get(User::$me->id),
			],
		];
	}

	private function removeMessageFromDatabase(int $id): void
	{
		Db::$db->query('
            DELETE FROM {db_prefix}lp_simple_chat_messages
            WHERE id = {int:id}',
			[
				'id' => $id,
			]
		);
	}
}
