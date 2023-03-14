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
 * @version 16.03.23
 */

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentPosts extends Plugin
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-comment-alt';

	public function blockOptions(array &$options)
	{
		$options['recent_posts']['no_content_class'] = true;

		$options['recent_posts']['parameters'] = [
			'num_posts'       => 10,
			'link_type'       => 'link',
			'exclude_boards'  => '',
			'include_boards'  => '',
			'exclude_topics'  => '',
			'include_topics'  => '',
			'show_avatars'    => false,
			'update_interval' => 600,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'recent_posts')
			return;

		$parameters['num_posts']       = FILTER_VALIDATE_INT;
		$parameters['link_type']       = FILTER_DEFAULT;
		$parameters['exclude_boards']  = FILTER_DEFAULT;
		$parameters['include_boards']  = FILTER_DEFAULT;
		$parameters['exclude_topics']  = FILTER_DEFAULT;
		$parameters['include_topics']  = FILTER_DEFAULT;
		$parameters['show_avatars']    = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'recent_posts')
			return;

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
			'tab' => 'content'
		];

		$link_types = array_combine(['link', 'preview'], $this->txt['lp_recent_posts']['type_set']);

		foreach ($link_types as $key => $value) {
			$this->context['posting_fields']['link_type']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['link_type']
			];
		}

		$this->context['posting_fields']['exclude_boards']['label']['text'] = $this->txt['lp_recent_posts']['exclude_boards'];
		$this->context['posting_fields']['exclude_boards']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_recent_posts']['exclude_boards_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
				'style'     => 'width: 100%'
			]
		];

		$this->context['posting_fields']['include_boards']['label']['text'] = $this->txt['lp_recent_posts']['include_boards'];
		$this->context['posting_fields']['include_boards']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_recent_posts']['include_boards_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['include_boards'] ?? '',
				'style'     => 'width: 100%'
			]
		];

		$this->context['posting_fields']['exclude_topics']['label']['text'] = $this->txt['lp_recent_posts']['exclude_topics'];
		$this->context['posting_fields']['exclude_topics']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_recent_posts']['exclude_topics_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['exclude_topics'] ?? '',
				'style'     => 'width: 100%'
			]
		];

		$this->context['posting_fields']['include_topics']['label']['text'] = $this->txt['lp_recent_posts']['include_topics'];
		$this->context['posting_fields']['include_topics']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_recent_posts']['include_topics_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['include_topics'] ?? '',
				'style'     => 'width: 100%'
			]
		];

		$this->context['posting_fields']['show_avatars']['label']['text'] = $this->txt['lp_recent_posts']['show_avatars'];
		$this->context['posting_fields']['show_avatars']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_avatars',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_avatars']
			],
			'tab' => 'appearance'
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
		$exclude_boards = empty($parameters['exclude_boards']) ? null : explode(',', $parameters['exclude_boards']);
		$include_boards = empty($parameters['include_boards']) ? null : explode(',', $parameters['include_boards']);

		$posts = $this->getFromSsi('recentPosts', (int) $parameters['num_posts'], $exclude_boards, $include_boards, 'array');

		if (empty($posts))
			return [];

		if (! empty($parameters['exclude_topics'])) {
			$exclude_topics = array_flip(explode(',', $parameters['exclude_topics']));

			$posts = array_filter($posts, fn($item) => ! array_key_exists($item['topic'], $exclude_topics));
		}

		if (! empty($parameters['include_topics'])) {
			$include_topics = array_flip(explode(',', $parameters['include_topics']));

			$posts = array_filter($posts, fn($item) => array_key_exists($item['topic'], $include_topics));
		}

		if ($parameters['show_avatars'])
			$posts = $this->getItemsWithUserAvatars($posts, 'poster');

		return $posts;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'recent_posts')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$recent_posts = $this->cache('recent_posts_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recent_posts))
			return;

		echo '
		<ul class="recent_posts noup">';

		foreach ($recent_posts as $post) {
			$post['preview'] = '<a href="' . $post['href'] . '">' . $this->getShortenText($post['preview'], 20) . '</a>';

			echo '
			<li class="windowbg">';

			if ($parameters['show_avatars'] && $post['poster']['avatar'])
				echo '
				<div class="poster_avatar" title="', $post['poster']['name'], '">', $post['poster']['avatar'], '</div>';

			if ($post['is_new'])
				echo '
				<a class="new_posts" href="', $this->scripturl, '?topic=', $post['topic'], '.msg', $post['new_from'], ';topicseen#new">', $this->txt['new'], '</a> ';

			echo $post[$parameters['link_type']];

			if (empty($parameters['show_avatars']))
				echo '
				<br><span class="smalltext">', $this->txt['by'], ' ', $post['poster']['link'], '</span>';

			echo '
				<br><span class="smalltext">', $this->getFriendlyTime((int) $post['timestamp']), '</span>
			</li>';
		}

		echo '
		</ul>';
	}
}
