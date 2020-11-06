<?php

namespace Bugo\LightPortal\Addons\RandomTopics;

use Bugo\LightPortal\Helpers;

/**
 * RandomTopics
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

class RandomTopics
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-random';

	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * The maximum number of random topics to output
	 *
	 * Максимальное количество случайных тем для вывода
	 *
	 * @var int
	 */
	private static $num_topics = 10;

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
		$options['random_topics']['no_content_class'] = static::$no_content_class;

		$options['random_topics']['parameters']['num_topics'] = static::$num_topics;
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
		if ($type !== 'random_topics')
			return;

		$parameters['num_topics'] = FILTER_VALIDATE_INT;
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

		if ($context['lp_block']['type'] !== 'random_topics')
			return;

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_random_topics_addon_num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);
	}

	/**
	 * Get the list of random topics
	 *
	 * Получаем список случайных тем
	 *
	 * @param int $num_topics
	 * @return array
	 */
	public static function getData($num_topics)
	{
		global $db_type, $modSettings, $user_info, $context, $settings, $scripturl;

		if (empty($num_topics))
			return [];

		$ignore_boards = !empty($modSettings['recycle_board']) ? [(int) $modSettings['recycle_board']] : [];

		if (!empty($modSettings['allow_ignore_boards'])) {
			$ignore_boards = array_unique(array_merge($ignore_boards, $user_info['ignoreboards']));
		}

		$min = Helpers::db()->table('topics')->min('id_topic');

		if (empty($min))
			return [];

		$max = Helpers::db()->table('topics')->max('id_topic');

		$topic_ids = self::getRandomNumbersFromRange($min, $max, $num_topics);

		$request = Helpers::db()->table('topics AS t')
			->select('mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg')
			->addSelect('COALESCE(mem.real_name, mf.poster_name) AS poster_name')
			->addSelect($user_info['is_guest'] ? '1 AS is_read' : 'COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read', 'mf.icon')
			->join('messages AS ml', 't.id_last_msg = ml.id_msg')
			->join('messages AS mf', 't.id_first_msg = mf.id_msg')
			->leftJoin('members AS mem', 'mf.id_member = mem.id_member');

		if (empty($user_info['is_guest'])) {
			$request = $request->leftJoin('log_topics AS lt', 't.id_topic = lt.id_topic AND lt.id_member = ' . $user_info['id'])
				->leftJoin('log_mark_read AS lmr', 't.id_board = lmr.id_board AND lmr.id_member = ' . $user_info['id']);
		}

		$request = $request->where('t.approved', 1);

		if (!empty($ignore_boards)) {
			$request = $request->whereNotIn('t.id_board', $ignore_boards);
		}

		$request = $request->whereIn('t.id_topic', $topic_ids)
			->get();

		$icon_sources = [];
		foreach ($context['stable_icons'] as $icon)
			$icon_sources[$icon] = 'images_url';

		$topics = [];

		foreach ($request as $row) {
			if (!empty($modSettings['messageIconChecks_enable']) && !isset($icon_sources[$row['icon']]))
				$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';
			elseif (!isset($icon_sources[$row['icon']]))
				$icon_sources[$row['icon']] = 'images_url';

			$topics[] = array(
				'poster' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>',
				'time'   => $row['poster_time'],
				'link'   => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new" rel="nofollow">' . $row['subject'] . '</a>',
				'is_new' => empty($row['is_read']),
				'icon'   => '<img class="centericon" src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" alt="' . $row['icon'] . '">'
			);
		}

		shuffle($topics);

		return $topics;
	}

	/**
	 * Get $num random numbers from $min to $max
	 *
	 * Получаем $num случайных чисел от $min до $max
	 *
	 * @param int $min
	 * @param int $max
	 * @param int $num
	 * @return array
	 */
	private static function getRandomNumbersFromRange($min = 0, $max = 0, $num = 0)
	{
		$result = [];

		while (count($result) < $num) {
			$result[mt_rand($min, $max)] = 0;
		}

		return array_keys($result);
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
		global $user_info, $txt;

		if ($type !== 'random_topics')
			return;

		$random_topics = Helpers::cache(
			'random_topics_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$cache_time,
			$parameters['num_topics']
		);

		if (!empty($random_topics)) {
			ob_start();

			echo '
			<ul class="random_topics noup">';

			foreach ($random_topics as $topic) {
				echo '
				<li class="windowbg">', ($topic['is_new'] ? '
					<span class="new_posts">' . $txt['new'] . '</span>' : ''), $topic['icon'], ' ', $topic['link'], '
					<br><span class="smalltext">', $txt['by'], ' ', $topic['poster'], '</span>
					<br><span class="smalltext">', Helpers::getFriendlyTime($topic['time']), '</span>
				</li>';
			}

			echo '
			</ul>';

			$content = ob_get_clean();
		}
	}
}
