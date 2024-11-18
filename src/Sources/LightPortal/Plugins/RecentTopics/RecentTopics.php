<?php

/**
 * @package RecentTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 20.11.24
 */

namespace Bugo\LightPortal\Plugins\RecentTopics;

use Bugo\Compat\{Config, User};
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Bugo\LightPortal\Areas\Fields\RadioField;
use Bugo\LightPortal\Areas\Partials\BoardSelect;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\DateTime;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentTopics extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-book-open';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'no_content_class' => true,
			'link_in_title'    => Config::$scripturl . '?action=unread',
			'exclude_boards'   => '',
			'include_boards'   => '',
			'use_simple_style' => false,
			'show_avatars'     => false,
			'show_icons'       => false,
			'num_topics'       => 10,
			'link_type'        => 'link',
			'update_interval'  => 600,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_boards'   => FILTER_DEFAULT,
			'include_boards'   => FILTER_DEFAULT,
			'use_simple_style' => FILTER_VALIDATE_BOOLEAN,
			'show_avatars'     => FILTER_VALIDATE_BOOLEAN,
			'show_icons'       => FILTER_VALIDATE_BOOLEAN,
			'num_topics'       => FILTER_VALIDATE_INT,
			'link_type'        => FILTER_DEFAULT,
			'update_interval'  => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('exclude_boards', $this->txt['exclude_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'exclude_boards',
				'hint'  => $this->txt['exclude_boards_select'],
				'value' => $options['exclude_boards'] ?? '',
			]);

		CustomField::make('include_boards', $this->txt['include_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'include_boards',
				'hint'  => $this->txt['include_boards_select'],
				'value' => $options['include_boards'] ?? '',
			]);

		CheckboxField::make('use_simple_style', $this->txt['use_simple_style'])
			->setTab(Tab::APPEARANCE)
			->setDescription($this->txt['use_simple_style_subtext'])
			->setValue($options['use_simple_style']);

		CheckboxField::make('show_avatars', $this->txt['show_avatars'])
			->setTab(Tab::APPEARANCE)
			->setValue(
				$options['show_avatars']
				&& empty($options['use_simple_style'])
			);

		CheckboxField::make('show_icons', $this->txt['show_icons'])
			->setTab(Tab::APPEARANCE)
			->setValue(
				$options['show_icons']
				&& empty($options['use_simple_style'])
			);

		NumberField::make('num_topics', $this->txt['num_topics'])
			->setAttribute('min', 1)
			->setValue($options['num_topics']);

		RadioField::make('link_type', $this->txt['type'])
			->setOptions(array_combine(['link', 'preview'], $this->txt['type_set']))
			->setValue($options['link_type']);

		NumberField::make('update_interval', $this->txt['update_interval'])
			->setAttribute('min', 0)
			->setValue($options['update_interval']);
	}

	public function getData(array $parameters): array
	{
		$excludeBoards = empty($parameters['exclude_boards']) ? null : explode(',', (string) $parameters['exclude_boards']);
		$includeBoards = empty($parameters['include_boards']) ? null : explode(',', (string) $parameters['include_boards']);

		$topics = $this->getFromSsi('recentTopics', (int) $parameters['num_topics'], $excludeBoards, $includeBoards, 'array');

		if (empty($topics))
			return [];

		array_walk($topics,
			static fn(&$topic) => $topic['timestamp'] = DateTime::relative((int) $topic['timestamp'])
		);

		if ($parameters['show_avatars'] && empty($parameters['use_simple_style'])) {
			$topics = Avatar::getWithItems($topics, 'poster');
		}

		return $topics;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		if ($this->request()->has('preview')) {
			$parameters['update_interval'] = 0;
		}

		$parameters['show_avatars'] ??= false;
		$parameters['num_topics'] ??= 10;

		$recentTopics = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recentTopics))
			return;

		$this->setTemplate();

		show_topics($recentTopics, $parameters, $this->isInSidebar($e->args->id) === false);
	}
}
