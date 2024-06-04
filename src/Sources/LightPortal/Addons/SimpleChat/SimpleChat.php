<?php

/**
 * SimpleChat.php
 *
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 02.06.24
 */

namespace Bugo\LightPortal\Addons\SimpleChat;

use Bugo\Compat\{Config, Db, Lang, Theme, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Utils\Avatar;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class SimpleChat extends Block
{
	public string $icon = 'fas fa-message';

	private readonly Chat $chat;

	public function __construct()
	{
		$this->chat = new Chat;
	}

	public function init(): void
	{
		$this->applyHook('actions');
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
		$this->prepareTable();
	}

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_chat')
			return;

		$params['show_avatars'] = false;
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_chat')
			return;

		$params['show_avatars'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_chat')
			return;

		CheckboxField::make('show_avatars', Lang::$txt['lp_simple_chat']['show_avatars'])
			->setTab(Tab::APPEARANCE)
			->setValue(Utils::$context['lp_block']['options']['show_avatars']);
	}

	public function getData(int $block_id, array $parameters): array
	{
		$messages = $this->chat->getMessages($block_id);

		if ($parameters['show_avatars']) {
			$messages = Avatar::getWithItems($messages);
		}

		return $messages;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'simple_chat')
			return;

		Theme::loadCSSFile('admin.css');
		Theme::loadJavaScriptFile('light_portal/bundle.min.js', ['defer' => true]);

		$parameters['show_avatars'] ??= false;

		$messages = $this->cache('simple_chat_addon_b' . $data->id)
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $data->id, $parameters);

		Utils::$context['lp_chats'][$data->id] = json_encode($messages, JSON_UNESCAPED_UNICODE);

		$this->setTemplate();

		show_chat_block($data->id, (bool) $parameters['show_avatars'], $this->isInSidebar($data->id));
	}

	public function onBlockRemoving(array $items): void
	{
		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_simple_chat_messages
			WHERE block_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);
	}

	private function prepareTable(): void
	{
		$tables = [];

		Db::extend('packages');

		if (! empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'lp_simple_chat_messages')))
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
			Utils::$smcFunc['db_create_table']('{db_prefix}' . $table['name'], $table['columns'], $table['indexes']);
		}
	}
}
