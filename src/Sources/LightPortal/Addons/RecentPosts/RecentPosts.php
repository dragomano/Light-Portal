<?php

/**
 * RecentPosts.php
 *
 * @package RecentPosts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 03.06.23
 */

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Partials\{BoardSelect, TopicSelect};

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentPosts extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-comment-alt';

	public function blockOptions(array &$options)
	{
		$options['recent_posts']['no_content_class'] = true;

		$options['recent_posts']['parameters'] = [
			'exclude_boards'   => '',
			'include_boards'   => '',
			'exclude_topics'   => '',
			'include_topics'   => '',
			'use_simple_style' => false,
			'show_avatars'     => false,
			'num_posts'        => 10,
			'link_type'        => 'link',
			'show_body'        => false,
			'limit_body'       => true,
			'update_interval'  => 600,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'recent_posts')
			return;

		$parameters['exclude_boards']   = FILTER_DEFAULT;
		$parameters['include_boards']   = FILTER_DEFAULT;
		$parameters['exclude_topics']   = FILTER_DEFAULT;
		$parameters['include_topics']   = FILTER_DEFAULT;
		$parameters['use_simple_style'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_avatars']     = FILTER_VALIDATE_BOOLEAN;
		$parameters['num_posts']        = FILTER_VALIDATE_INT;
		$parameters['link_type']        = FILTER_DEFAULT;
		$parameters['show_body']        = FILTER_VALIDATE_BOOLEAN;
		$parameters['limit_body']       = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval']  = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'recent_posts')
			return;

		$this->context['posting_fields']['use_simple_style']['label']['text'] = $this->txt['lp_recent_posts']['use_simple_style'];
		$this->context['posting_fields']['use_simple_style']['input'] = [
			'type' => 'checkbox',
			'after' => $this->txt['lp_recent_posts']['use_simple_style_subtext'],
			'attributes' => [
				'id'      => 'use_simple_style',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['use_simple_style']
			],
			'tab' => 'appearance'
		];

		$this->context['posting_fields']['show_avatars']['label']['text'] = $this->txt['lp_recent_posts']['show_avatars'];
		$this->context['posting_fields']['show_avatars']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_avatars',
				'checked' => $this->context['lp_block']['options']['parameters']['show_avatars'] && empty($this->context['lp_block']['options']['parameters']['use_simple_style'])
			],
			'tab' => 'appearance'
		];

		$this->context['posting_fields']['num_posts']['label']['text'] = $this->txt['lp_recent_posts']['num_posts'];
		$this->context['posting_fields']['num_posts']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_posts',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_posts']
			]
		];

		$this->context['posting_fields']['link_type']['label']['text'] = $this->txt['lp_recent_posts']['type'];
		$this->context['posting_fields']['link_type']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'link_type'
			],
			'options' => [],
		];

		$link_types = array_combine(['link', 'preview'], $this->txt['lp_recent_posts']['type_set']);

		foreach ($link_types as $key => $value) {
			$this->context['posting_fields']['link_type']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['link_type']
			];
		}

		$this->context['posting_fields']['exclude_boards']['label']['html'] = $this->txt['lp_recent_posts']['exclude_boards'];
		$this->context['posting_fields']['exclude_boards']['input']['tab'] = 'content';
		$this->context['posting_fields']['exclude_boards']['input']['html'] = (new BoardSelect)([
			'id'    => 'exclude_boards',
			'hint'  => $this->txt['lp_recent_posts']['exclude_boards_select'],
			'value' => $this->context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
		]);

		$this->context['posting_fields']['include_boards']['label']['html'] = $this->txt['lp_recent_posts']['include_boards'];
		$this->context['posting_fields']['include_boards']['input']['tab'] = 'content';
		$this->context['posting_fields']['include_boards']['input']['html'] = (new BoardSelect)([
			'id'    => 'include_boards',
			'hint'  => $this->txt['lp_recent_posts']['include_boards_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_boards'] ?? '',
		]);

		$this->context['posting_fields']['exclude_topics']['label']['html'] = $this->txt['lp_recent_posts']['exclude_topics'];
		$this->context['posting_fields']['exclude_topics']['input']['tab'] = 'content';
		$this->context['posting_fields']['exclude_topics']['input']['html'] = (new TopicSelect)([
			'id'    => 'exclude_topics',
			'hint'  => $this->txt['lp_recent_posts']['exclude_topics_select'],
			'value' => $this->context['lp_block']['options']['parameters']['exclude_topics'] ?? '',
		]);

		$this->context['posting_fields']['include_topics']['label']['html'] = $this->txt['lp_recent_posts']['include_topics'];
		$this->context['posting_fields']['include_topics']['input']['tab'] = 'content';
		$this->context['posting_fields']['include_topics']['input']['html'] = (new TopicSelect)([
			'id'    => 'include_topics',
			'hint'  => $this->txt['lp_recent_posts']['include_topics_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_topics'] ?? '',
		]);

		$this->context['posting_fields']['show_body']['label']['text'] = $this->txt['lp_recent_posts']['show_body'];
		$this->context['posting_fields']['show_body']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_body',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_body']
			],
		];

		$this->context['posting_fields']['limit_body']['label']['text'] = $this->txt['lp_recent_posts']['limit_body'];
		$this->context['posting_fields']['limit_body']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'limit_body',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['limit_body']
			],
		];

		$this->context['posting_fields']['update_interval']['label']['text'] = $this->txt['lp_recent_posts']['update_interval'];
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
		$exclude_boards = empty($parameters['exclude_boards']) ? [] : explode(',', $parameters['exclude_boards']);
		$include_boards = empty($parameters['include_boards']) ? [] : explode(',', $parameters['include_boards']);
		$exclude_topics = empty($parameters['exclude_topics']) ? [] : explode(',', $parameters['exclude_topics']);
		$include_topics = empty($parameters['include_topics']) ? [] : explode(',', $parameters['include_topics']);

		$query_where = '
			m.id_msg >= {int:min_message_id}' . (empty($exclude_boards) ? '' : '
			AND b.id_board NOT IN ({array_int:exclude_boards})') . (empty($include_boards) ? '' : '
			AND b.id_board IN ({array_int:include_boards})') . (empty($exclude_topics) ? '' : '
			AND m.id_topic NOT IN ({array_int:exclude_topics})') . (empty($include_topics) ? '' : '
			AND m.id_topic IN ({array_int:include_topics})') . '
			AND {query_wanna_see_board}' . ($this->modSettings['postmod_active'] ? '
			AND m.approved = {int:is_approved}' : '');

		$query_where_params = [
			'is_approved'    => 1,
			'include_boards' => $include_boards,
			'exclude_boards' => $exclude_boards,
			'include_topics' => $include_topics,
			'exclude_topics' => $exclude_topics,
			'min_message_id' => $this->modSettings['maxMsgID'] - (empty($this->context['min_message_posts']) ? 25 : $this->context['min_message_posts']) * min((int) $parameters['num_posts'], 5),
		];

		$posts = $this->getFromSsi('queryPosts', $query_where, $query_where_params, (int) $parameters['num_posts'], 'm.id_msg DESC', 'array', (bool) $parameters['limit_body']);

		if (empty($posts))
			return [];

		array_walk($posts, fn(&$post) => $post['timestamp'] = $this->getFriendlyTime((int) $post['timestamp']));

		if ($parameters['show_avatars'] && empty($parameters['use_simple_style']))
			$posts = $this->getItemsWithUserAvatars($posts, 'poster');

		return $posts;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'recent_posts')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_avatars'] ??= false;
		$parameters['limit_body'] ??= false;

		$recent_posts = $this->cache('recent_posts_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recent_posts))
			return;

		$this->setTemplate();

		show_posts($recent_posts, $parameters, $this->isBlockInPlacements($block_id, ['header', 'top', 'bottom', 'footer']));
	}
}
