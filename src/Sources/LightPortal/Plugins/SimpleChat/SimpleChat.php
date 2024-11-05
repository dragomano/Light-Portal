<?php

/**
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\SimpleChat;

use Bugo\Compat\{Lang, Theme, Utils};
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\RadioField;
use Bugo\LightPortal\Enums\{Hook, Tab};
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\Avatar;

use function array_combine;
use function json_encode;
use function show_chat_block;

use const FILTER_DEFAULT;
use const FILTER_VALIDATE_BOOLEAN;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class SimpleChat extends Block
{
	public string $icon = 'fas fa-message';

	private array $params = [
		'show_avatars'  => false,
		'form_position' => 'bottom',
	];

	private readonly Chat $chat;

	public function __construct()
	{
		$this->chat = new Chat;
	}

	public function init(): void
	{
		$this->applyHook(Hook::actions);
	}

	public function actions(): void
	{
		if ($this->request()->isNot('portal'))
			return;

		if ($this->request()->has('chat') && $this->request('chat') === 'post') {
			$this->chat->addMessage();
		}

		if ($this->request()->has('chat') && $this->request('chat') === 'update') {
			$this->chat->deleteMessage();
		}
	}

	public function addSettings(): void
	{
		$this->chat->prepareTable();
	}

	public function prepareBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_chat')
			return;

		$e->args->params = $this->params;
	}

	public function validateBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_chat')
			return;

		$e->args->params = [
			'show_avatars'  => FILTER_VALIDATE_BOOLEAN,
			'form_position' => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_chat')
			return;

		CheckboxField::make('show_avatars', Lang::$txt['lp_simple_chat']['show_avatars'])
			->setTab(Tab::APPEARANCE)
			->setValue(Utils::$context['lp_block']['options']['show_avatars']);

		RadioField::make('form_position', Lang::$txt['lp_simple_chat']['form_position'])
			->setOptions(array_combine(['bottom', 'top'], Lang::$txt['lp_simple_chat']['form_position_set']))
			->setValue(Utils::$context['lp_block']['options']['form_position']);
	}

	public function getData(int $block_id, array $parameters): array
	{
		$messages = $this->chat->getMessages($block_id);

		if ($parameters['show_avatars']) {
			$messages = Avatar::getWithItems($messages);
		}

		return $messages;
	}

	public function prepareContent(Event $e): void
	{
		[$data, $parameters] = [$e->args->data, $e->args->parameters];

		if ($data->type !== 'simple_chat')
			return;

		Theme::loadCSSFile('admin.css');
		Theme::loadJavaScriptFile('light_portal/bundle.min.js', ['defer' => true]);

		$parameters['show_avatars'] ??= $this->params['show_avatars'];
		$parameters['form_position'] ??= $this->params['form_position'];

		$messages = $this->cache('simple_chat_addon_b' . $data->id)
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $data->id, $parameters);

		Utils::$context['lp_chats'][$data->id] = json_encode($messages, JSON_UNESCAPED_UNICODE);

		$this->setTemplate();

		show_chat_block($data->id, $parameters, $this->isInSidebar($data->id));
	}

	public function onBlockRemoving(Event $e): void
	{
		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_simple_chat_messages
			WHERE block_id IN ({array_int:items})',
			[
				'items' => $e->args->items,
			]
		);
	}
}
