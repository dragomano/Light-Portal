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
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentTopics
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
	 * The maximum number of topics to output
	 *
	 * Максимальное количество тем для вывода
	 *
	 * @var int
	 */
	private static $num_topics = 10;

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
	 * @param int $num_topics
	 * @return array
	 */
	private static function getData($num_topics)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		return ssi_recentTopics($num_topics, null, null, 'array');
	}

	/**
	 * Get the block html code
	 *
	 * Получаем html-код блока
	 *
	 * @param int $num_topics
	 * @return string
	 */
	public static function getHtml($num_topics)
	{
		global $txt;

		$recent_topics = self::getData($num_topics);

		if (empty($recent_topics))
			return '';

		$html = '
		<ul class="recent_topics noup">';

		foreach ($recent_topics as $topic) {
			$html .= '
			<li class="windowbg">' . ($topic['is_new'] ? '
				<span class="new_posts">' . $txt['new'] . '</span>' : '') . $topic['icon'] . ' ' . $topic['link'] . '
				<br><span class="smalltext">' . $txt['by'] . ' ' . $topic['poster']['link'] . '</span>
				<br><span class="smalltext">' . Helpers::getFriendlyTime($topic['timestamp']) . '</span>
			</li>';
		}

		$html .= '
		</ul>';

		return $html;
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
		global $user_info;

		if ($type !== 'recent_topics')
			return;

		$recent_topics = Helpers::getFromCache(
			'recent_topics_addon_b' . $block_id . '_u' . $user_info['id'],
			'getHtml',
			__CLASS__,
			$parameters['update_interval'] ?? $cache_time,
			$parameters['num_topics']
		);

		if (!empty($recent_topics)) {
			ob_start();
			echo $recent_topics;
			$content = ob_get_clean();
		}
	}
}
