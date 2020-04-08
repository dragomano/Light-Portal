<?php

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\LightPortal\Helpers;

/**
 * RecentPosts
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentPosts
{
	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * The maximum number of posts to output
	 *
	 * Максимальное количество сообщений для вывода
	 *
	 * @var int
	 */
	private static $num_posts = 10;

	/**
	 * The link type (link|preview)
	 *
	 * Тип отображаемой ссылки (link|preview)
	 *
	 * @var string
	 */
	private static $type = 'link';

	/**
	 * Display user avatars (true|false)
	 *
	 * Отображать аватарки (true|false)
	 *
	 * @var bool
	 */
	private static $show_avatars = false;

	/**
	 * Online list update interval, in seconds
	 *
	 * Интервал обновления списка онлайн, в секундах
	 *
	 * @var int
	 */
	private static $update_interval = 600;

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['recent_posts'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'num_posts'       => static::$num_posts,
				'link_type'       => static::$type,
				'show_avatars'    => static::$show_avatars,
				'update_interval' => static::$update_interval
			)
		);
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'recent_posts')
			return;

		$args['parameters'] = array(
			'num_posts'       => FILTER_VALIDATE_INT,
			'link_type'       => FILTER_SANITIZE_STRING,
			'show_avatars'    => FILTER_VALIDATE_BOOLEAN,
			'update_interval' => FILTER_VALIDATE_INT
		);
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'recent_posts')
			return;

		$context['posting_fields']['num_posts']['label']['text'] = $txt['lp_recent_posts_addon_num_posts'];
		$context['posting_fields']['num_posts']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_posts',
				'min' => 1,
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
			if (!defined('JQUERY_VERSION')) {
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

		$context['posting_fields']['show_avatars']['label']['text'] = $txt['lp_recent_posts_addon_show_avatars'];
		$context['posting_fields']['show_avatars']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id' => 'show_avatars',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_avatars'])
			)
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_recent_posts_addon_update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'update_interval',
				'min' => 0,
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
	public static function getRecentPosts($parameters)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		$posts = ssi_recentPosts($parameters['num_posts'], null, null, 'array');

		if (!empty($posts) && !empty($parameters['show_avatars'])) {
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
					$item['poster']['avatar'] = '<img class="avatar" src="' . $modSettings['avatar_url'] . '/default.png" alt="">';
				}

				return $item;
			}, $posts);
		}

		return $posts;
	}

	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $txt;

		if ($type !== 'recent_posts')
			return;

		$recent_posts = Helpers::useCache(
			'recent_posts_addon_b' . $block_id . '_u' . $context['user']['id'],
			'getRecentPosts',
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
					<span class="poster_avatar">', $post['poster']['avatar'], '</span>';

				echo '
					', ($post['is_new'] ? '<span class="new_posts">' . $txt['new'] . '</span> ' : ''), $post[$parameters['link_type']], '
					<br>
					<span class="smalltext">', $txt['by'], ' ', $post['poster']['link'], '</span>
					<br class="clear">
					<span class="smalltext">', Helpers::getFriendlyTime($post['timestamp']), '</span>
				</li>';
			}

			echo '
			</ul>';

			$content = ob_get_clean();
		}
	}
}
