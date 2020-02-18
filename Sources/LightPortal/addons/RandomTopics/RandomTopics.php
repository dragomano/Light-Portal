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
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RandomTopics
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
		$options['random_topics'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'num_topics' => static::$num_topics
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

		if ($context['current_block']['type'] !== 'random_topics')
			return;

		$args['parameters'] = array(
			'num_topics' => FILTER_VALIDATE_INT
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

		if ($context['lp_block']['type'] !== 'random_topics')
			return;

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_random_topics_addon_num_topics'];
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
	 * Get the list of random topics
	 *
	 * Получаем список случайных тем
	 *
	 * @param int $num_topics
	 * @return array
	 */
	public static function getRandomTopics($num_topics)
	{
		global $db_type, $smcFunc, $modSettings, $user_info, $context, $settings, $scripturl;

		if (empty($num_topics))
			return [];

		if ($db_type == 'postgresql') {
			$request = $smcFunc['db_query']('', '
				WITH RECURSIVE r AS (
					WITH b AS (
						SELECT min(t.id_topic), (
							SELECT t.id_topic FROM {db_prefix}topics AS t
							WHERE t.approved = {int:is_approved}' . (!empty($modSettings['recycle_board']) ? '
								AND t.id_board != {int:recycle_board}' : '') . '
							ORDER BY t.id_topic DESC
							LIMIT 1
							OFFSET {int:limit} - 1
						) max
						FROM {db_prefix}topics AS t
						WHERE t.approved = {int:is_approved}' . (!empty($modSettings['recycle_board']) ? '
							AND t.id_board != {int:recycle_board}' : '') . '
					)
					(
						SELECT t.id_topic, min, max, array[]::integer[] || t.id_topic AS a, 0 AS n
						FROM {db_prefix}topics AS t, b
						WHERE t.id_topic >= min + ((max - min) * random())::int' . (!empty($modSettings['recycle_board']) ? '
							AND t.id_board != {int:recycle_board}' : '') . '
							AND	t.approved = {int:is_approved}
						LIMIT 1
					) UNION ALL (
						SELECT t.id_topic, min, max, a || t.id_topic, r.n + 1 AS n
						FROM {db_prefix}topics AS t, r
						WHERE t.id_topic >= min + ((max - min) * random())::int
							AND t.id_topic <> all(a)
							AND r.n + 1 < {int:limit}' . (!empty($modSettings['recycle_board']) ? '
							AND t.id_board != {int:recycle_board}' : '') . '
							AND t.approved = {int:is_approved}
						LIMIT 1
					)
				)
				SELECT t.id_topic
				FROM {db_prefix}topics AS t, r
				WHERE r.id_topic = t.id_topic',
				array(
					'is_approved'   => 1,
					'recycle_board' => !empty($modSettings['recycle_board']) ? (int) $modSettings['recycle_board'] : null,
					'limit'         => $num_topics
				)
			);

			$topic_ids = [];
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$topic_ids[] = $row['id_topic'];

			$smcFunc['db_free_result']($request);

			if (empty($topic_ids))
				return self::getRandomTopics($num_topics - 1);

			$request = $smcFunc['db_query']('', '
				SELECT
					mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg,
					COALESCE(mem.real_name, mf.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read' : '
					COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read') . ', mf.icon
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
					INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mf.id_member)' . (!$user_info['is_guest'] ? '
					LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})' : '') . '
				WHERE t.id_topic IN ({array_int:topic_ids})' . (!empty($modSettings['allow_ignore_boards']) ? '
					AND t.id_board NOT IN (SELECT ignore_boards FROM {db_prefix}members WHERE id_member = {int:current_member})' : ''),
				array(
					'current_member' => $user_info['id'],
					'topic_ids'      => $topic_ids
				)
			);
		} else {
			$request = $smcFunc['db_query']('', '
				SELECT
					mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg,
					COALESCE(mem.real_name, mf.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read' : '
					COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read') . ', mf.icon
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
					INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mf.id_member)' . (!$user_info['is_guest'] ? '
					LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})' : '') . '
				WHERE t.approved = {int:is_approved}' . (!empty($modSettings['recycle_board']) ? '
					AND t.id_board != {int:recycle_board}' : '') . (!empty($modSettings['allow_ignore_boards']) ? '
					AND t.id_board NOT IN (SELECT ignore_boards FROM {db_prefix}members WHERE id_member = {int:current_member})' : '') . '
					AND t.id_topic IN (SELECT id_topic FROM {db_prefix}topics)
				ORDER BY RAND()
				LIMIT {int:limit}',
				array(
					'current_member' => $user_info['id'],
					'is_approved'    => 1,
					'recycle_board'  => !empty($modSettings['recycle_board']) ? (int) $modSettings['recycle_board'] : null,
					'limit'          => $num_topics
				)
			);
		}

		$icon_sources = array();
		foreach ($context['stable_icons'] as $icon)
			$icon_sources[$icon] = 'images_url';

		$topics = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
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

		$smcFunc['db_free_result']($request);

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
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $txt, $scripturl;

		if ($type !== 'random_topics')
			return;

		$random_topics = Helpers::useCache('random_topics_addon_b' . $block_id . '_u' . $context['user']['id'], 'getRandomTopics', __CLASS__, $cache_time, $parameters['num_topics']);

		if (!empty($random_topics)) {
			ob_start();

			echo '
			<ul class="random_topics noup">';

			foreach ($random_topics as $topic) {
				echo '
				<li class="windowbg">
					', ($topic['is_new'] ? '<span class="new_posts">' . $txt['new'] . '</span>' : ''), $topic['icon'], ' ', $topic['link'], '
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
