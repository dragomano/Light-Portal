<?php

/**
 * RecentTopics.php
 *
 * @package RecentTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 08.05.22
 */

namespace Bugo\LightPortal\Addons\RecentTopics;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentTopics extends Plugin
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-book-open';

	public function blockOptions(array &$options)
	{
		$options['recent_topics']['no_content_class'] =  true;

		$options['recent_topics']['parameters'] = [
			'use_simple_style' => false,
			'show_avatars'     => false,
			'show_icons'       => false,
			'num_topics'       => 10,
			'exclude_boards'   => '',
			'include_boards'   => '',
			'update_interval'  => 600,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'recent_topics')
			return;

		$parameters['use_simple_style'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_avatars']     = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_icons']       = FILTER_VALIDATE_BOOLEAN;
		$parameters['num_topics']       = FILTER_VALIDATE_INT;
		$parameters['exclude_boards']   = FILTER_DEFAULT;
		$parameters['include_boards']   = FILTER_DEFAULT;
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

		$this->context['posting_fields']['exclude_boards']['label']['text'] = $this->txt['lp_recent_topics']['exclude_boards'];
		$this->context['posting_fields']['exclude_boards']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_recent_topics']['exclude_boards_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
				'style'     => 'width: 100%'
			]
		];

		$this->context['posting_fields']['include_boards']['label']['text'] = $this->txt['lp_recent_topics']['include_boards'];
		$this->context['posting_fields']['include_boards']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_recent_topics']['include_boards_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['include_boards'] ?? '',
				'style'     => 'width: 100%'
			]
		];

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

		$recent_topics = $this->cache('recent_topics_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (empty($recent_topics))
			return;

		echo '
		<ul class="recent_topics noup">';

		if ($parameters['use_simple_style']) {
			foreach ($recent_topics as $topic) {
				echo '
			<li class="windowbg">
				<div class="smalltext">', $topic['time'], '</div>';

				echo $topic['link'];

				echo '
				<div class="smalltext', $this->context['right_to_left'] ? ' floatright' : '', '">
					<i class="fas fa-eye"></i> ', $topic['views'], '&nbsp;
					<i class="fas fa-comment"></i> ', $topic['replies'], '
				</div>
			</li>';
			}
		} else {
			foreach ($recent_topics as $topic) {
				echo '
			<li class="windowbg">';

				if ($parameters['show_avatars'] && $topic['poster']['avatar'])
					echo '
				<div class="poster_avatar" title="', $topic['poster']['name'], '">', $topic['poster']['avatar'], '</div>';

				if ($topic['is_new'])
					echo '
				<a class="new_posts" href="', $this->scripturl, '?topic=', $topic['topic'], '.msg', $topic['new_from'], ';topicseen#new">', $this->txt['new'], '</a> ';

				echo ($parameters['show_icons'] ? $topic['icon'] . ' ' : ''), $topic['link'];

				if (empty($parameters['show_avatars']))
					echo '
				<br><span class="smalltext">', $this->txt['by'], ' ', $topic['poster']['link'], '</span>';

				echo '
				<br><span class="smalltext">', $this->getFriendlyTime((int) $topic['timestamp']), '</span>
			</li>';
			}
		}

		echo '
		</ul>';
	}
}
