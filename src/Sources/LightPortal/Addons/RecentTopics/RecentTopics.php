<?php

/**
 * RecentTopics.php
 *
 * @package RecentTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\RecentTopics;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Bugo\LightPortal\Areas\Fields\RadioField;
use Bugo\LightPortal\Areas\Partials\BoardSelect;
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentTopics extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-book-open';

	public function blockOptions(array &$options): void
	{
		$options['recent_topics']['no_content_class'] =  true;

		$options['recent_topics']['parameters'] = [
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

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'recent_topics')
			return;

		$parameters['exclude_boards']   = FILTER_DEFAULT;
		$parameters['include_boards']   = FILTER_DEFAULT;
		$parameters['use_simple_style'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_avatars']     = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_icons']       = FILTER_VALIDATE_BOOLEAN;
		$parameters['num_topics']       = FILTER_VALIDATE_INT;
		$parameters['link_type']        = FILTER_DEFAULT;
		$parameters['update_interval']  = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'recent_topics')
			return;

		CustomField::make('exclude_boards', $this->txt['lp_recent_topics']['exclude_boards'])
			->setTab('content')
			->setValue(fn() => new BoardSelect, [
				'id'    => 'exclude_boards',
				'hint'  => $this->txt['lp_recent_topics']['exclude_boards_select'],
				'value' => $this->context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
			]);

		CustomField::make('include_boards', $this->txt['lp_recent_topics']['include_boards'])
			->setTab('content')
			->setValue(fn() => new BoardSelect, [
				'id'    => 'include_boards',
				'hint'  => $this->txt['lp_recent_topics']['include_boards_select'],
				'value' => $this->context['lp_block']['options']['parameters']['include_boards'] ?? '',
			]);

		CheckboxField::make('use_simple_style', $this->txt['lp_recent_topics']['use_simple_style'])
			->setTab('appearance')
			->setAfter($this->txt['lp_recent_topics']['use_simple_style_subtext'])
			->setValue($this->context['lp_block']['options']['parameters']['use_simple_style']);

		CheckboxField::make('show_avatars', $this->txt['lp_recent_topics']['show_avatars'])
			->setTab('appearance')
			->setValue($this->context['lp_block']['options']['parameters']['show_avatars'] && empty($this->context['lp_block']['options']['parameters']['use_simple_style']));

		CheckboxField::make('show_icons', $this->txt['lp_recent_topics']['show_icons'])
			->setTab('appearance')
			->setValue($this->context['lp_block']['options']['parameters']['show_icons'] && empty($this->context['lp_block']['options']['parameters']['use_simple_style']));

		NumberField::make('num_topics', $this->txt['lp_recent_topics']['num_topics'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['num_topics']);

		RadioField::make('link_type', $this->txt['lp_recent_topics']['type'])
			->setOptions(array_combine(['link', 'preview'], $this->txt['lp_recent_topics']['type_set']))
			->setValue($this->context['lp_block']['options']['parameters']['link_type']);

		NumberField::make('update_interval', $this->txt['lp_recent_topics']['update_interval'])
			->setAttribute('min', 0)
			->setValue($this->context['lp_block']['options']['parameters']['update_interval']);
	}

	/**
	 * @throws IntlException
	 */
	public function getData(array $parameters): array
	{
		$exclude_boards = empty($parameters['exclude_boards']) ? null : explode(',', $parameters['exclude_boards']);
		$include_boards = empty($parameters['include_boards']) ? null : explode(',', $parameters['include_boards']);

		$topics = $this->getFromSsi('recentTopics', (int) $parameters['num_topics'], $exclude_boards, $include_boards, 'array');

		if (empty($topics))
			return [];

		array_walk($topics, fn(&$topic) => $topic['timestamp'] = $this->getFriendlyTime((int) $topic['timestamp']));

		if ($parameters['show_avatars'] && empty($parameters['use_simple_style']))
			$topics = $this->getItemsWithUserAvatars($topics, 'poster');

		return $topics;
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'recent_topics')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_avatars'] ??= false;

		$recent_topics = $this->cache('recent_topics_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $data->cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recent_topics))
			return;

		$this->setTemplate();

		show_topics($recent_topics, $parameters, $this->isInSidebar($data->block_id) === false);
	}
}
