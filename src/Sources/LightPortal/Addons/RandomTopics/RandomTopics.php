<?php

/**
 * RandomTopics.php
 *
 * @package RandomTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 04.02.24
 */

namespace Bugo\LightPortal\Addons\RandomTopics;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CustomField, NumberField};
use Bugo\LightPortal\Areas\Partials\BoardSelect;
use Bugo\LightPortal\Utils\{Config, DateTime, Lang, Theme, User, Utils};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class RandomTopics extends Block
{
	public string $icon = 'fas fa-random';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'random_topics')
			return;

		$params = [
			'no_content_class' => true,
			'exclude_boards'   => '',
			'include_boards'   => '',
			'num_topics'       => 10,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'random_topics')
			return;

		$params = [
			'exclude_boards' => FILTER_DEFAULT,
			'include_boards' => FILTER_DEFAULT,
			'num_topics'     => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'random_topics')
			return;

		CustomField::make('exclude_boards', Lang::$txt['lp_random_topics']['exclude_boards'])
			->setTab('content')
			->setValue(fn() => new BoardSelect, [
				'id'    => 'exclude_boards',
				'hint'  => Lang::$txt['lp_random_topics']['exclude_boards_select'],
				'value' => Utils::$context['lp_block']['options']['exclude_boards'] ?? '',
			]);

		CustomField::make('include_boards', Lang::$txt['lp_random_topics']['include_boards'])
			->setTab('content')
			->setValue(fn() => new BoardSelect, [
				'id'    => 'include_boards',
				'hint'  => Lang::$txt['lp_random_topics']['include_boards_select'],
				'value' => Utils::$context['lp_block']['options']['include_boards'] ?? '',
			]);

		NumberField::make('num_topics', Lang::$txt['lp_random_topics']['num_topics'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_topics']);
	}

	public function getData(array $parameters): array
	{
		$exclude_boards = empty($parameters['exclude_boards']) ? null : explode(',', $parameters['exclude_boards']);
		$include_boards = empty($parameters['include_boards']) ? null : explode(',', $parameters['include_boards']);
		$num_topics     = empty($parameters['num_topics']) ? 0 : (int) $parameters['num_topics'];

		if (empty($num_topics))
			return [];

		if (Config::$db_type === 'postgresql') {
			$result = Utils::$smcFunc['db_query']('', '
				WITH RECURSIVE r AS (
					WITH b AS (
						SELECT min(t.id_topic), (
							SELECT t.id_topic FROM {db_prefix}topics AS t
							WHERE {query_wanna_see_topic_board}
								AND t.approved = {int:is_approved}' . ($exclude_boards ? '
								AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($include_boards ? '
								AND t.id_board IN ({array_int:include_boards})' : '') . '
							ORDER BY t.id_topic DESC
							LIMIT 1 OFFSET {int:limit} - 1
						) max
						FROM {db_prefix}topics AS t
						WHERE {query_wanna_see_topic_board}
							AND t.approved = {int:is_approved}' . ($exclude_boards ? '
							AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($include_boards ? '
							AND t.id_board IN ({array_int:include_boards})' : '') . '
					)
					(
						SELECT t.id_topic, min, max, array[]::integer[] || t.id_topic AS a, 0 AS n
						FROM {db_prefix}topics AS t, b
						WHERE {query_wanna_see_topic_board}
							AND t.id_topic >= min + ((max - min) * random())::int
							AND	t.approved = {int:is_approved}' . ($exclude_boards ? '
							AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($include_boards ? '
							AND t.id_board IN ({array_int:include_boards})' : '') . '
						LIMIT 1
					) UNION ALL (
						SELECT t.id_topic, min, max, a || t.id_topic, r.n + 1 AS n
						FROM {db_prefix}topics AS t, r
						WHERE {query_wanna_see_topic_board}
							AND t.id_topic >= min + ((max - min) * random())::int
							AND t.id_topic <> all(a)
							AND r.n + 1 < {int:limit}
							AND t.approved = {int:is_approved}' . ($exclude_boards ? '
							AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($include_boards ? '
							AND t.id_board IN ({array_int:include_boards})' : '') . '
						LIMIT 1
					)
				)
				SELECT t.id_topic
				FROM {db_prefix}topics AS t, r
				WHERE r.id_topic = t.id_topic',
				[
					'is_approved'    => 1,
					'exclude_boards' => $exclude_boards,
					'include_boards' => $include_boards,
					'limit'          => $num_topics
				]
			);

			$topic_ids = [];
			while ($row = Utils::$smcFunc['db_fetch_assoc']($result))
				$topic_ids[] = $row['id_topic'];

			Utils::$smcFunc['db_free_result']($result);
			Utils::$context['lp_num_queries']++;

			if (empty($topic_ids))
				return $this->getData(array_merge($parameters, ['num_topics' => $num_topics - 1]));

			$result = Utils::$smcFunc['db_query']('', '
				SELECT
					mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg,
					COALESCE(mem.real_name, mf.poster_name) AS poster_name, ' . (User::$info['is_guest'] ? '1 AS is_read' : '
					COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read') . ', mf.icon
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
					INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
					LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . (User::$info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (t.id_board = lmr.id_board AND lmr.id_member = {int:current_member})') . '
				WHERE {query_wanna_see_topic_board}
					AND t.id_topic IN ({array_int:topic_ids})',
				[
					'current_member' => User::$info['id'],
					'topic_ids'      => $topic_ids
				]
			);
		} else {
			$result = Utils::$smcFunc['db_query']('', '
				SELECT
					mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg,
					COALESCE(mem.real_name, mf.poster_name) AS poster_name, ' . (User::$info['is_guest'] ? '1 AS is_read' : '
					COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read') . ', mf.icon
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
					INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
					LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . (User::$info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (t.id_board = lmr.id_board AND lmr.id_member = {int:current_member})') . '
				WHERE {query_wanna_see_topic_board}
					AND t.approved = {int:is_approved}' . ($exclude_boards ? '
					AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($include_boards ? '
					AND t.id_board IN ({array_int:include_boards})' : '') . '
				ORDER BY RAND()
				LIMIT {int:limit}',
				[
					'current_member' => User::$info['id'],
					'is_approved'    => 1,
					'exclude_boards' => $exclude_boards,
					'include_boards' => $include_boards,
					'limit'          => $num_topics
				]
			);
		}

		$icon_sources = [];
		foreach (Utils::$context['stable_icons'] as $icon)
			$icon_sources[$icon] = 'images_url';

		$topics = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if (! empty(Config::$modSettings['messageIconChecks_enable']) && ! isset($icon_sources[$row['icon']])) {
				$icon_sources[$row['icon']] = file_exists(Theme::$current->settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';
			} elseif (! isset($icon_sources[$row['icon']])) {
				$icon_sources[$row['icon']] = 'images_url';
			}

			$topics[] = [
				'poster' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . Config::$scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>',
				'time'   => $row['poster_time'],
				'link'   => '<a href="' . Config::$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new" rel="nofollow">' . $row['subject'] . '</a>',
				'is_new' => empty($row['is_read']),
				'icon'   => '<img class="centericon" src="' . Theme::$current->settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" alt="' . $row['icon'] . '">'
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $topics;
	}

	/**
	 * @throws IntlException
	 */
	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'random_topics')
			return;

		$randomTopics = $this->cache('random_topics_addon_b' . $data->block_id . '_u' . User::$info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if ($randomTopics) {
			echo '
			<ul class="random_topics noup">';

			foreach ($randomTopics as $topic) {
				echo '
				<li class="windowbg">', ($topic['is_new'] ? '
					<span class="new_posts">' . Lang::$txt['new'] . '</span>' : ''), ' ', $topic['icon'], ' ', $topic['link'], '
					<br><span class="smalltext">', Lang::$txt['by'], ' ', $topic['poster'], '</span>
					<br><span class="smalltext">', DateTime::relative($topic['time']), '</span>
				</li>';
			}

			echo '
			</ul>';
		} else {
			echo '<div class="infobox">', Lang::$txt['lp_random_topics']['none'], '</div>';
		}
	}
}
