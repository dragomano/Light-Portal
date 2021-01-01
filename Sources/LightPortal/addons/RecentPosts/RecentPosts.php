<?php

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\LightPortal\Helpers;

/**
 * RecentPosts
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentPosts
{
	/**
	 * @var string
	 */
	public $addon_icon = 'far fa-comment-alt';

	/**
	 * @var bool
	 */
	private $no_content_class = true;

	/**
	 * @var int
	 */
	private $num_posts = 10;

	/**
	 * @var string
	 */
	private $type = 'link';

	/**
	 * @var string
	 */
	private $exclude_boards = '';

	/**
	 * @var string
	 */
	private $include_boards = '';

	/**
	 * @var string
	 */
	private $exclude_topics = '';

	/**
	 * @var string
	 */
	private $include_topics = '';

	/**
	 * @var bool
	 */
	private $show_avatars = false;

	/**
	 * @var int
	 */
	private $update_interval = 600;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['recent_posts']['no_content_class'] = $this->no_content_class;

		$options['recent_posts']['parameters']['num_posts']       = $this->num_posts;
		$options['recent_posts']['parameters']['link_type']       = $this->type;
		$options['recent_posts']['parameters']['exclude_boards' ] = $this->exclude_boards;
		$options['recent_posts']['parameters']['include_boards']  = $this->include_boards;
		$options['recent_posts']['parameters']['exclude_topics']  = $this->exclude_topics;
		$options['recent_posts']['parameters']['include_topics']  = $this->include_topics;
		$options['recent_posts']['parameters']['show_avatars']    = $this->show_avatars;
		$options['recent_posts']['parameters']['update_interval'] = $this->update_interval;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
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

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'recent_posts')
			return;

		$context['posting_fields']['num_posts']['label']['text'] = $txt['lp_recent_posts_addon_num_posts'];
		$context['posting_fields']['num_posts']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_posts',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_posts']
			)
		);

		$context['posting_fields']['link_type']['label']['text'] = $txt['lp_recent_posts_addon_type'];
		$context['posting_fields']['link_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'link_type'
			),
			'options' => array()
		);

		foreach ($txt['lp_recent_posts_addon_type_set'] as $key => $value) {
			if (RC2_CLEAN) {
				$context['posting_fields']['link_type']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['link_type']
				);
			} else {
				$context['posting_fields']['link_type']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['link_type']
				);
			}
		}

		$context['posting_fields']['exclude_boards']['label']['text'] = $txt['lp_recent_posts_addon_exclude_boards'];
		$context['posting_fields']['exclude_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts_addon_exclude_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['include_boards']['label']['text'] = $txt['lp_recent_posts_addon_include_boards'];
		$context['posting_fields']['include_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts_addon_include_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['include_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['exclude_topics']['label']['text'] = $txt['lp_recent_posts_addon_exclude_topics'];
		$context['posting_fields']['exclude_topics']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts_addon_exclude_topics_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['exclude_topics'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['include_topics']['label']['text'] = $txt['lp_recent_posts_addon_include_topics'];
		$context['posting_fields']['include_topics']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_posts_addon_include_topics_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['include_topics'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['show_avatars']['label']['text'] = $txt['lp_recent_posts_addon_show_avatars'];
		$context['posting_fields']['show_avatars']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_avatars',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_avatars'])
			)
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_recent_posts_addon_update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'update_interval',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['update_interval']
			)
		);
	}

	/**
	 * Get the recent posts of the forum
	 *
	 * Получаем последние сообщения форума
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getData($parameters)
	{
		global $boarddir;

		if (!empty($parameters['exclude_boards']))
			$exclude_boards = explode(',', $parameters['exclude_boards']);

		if (!empty($parameters['include_boards']))
			$include_boards = explode(',', $parameters['include_boards']);

		require_once($boarddir . '/SSI.php');
		$posts = ssi_recentPosts($parameters['num_posts'], $exclude_boards ?? null, $include_boards ?? null, 'array');

		if (empty($posts))
			return [];

		if (!empty($parameters['exclude_topics'])) {
			$exclude_topics = array_flip(explode(',', $parameters['exclude_topics']));

			$posts = array_filter($posts, function ($item) use ($exclude_topics) {
				return !array_key_exists($item['topic'], $exclude_topics);
			});
		}

		if (!empty($parameters['include_topics'])) {
			$include_topics = array_flip(explode(',', $parameters['include_topics']));

			$posts = array_filter($posts, function ($item) use ($include_topics) {
				return array_key_exists($item['topic'], $include_topics);
			});
		}

		if (!empty($parameters['show_avatars'])) {
			$posters = array_map(function ($item) {
				return $item['poster']['id'];
			}, $posts);

			loadMemberData(array_unique($posters));

			$posts = array_map(function ($item) {
				global $memberContext, $modSettings;

				if (!empty($item['poster']['id'])) {
					if (!isset($memberContext[$item['poster']['id']]))
						loadMemberContext($item['poster']['id']);

					$item['poster']['avatar'] = $memberContext[$item['poster']['id']]['avatar']['image'];
				} else {
					$item['poster']['avatar'] = '<img class="avatar" src="' . $modSettings['avatar_url'] . '/default.png" loading="lazy" alt="'. $item['poster']['name'] . '">';
				}

				return $item;
			}, $posts);
		}

		return $posts;
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $scripturl, $txt;

		if ($type !== 'recent_posts')
			return;

		$recent_posts = Helpers::cache(
			'recent_posts_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$parameters['update_interval'] ?? $cache_time,
			$parameters
		);

		if (!empty($recent_posts)) {
			ob_start();

			echo '
		<ul class="recent_posts noup">';

			foreach ($recent_posts as $post) {
				$post['preview'] = '<a href="' . $post['href'] . '">' . shorten_subject($post['preview'], 20) . '</a>';

				echo '
			<li class="windowbg">';

				if (!empty($parameters['show_avatars']))
					echo '
				<span class="poster_avatar" title="', $post['poster']['name'], '">', $post['poster']['avatar'], '</span>';

				if ($post['is_new'])
					echo '
				<a class="new_posts" href="', $scripturl, '?topic=', $post['topic'], '.msg', $post['new_from'], ';topicseen#new">', $txt['new'], '</a>';

				echo '
				', $post[$parameters['link_type']];

				if (empty($parameters['show_avatars']))
					echo '
				<br><span class="smalltext">', $txt['by'], ' ', $post['poster']['link'], '</span>';

				echo '
				<br><span class="smalltext">', Helpers::getFriendlyTime($post['timestamp'], true), '</span>
			</li>';
			}

			echo '
		</ul>';

			$content = ob_get_clean();
		}
	}
}
