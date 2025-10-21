<?php declare(strict_types=1);

/**
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 20.10.25
 */

namespace LightPortal\Plugins\SimpleChat;

use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\Compat\Time;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\Avatar;
use LightPortal\Utils\ParamWrapper;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;
use LightPortal\Utils\Traits\HasView;

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

	public function __construct(private readonly string $name, private readonly PortalSqlInterface $sql) {}

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

	public function getMessages(int $blockId): array
	{
		$select = $this->sql->select()
			->from(['chat' => 'lp_simple_chat_messages'])
			->columns(['id', 'block_id', 'user_id', 'message', 'created_at'])
			->join(['mem' => 'members'], 'chat.user_id = mem.id_member', ['real_name'])
			->order('chat.created_at DESC');

		if ($blockId) {
			$select->where(['chat.block_id' => $blockId]);
		}

		$result = $this->sql->execute($select);

		return $this->processMessages($result, $blockId);
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

	private function processMessages($result, int $blockId): array
	{
		$messages = [];

		foreach ($result as $row) {
			$messages[$row['block_id']][] = [
				'id'         => $row['id'],
				'block_id'   => $row['block_id'],
				'message'    => BBCodeParser::load()->parse($row['message']),
				'created_at' => Time::stringFromUnix($row['created_at']),
				'author'     => [
					'id'   => $row['user_id'],
					'name' => str_replace('&#39;', '\'', $row['real_name']),
				],
			];
		}

		if ($this->parameters['show_avatars'] && $messages[$blockId]) {
			$messages[$blockId] = Avatar::getWithItems($messages[$blockId]);
		}

		return $messages[$blockId] ?? [];
	}

	private function createMessage(array $data): array
	{
		$message = Utils::htmlspecialchars($data['message']);
		$time = time();

		$insert = $this->sql->insert('lp_simple_chat_messages', 'id')
			->values([
				'block_id'   => $data['block_id'],
				'user_id'    => User::$me->id,
				'message'    => $message,
				'created_at' => $time,
			]);

		$result = $this->sql->execute($insert);

		return [
			'id'         => $result->getGeneratedValue(),
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
		$delete = $this->sql->delete('lp_simple_chat_messages')->where(['id' => $id]);
		$this->sql->execute($delete);
	}
}
