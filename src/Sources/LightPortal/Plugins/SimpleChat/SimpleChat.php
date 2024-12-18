<?php

/**
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 18.12.24
 */

namespace Bugo\LightPortal\Plugins\SimpleChat;

use Bugo\Compat\Db;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
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
		'window_height' => 100,
	];

	private readonly Chat $chat;

	public function __construct()
	{
		parent::__construct();

		$this->chat = new Chat($this->name);
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
		$e->args->params = $this->params;
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_avatars'  => FILTER_VALIDATE_BOOLEAN,
			'form_position' => FILTER_DEFAULT,
			'window_height' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CheckboxField::make('show_avatars', $this->txt['show_avatars'])
			->setTab(Tab::APPEARANCE)
			->setValue($options['show_avatars']);

		RadioField::make('form_position', $this->txt['form_position'])
			->setOptions(array_combine(['bottom', 'top'], $this->txt['form_position_set']))
			->setValue($options['form_position']);

		NumberField::make('window_height', $this->txt['window_height'])
			->setAttribute('step', 10)
			->setValue($options['window_height']);
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
		Theme::loadCSSFile('admin.css');
		Theme::loadJavaScriptFile('light_portal/bundle.min.js', ['defer' => true]);

		[$id, $parameters] = [$e->args->id, $e->args->parameters];

		$parameters['show_avatars'] ??= $this->params['show_avatars'];
		$parameters['form_position'] ??= $this->params['form_position'];
		$parameters['window_height'] ??= $this->params['window_height'];

		$messages = $this->cache($this->name . '_addon_b' . $id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', $id, $parameters);

		Utils::$context['lp_chats'][$id] = json_encode($messages, JSON_UNESCAPED_UNICODE);

		$this->setTemplate();

		show_chat_block($id, $parameters, $this->isInSidebar($id));
	}

	public function onBlockRemoving(Event $e): void
	{
		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_simple_chat_messages
			WHERE block_id IN ({array_int:items})',
			[
				'items' => $e->args->items,
			]
		);
	}
}
