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
 * @version 1.1
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
		$options['recent_topics'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'num_topics'      => static::$num_topics,
				'exclude_boards'  => static::$exclude_boards,
				'include_boards'  => static::$include_boards,
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

		if ($context['current_block']['type'] !== 'recent_topics')
			return;

		$args['parameters'] = array(
			'num_topics'      => FILTER_VALIDATE_INT,
			'exclude_boards'  => FILTER_SANITIZE_STRING,
			'include_boards'  => FILTER_SANITIZE_STRING,
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
		return ssi_recentTopics($parameters['num_topics'], $exclude_boards ?? null, $include_boards ?? null, 'array');
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

		$recent_topics = Helpers::getFromCache('recent_topics_addon_b' . $block_id . '_u' . $user_info['id'], 'getData', __CLASS__, $parameters['update_interval'] ?? $cache_time, $parameters);

		if (!empty($recent_topics)) {
			ob_start();

			echo '
		<ul class="recent_topics noup">';

			foreach ($recent_topics as $topic) {
				echo '
			<li class="windowbg">';

				if ($topic['is_new'])
					echo '
				<a class="new_posts" href="', $scripturl, '?topic=', $topic['topic'], '.msg', $topic['new_from'], ';topicseen#new">', $txt['new'], '</a>';

				echo $topic['icon'], ' ', $topic['link'], '
				<br><span class="smalltext">', $txt['by'], ' ', $topic['poster']['link'], '</span>
				<br><span class="smalltext">', Helpers::getFriendlyTime($topic['timestamp'], true), '</span>
			</li>';
			}

			echo '
		</ul>';

			$content = ob_get_clean();
		}
	}
}
