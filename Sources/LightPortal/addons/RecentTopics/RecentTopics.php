<?php

namespace Bugo\LightPortal\Addons\RecentTopics;

use Bugo\LightPortal\Helpers;

/**
 * RecentTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentTopics
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-book-open';

	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * The maximum number of topics to output
	 *
	 * Максимальное количество тем для вывода
	 *
	 * @var int
	 */
	private static $num_topics = 10;

	/**
	 * If set, does NOT show topics from the specified boards
	 *
	 * Идентификаторы разделов, темы из которых НЕ нужно показывать
	 *
	 * @var string
	 */
	private static $exclude_boards = '';

	/**
	 * If set, ONLY includes topics from the specified boards
	 *
	 * Идентификаторы разделов, для отображения тем ТОЛЬКО из них
	 *
	 * @var string
	 */
	private static $include_boards = '';

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
		$options['recent_topics']['no_content_class'] = static::$no_content_class;

		$options['recent_topics']['parameters']['num_topics']      = static::$num_topics;
		$options['recent_topics']['parameters']['exclude_boards']  = static::$exclude_boards;
		$options['recent_topics']['parameters']['include_boards']  = static::$include_boards;
		$options['recent_topics']['parameters']['show_avatars']    = static::$show_avatars;
		$options['recent_topics']['parameters']['update_interval'] = static::$update_interval;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public static function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'recent_topics')
			return;

		$parameters['num_topics']      = FILTER_VALIDATE_INT;
		$parameters['exclude_boards']  = FILTER_SANITIZE_STRING;
		$parameters['include_boards']  = FILTER_SANITIZE_STRING;
		$parameters['show_avatars']    = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval'] = FILTER_VALIDATE_INT;
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

		if ($context['lp_block']['type'] !== 'recent_topics')
			return;

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_recent_topics_addon_num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);

		$context['posting_fields']['exclude_boards']['label']['text'] = $txt['lp_recent_topics_addon_exclude_boards'];
		$context['posting_fields']['exclude_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_topics_addon_exclude_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['include_boards']['label']['text'] = $txt['lp_recent_topics_addon_include_boards'];
		$context['posting_fields']['include_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_topics_addon_include_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['include_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['show_avatars']['label']['text'] = $txt['lp_recent_topics_addon_show_avatars'];
		$context['posting_fields']['show_avatars']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_avatars',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_avatars'])
			)
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_recent_topics_addon_update_interval'];
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
	 * Get the recent topics of the forum
	 *
	 * Получаем последние темы форума
	 *
	 * @param array $parameters
	 * @return array
	 */
	public static function getData($parameters)
	{
		global $boarddir;

		if (!empty($parameters['exclude_boards']))
			$exclude_boards = explode(',', $parameters['exclude_boards']);

		if (!empty($parameters['include_boards']))
			$include_boards = explode(',', $parameters['include_boards']);

		require_once($boarddir . '/SSI.php');
		$topics = ssi_recentTopics($parameters['num_topics'], $exclude_boards ?? null, $include_boards ?? null, 'array');

		if (empty($topics))
			return [];

		if (!empty($parameters['show_avatars'])) {
			$posters = array_map(function ($item) {
				return $item['poster']['id'];
			}, $topics);

			loadMemberData(array_unique($posters));

			$topics = array_map(function ($item) {
				global $memberContext, $modSettings;

				if (!empty($item['poster']['id'])) {
					if (!isset($memberContext[$item['poster']['id']]))
						loadMemberContext($item['poster']['id']);

					$item['poster']['avatar'] = $memberContext[$item['poster']['id']]['avatar']['image'];
				} else {
					$item['poster']['avatar'] = '<img class="avatar" src="' . $modSettings['avatar_url'] . '/default.png" alt="">';
				}

				return $item;
			}, $topics);
		}

		return $topics;
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
		global $user_info, $scripturl, $txt;

		if ($type !== 'recent_topics')
			return;

		$recent_topics = Helpers::cache(
			'recent_topics_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$parameters['update_interval'] ?? $cache_time,
			$parameters
		);

		if (!empty($recent_topics)) {
			ob_start();

			echo '
		<ul class="recent_topics noup">';

			foreach ($recent_topics as $topic) {
				echo '
			<li class="windowbg">';

				if (!empty($parameters['show_avatars']))
					echo '
				<span class="poster_avatar" title="', $topic['poster']['name'], '">', $topic['poster']['avatar'], '</span>';

				if ($topic['is_new'])
					/* echo '
				<a class="new_posts" href="', $scripturl, '?topic=', $topic['topic'], '.msg', $topic['new_from'], ';topicseen#new">', $txt['new'], '</a>'; */
					echo '
				<a class="new_posts" href="', $topic['href'], '">', $txt['new'], '</a>';

				echo $topic['icon'], ' ', $topic['link'];

				if (empty($parameters['show_avatars']))
					echo '
				<br><span class="smalltext">', $txt['by'], ' ', $topic['poster']['link'], '</span>';

				echo '
				<br><span class="smalltext">', Helpers::getFriendlyTime($topic['timestamp'], true), '</span>
			</li>';
			}

			echo '
		</ul>';

			$content = ob_get_clean();
		}
	}
}
