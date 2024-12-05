<?php

/**
 * @package RandomTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\RandomTopics;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Partials\BoardSelect;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class RandomTopics extends Block
{
	public string $icon = 'fas fa-random';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'no_content_class' => true,
			'exclude_boards'   => '',
			'include_boards'   => '',
			'num_topics'       => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_boards' => FILTER_DEFAULT,
			'include_boards' => FILTER_DEFAULT,
			'num_topics'     => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('exclude_boards', $this->txt['exclude_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'exclude_boards',
				'hint'  => $this->txt['exclude_boards_select'],
				'value' => $options['exclude_boards'] ?? '',
			]);

		CustomField::make('include_boards', $this->txt['include_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'include_boards',
				'hint'  => $this->txt['include_boards_select'],
				'value' => $options['include_boards'] ?? '',
			]);

		NumberField::make('num_topics', $this->txt['num_topics'])
			->setAttribute('min', 1)
			->setValue($options['num_topics']);
	}

	public function getData(array $parameters): array
	{
		$excludeBoards = empty($parameters['exclude_boards']) ? null : explode(',', (string) $parameters['exclude_boards']);
		$includeBoards = empty($parameters['include_boards']) ? null : explode(',', (string) $parameters['include_boards']);
		$topicsCount   = empty($parameters['num_topics']) ? 0 : (int) $parameters['num_topics'];

		if (empty($topicsCount))
			return [];

		if (Config::$db_type === 'postgresql') {
			$result = Db::$db->query('', '
				WITH RECURSIVE r AS (
					WITH b AS (
						SELECT min(t.id_topic), (
							SELECT t.id_topic FROM {db_prefix}topics AS t
							WHERE {query_wanna_see_topic_board}
								AND t.approved = {int:is_approved}' . ($excludeBoards ? '
								AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($includeBoards ? '
								AND t.id_board IN ({array_int:include_boards})' : '') . '
							ORDER BY t.id_topic DESC
							LIMIT 1 OFFSET {int:limit} - 1
						) max
						FROM {db_prefix}topics AS t
						WHERE {query_wanna_see_topic_board}
							AND t.approved = {int:is_approved}' . ($excludeBoards ? '
							AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($includeBoards ? '
							AND t.id_board IN ({array_int:include_boards})' : '') . '
					)
					(
						SELECT t.id_topic, min, max, array[]::integer[] || t.id_topic AS a, 0 AS n
						FROM {db_prefix}topics AS t, b
						WHERE {query_wanna_see_topic_board}
							AND t.id_topic >= min + ((max - min) * random())::int
							AND	t.approved = {int:is_approved}' . ($excludeBoards ? '
							AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($includeBoards ? '
							AND t.id_board IN ({array_int:include_boards})' : '') . '
						LIMIT 1
					) UNION ALL (
						SELECT t.id_topic, min, max, a || t.id_topic, r.n + 1 AS n
						FROM {db_prefix}topics AS t, r
						WHERE {query_wanna_see_topic_board}
							AND t.id_topic >= min + ((max - min) * random())::int
							AND t.id_topic <> all(a)
							AND r.n + 1 < {int:limit}
							AND t.approved = {int:is_approved}' . ($excludeBoards ? '
							AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($includeBoards ? '
							AND t.id_board IN ({array_int:include_boards})' : '') . '
						LIMIT 1
					)
				)
				SELECT t.id_topic
				FROM {db_prefix}topics AS t, r
				WHERE r.id_topic = t.id_topic',
				[
					'is_approved'    => 1,
					'exclude_boards' => $excludeBoards,
					'include_boards' => $includeBoards,
					'limit'          => $topicsCount,
				]
			);

			$topicIds = [];
			while ($row = Db::$db->fetch_assoc($result))
				$topicIds[] = $row['id_topic'];

			Db::$db->free_result($result);

			if (empty($topicIds))
				return $this->getData(array_merge($parameters, ['num_topics' => $topicsCount - 1]));

			$result = Db::$db->query('', '
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
					'topic_ids'      => $topicIds,
				]
			);
		} else {
			$result = Db::$db->query('', '
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
					AND t.approved = {int:is_approved}' . ($excludeBoards ? '
					AND t.id_board NOT IN ({array_int:exclude_boards})' : '') . ($includeBoards ? '
					AND t.id_board IN ({array_int:include_boards})' : '') . '
				ORDER BY RAND()
				LIMIT {int:limit}',
				[
					'current_member' => User::$info['id'],
					'is_approved'    => 1,
					'exclude_boards' => $excludeBoards,
					'include_boards' => $includeBoards,
					'limit'          => $topicsCount,
				]
			);
		}

		$iconSources = [];
		foreach (Utils::$context['stable_icons'] as $icon)
			$iconSources[$icon] = 'images_url';

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if (! empty(Config::$modSettings['messageIconChecks_enable']) && ! isset($iconSources[$row['icon']])) {
				$iconSources[$row['icon']] = file_exists(Theme::$current->settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png')
					? 'images_url'
					: 'default_images_url';
			} elseif (! isset($iconSources[$row['icon']])) {
				$iconSources[$row['icon']] = 'images_url';
			}

			$topics[] = [
				'poster' => empty($row['id_member']) ? $row['poster_name'] : Str::html('a', [
						'href' => Config::$scripturl . '?action=profile;u=' . $row['id_member']
					])->setText($row['poster_name']),
				'time'   => $row['poster_time'],
				'link'   => Str::html('a', [
					'href' => Config::$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new',
					'rel' => 'nofollow'
				])->setText($row['subject']),
				'is_new' => empty($row['is_read']),
				'icon'   => Str::html('img', [
					'class' => 'centericon',
					'src' => Theme::$current->settings[$iconSources[$row['icon']]] . '/post/' . $row['icon'] . '.png',
					'alt' => $row['icon']
				])
			];
		}

		Db::$db->free_result($result);

		return $topics;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$randomTopics = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if ($randomTopics) {
			$ul = Str::html('ul', ['class' => $this->name . ' noup']);

			foreach ($randomTopics as $topic) {
				$li = Str::html('li', ['class' => 'windowbg']);

				if ($topic['is_new']) {
					$li->addHtml(
						Str::html('span', Lang::$txt['new'])
							->class('new_posts')
					);
				}

				$li->addHtml($topic['icon'])
					->addHtml($topic['link'])
					->addHtml(Str::html('br'));

				$li->addHtml(
					Str::html('span')
						->class('smalltext')
						->setHtml(Lang::$txt['by'] . ' ' . $topic['poster'])
				)
					->addHtml(Str::html('br'));

				$li->addHtml(
					Str::html('span')
						->class('smalltext')
						->setHtml(DateTime::relative($topic['time']))
				);

				$ul->addHtml($li);
			}

			echo $ul;
		} else {
			echo Str::html('div', ['class' => 'infobox'])
				->setText($this->txt['none']);
		}
	}
}
