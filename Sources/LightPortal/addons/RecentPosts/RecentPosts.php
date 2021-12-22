<?php

/**
 * RecentPosts.php
 *
 * @package RecentPosts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class RecentPosts extends Plugin
{
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
		$parameters['link_type']       = FILTER_SANITIZE_STRING;
		$parameters['exclude_boards']  = FILTER_SANITIZE_STRING;
		$parameters['include_boards']  = FILTER_SANITIZE_STRING;
		$parameters['exclude_topics']  = FILTER_SANITIZE_STRING;
		$parameters['include_topics']  = FILTER_SANITIZE_STRING;
		$parameters['show_avatars']    = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'recent_posts')
			return;

		$context['posting_fields']['num_posts']['label']['text'] = $txt['lp_recent_posts']['num_posts'];
		$context['posting_fields']['num_posts']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_posts',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_posts']
			)
		);

		$context['posting_fields']['link_type']['label']['text'] = $txt['lp_recent_posts']['type'];
		$context['posting_fields']['link_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'link_type'
			),
			'options' => array(),
			'tab' => 'content'
		);

		$link_types = array_combine(array('link', 'preview'), $txt['lp_recent_posts']['type_set']);

		foreach ($link_types as $key => $value) {
			$context['posting_fields']['link_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['link_type']
			);
		}

		$context['posting_fields']['exclude_boards']['label']['text'] = $txt['lp_recent_posts']['exclude_boards'];
		$context['posting_fields']['exclude_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts']['exclude_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['include_boards']['label']['text'] = $txt['lp_recent_posts']['include_boards'];
		$context['posting_fields']['include_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts']['include_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['include_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['exclude_topics']['label']['text'] = $txt['lp_recent_posts']['exclude_topics'];
		$context['posting_fields']['exclude_topics']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts']['exclude_topics_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['exclude_topics'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['include_topics']['label']['text'] = $txt['lp_recent_posts']['include_topics'];
		$context['posting_fields']['include_topics']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts']['include_topics_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['include_topics'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['show_avatars']['label']['text'] = $txt['lp_recent_posts']['show_avatars'];
		$context['posting_fields']['show_avatars']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_avatars',
				'checked' => ! empty($context['lp_block']['options']['parameters']['show_avatars'])
			),
			'tab' => 'appearance'
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_recent_posts']['update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'update_interval',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['update_interval']
			)
		);
	}

	public function getData(array $parameters): array
	{
		if (! empty($parameters['exclude_boards']))
			$exclude_boards = explode(',', $parameters['exclude_boards']);

		if (! empty($parameters['include_boards']))
			$include_boards = explode(',', $parameters['include_boards']);

		$this->loadSsi();

		$posts = ssi_recentPosts($parameters['num_posts'], $exclude_boards ?? null, $include_boards ?? null, 'array');

		if (empty($posts))
			return [];

		if (! empty($parameters['exclude_topics'])) {
			$exclude_topics = array_flip(explode(',', $parameters['exclude_topics']));

			$posts = array_filter($posts, function ($item) use ($exclude_topics) {
				return ! array_key_exists($item['topic'], $exclude_topics);
			});
		}

		if (! empty($parameters['include_topics'])) {
			$include_topics = array_flip(explode(',', $parameters['include_topics']));

			$posts = array_filter($posts, fn($item) => array_key_exists($item['topic'], $include_topics));
		}

		if (! empty($parameters['show_avatars']))
            $posts = $this->getPostsWithUserAvatars($posts);

		return $posts;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $scripturl, $txt;

		if ($type !== 'recent_posts')
			return;

		$recent_posts = Helper::cache('recent_posts_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (empty($recent_posts))
			return;

		echo '
		<ul class="recent_posts noup">';

		foreach ($recent_posts as $post) {
			$post['preview'] = '<a href="' . $post['href'] . '">' . shorten_subject($post['preview'], 20) . '</a>';

			echo '
			<li class="windowbg">';

			if (! empty($parameters['show_avatars']) && ! empty($post['poster']['avatar']))
				echo '
				<span class="poster_avatar" title="', $post['poster']['name'], '">', $post['poster']['avatar'], '</span>';

			if ($post['is_new'])
				echo '
				<a class="new_posts" href="', $scripturl, '?topic=', $post['topic'], '.msg', $post['new_from'], ';topicseen#new">', $txt['new'], '</a> ';

			echo $post[$parameters['link_type']];

			if (empty($parameters['show_avatars']))
				echo '
				<br><span class="smalltext">', $txt['by'], ' ', $post['poster']['link'], '</span>';

			echo '
				<br><span class="smalltext">', Helper::getFriendlyTime($post['timestamp'], true), '</span>
			</li>';
		}

		echo '
		</ul>';
	}

    private function getPostsWithUserAvatars(array $posts): array
    {
        $posters = array_map(fn($item) => $item['poster']['id'], $posts);

        $loadedUserIds = loadMemberData(array_unique($posters));

        return array_map(function ($item) use ($loadedUserIds) {
            global $memberContext, $modSettings;

            if (! empty($item['poster']['id']) && in_array($item['poster']['id'], $loadedUserIds)) {
                if (! isset($memberContext[$item['poster']['id']]['avatar']))
                    try {
                        loadMemberContext($item['poster']['id']);
                    } catch (\Exception $e) {
                        log_error('[LP] RecentPosts addon (user #' . $item['poster']['id'] . '): ' . $e->getMessage(), 'user');
                    }

                $item['poster']['avatar'] = $memberContext[$item['poster']['id']]['avatar']['image'];
            } else {
                $item['poster']['avatar'] = '<img class="avatar" src="' . $modSettings['avatar_url'] . '/default.png" loading="lazy" alt="' . $item['poster']['name'] . '">';
            }

            return $item;
        }, $posts);
    }
}
