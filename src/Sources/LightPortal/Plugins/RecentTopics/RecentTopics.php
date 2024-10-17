<?php

/**
 * @package RecentTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 02.06.24
 */

namespace Bugo\LightPortal\Plugins\RecentTopics;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Bugo\LightPortal\Areas\Fields\RadioField;
use Bugo\LightPortal\Areas\Partials\BoardSelect;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\DateTime;
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentTopics extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-book-open';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_topics')
			return;

		$params = [
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

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_topics')
			return;

		$params = [
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

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_topics')
			return;

		CustomField::make('exclude_boards', Lang::$txt['lp_recent_topics']['exclude_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'exclude_boards',
				'hint'  => Lang::$txt['lp_recent_topics']['exclude_boards_select'],
				'value' => Utils::$context['lp_block']['options']['exclude_boards'] ?? '',
			]);

		CustomField::make('include_boards', Lang::$txt['lp_recent_topics']['include_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'include_boards',
				'hint'  => Lang::$txt['lp_recent_topics']['include_boards_select'],
				'value' => Utils::$context['lp_block']['options']['include_boards'] ?? '',
			]);

		CheckboxField::make('use_simple_style', Lang::$txt['lp_recent_topics']['use_simple_style'])
			->setTab(Tab::APPEARANCE)
			->setAfter(Lang::$txt['lp_recent_topics']['use_simple_style_subtext'])
			->setValue(Utils::$context['lp_block']['options']['use_simple_style']);

		CheckboxField::make('show_avatars', Lang::$txt['lp_recent_topics']['show_avatars'])
			->setTab(Tab::APPEARANCE)
			->setValue(
				Utils::$context['lp_block']['options']['show_avatars']
				&& empty(Utils::$context['lp_block']['options']['use_simple_style'])
			);

		CheckboxField::make('show_icons', Lang::$txt['lp_recent_topics']['show_icons'])
			->setTab(Tab::APPEARANCE)
			->setValue(
				Utils::$context['lp_block']['options']['show_icons']
				&& empty(Utils::$context['lp_block']['options']['use_simple_style'])
			);

		NumberField::make('num_topics', Lang::$txt['lp_recent_topics']['num_topics'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_topics']);

		RadioField::make('link_type', Lang::$txt['lp_recent_topics']['type'])
			->setOptions(array_combine(['link', 'preview'], Lang::$txt['lp_recent_topics']['type_set']))
			->setValue(Utils::$context['lp_block']['options']['link_type']);

		NumberField::make('update_interval', Lang::$txt['lp_recent_topics']['update_interval'])
			->setAttribute('min', 0)
			->setValue(Utils::$context['lp_block']['options']['update_interval']);
	}

	/**
	 * @throws IntlException
	 */
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

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'recent_topics')
			return;

		if ($this->request()->has('preview')) {
			$parameters['update_interval'] = 0;
		}

		$parameters['show_avatars'] ??= false;

		$recentTopics = $this->cache('recent_topics_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recentTopics))
			return;

		$this->setTemplate();

		show_topics($recentTopics, $parameters, $this->isInSidebar($data->id) === false);
	}
}
