<?php declare(strict_types=1);

/**
 * @package RecentTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace Bugo\LightPortal\Plugins\RecentTopics;

use Bugo\Compat\Config;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Plugins\SsiBlock;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\UI\Partials\SelectFactory;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-book-open', showContentClass: false)]
class RecentTopics extends SsiBlock
{
	use HasView;

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
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
			->setValue(fn() => SelectFactory::board([
				'id'    => 'exclude_boards',
				'hint'  => $this->txt['exclude_boards_select'],
				'value' => $options['exclude_boards'] ?? '',
			]));

		CustomField::make('include_boards', $this->txt['include_boards'])
			->setTab(Tab::CONTENT)
			->setValue(fn() => SelectFactory::board([
				'id'    => 'include_boards',
				'hint'  => $this->txt['include_boards_select'],
				'value' => $options['include_boards'] ?? '',
			]));

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

	public function getData(ParamWrapper $parameters): array
	{
		$excludeBoards = empty($parameters['exclude_boards'])
			? null
			: explode(',', (string) $parameters['exclude_boards']);
		$includeBoards = empty($parameters['include_boards'])
			? null
			: explode(',', (string) $parameters['include_boards']);

		$topics = $this->getFromSSI(
			'recentTopics',
			Str::typed('int', $parameters['num_topics'], default: 10),
			$excludeBoards,
			$includeBoards,
			'array'
		);

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

		$recentTopics = $this->userCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime(Str::typed('int', $parameters['update_interval']))
			->setFallback(fn() => $this->getData($parameters));

		if (empty($recentTopics))
			return;

		echo $this->view(params: [
			'topics'      => $recentTopics,
			'parameters'  => $parameters,
			'isInSidebar' => $this->isInSidebar($e->args->id) === false
		]);
	}
}
