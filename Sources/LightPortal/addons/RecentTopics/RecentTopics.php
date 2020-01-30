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
 * @version 0.9.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentTopics
{
	/**
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Максимальное количество тем для вывода
	 *
	 * @var int
	 */
	private static $num_topics = 10;

	/**
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
				'num_topics' => static::$num_topics
			)
		);
	}

	/**
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
			'num_topics' => FILTER_VALIDATE_INT
		);
	}

	/**
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
				'id' => 'num_topics',
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);
	}

	/**
	 * Получаем последние темы форума
	 *
	 * @param int $num_topics
	 * @return void
	 */
	public static function getRecentTopics($num_topics)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		return ssi_recentTopics($num_topics, null, null, 'array');
	}

	/**
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

		if ($type !== 'recent_topics')
			return;

		$recent_topics = Helpers::useCache('recent_topics_addon_b' . $block_id . '_u' . $context['user']['id'], 'getRecentTopics', __CLASS__, $cache_time, $parameters['num_topics']);

		if (!empty($recent_topics)) {
			ob_start();

			echo '
			<ul class="recent_topics noup">';

			foreach ($recent_topics as $topic) {
				echo '
				<li class="windowbg">
					', ($topic['is_new'] ? '<span class="new_posts">' . $txt['new'] . '</span>' : ''), $topic['icon'], ' ', $topic['link'], '
					<br><span class="smalltext">', $txt['by'], ' ', $topic['poster']['link'], '</span>
					<br><span class="smalltext">', Helpers::getFriendlyTime($topic['timestamp']), '</span>
				</li>';
			}

			echo '
			</ul>';

			$content = ob_get_clean();
		}
	}
}
