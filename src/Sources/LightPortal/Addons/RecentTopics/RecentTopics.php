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
 * @version 03.06.23
 */

namespace Bugo\LightPortal\Addons\RecentTopics;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Partials\BoardSelect;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentTopics extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-book-open';

	public function blockOptions(array &$options)
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

	public function validateBlockData(array &$parameters, string $type)
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

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'recent_topics')
			return;

		$this->context['posting_fields']['use_simple_style']['label']['text'] = $this->txt['lp_recent_topics']['use_simple_style'];
		$this->context['posting_fields']['use_simple_style']['input'] = [
			'type' => 'checkbox',
			'after' => $this->txt['lp_recent_topics']['use_simple_style_subtext'],
			'attributes' => [
				'id'      => 'use_simple_style',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['use_simple_style']
			],
			'tab' => 'appearance'
		];

		$this->context['posting_fields']['show_avatars']['label']['text'] = $this->txt['lp_recent_topics']['show_avatars'];
		$this->context['posting_fields']['show_avatars']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_avatars',
				'checked' => $this->context['lp_block']['options']['parameters']['show_avatars'] && empty($this->context['lp_block']['options']['parameters']['use_simple_style'])
			],
			'tab' => 'appearance'
		];

		$this->context['posting_fields']['show_icons']['label']['text'] = $this->txt['lp_recent_topics']['show_icons'];
		$this->context['posting_fields']['show_icons']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_icons',
				'checked' => $this->context['lp_block']['options']['parameters']['show_icons'] && empty($this->context['lp_block']['options']['parameters']['use_simple_style'])
			],
			'tab' => 'appearance'
		];

		$this->context['posting_fields']['num_topics']['label']['text'] = $this->txt['lp_recent_topics']['num_topics'];
		$this->context['posting_fields']['num_topics']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_topics']
			]
		];

		$this->context['posting_fields']['link_type']['label']['text'] = $this->txt['lp_recent_topics']['type'];
		$this->context['posting_fields']['link_type']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'link_type'
			],
			'options' => [],
		];

		$link_types = array_combine(['link', 'preview'], $this->txt['lp_recent_topics']['type_set']);

		foreach ($link_types as $key => $value) {
			$this->context['posting_fields']['link_type']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['link_type']
			];
		}

		$this->context['posting_fields']['exclude_boards']['label']['html'] = $this->txt['lp_recent_topics']['exclude_boards'];
		$this->context['posting_fields']['exclude_boards']['input']['tab'] = 'content';
		$this->context['posting_fields']['exclude_boards']['input']['html'] = (new BoardSelect)([
			'id'    => 'exclude_boards',
			'hint'  => $this->txt['lp_recent_topics']['exclude_boards_select'],
			'value' => $this->context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
		]);

		$this->context['posting_fields']['include_boards']['label']['html'] = $this->txt['lp_recent_topics']['include_boards'];
		$this->context['posting_fields']['include_boards']['input']['tab'] = 'content';
		$this->context['posting_fields']['include_boards']['input']['html'] = (new BoardSelect)([
			'id'    => 'include_boards',
			'hint'  => $this->txt['lp_recent_topics']['include_boards_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_boards'] ?? '',
		]);

		$this->context['posting_fields']['update_interval']['label']['text'] = $this->txt['lp_recent_topics']['update_interval'];
		$this->context['posting_fields']['update_interval']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'update_interval',
				'min'   => 0,
				'value' => $this->context['lp_block']['options']['parameters']['update_interval']
			]
		];
	}

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

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'recent_topics')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_avatars'] ??= false;

		$recent_topics = $this->cache('recent_topics_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recent_topics))
			return;

		$this->setTemplate();

		show_topics($recent_topics, $parameters, $this->isBlockInPlacements($block_id, ['header', 'top', 'bottom', 'footer']));
	}
}
