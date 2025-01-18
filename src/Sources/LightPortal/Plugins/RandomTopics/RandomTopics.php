<?php declare(strict_types=1);

/**
 * @package RandomTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 08.01.25
 */

namespace Bugo\LightPortal\Plugins\RandomTopics;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Partials\BoardSelect;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

if (! defined('LP_NAME'))
	die('No direct access...');

class RandomTopics extends Block
{
	public string $icon = 'fas fa-random';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_boards'   => '',
			'include_boards'   => '',
			'num_topics'       => 10,
			'show_num_views'   => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_boards' => FILTER_DEFAULT,
			'include_boards' => FILTER_DEFAULT,
			'num_topics'     => FILTER_VALIDATE_INT,
			'show_num_views' => FILTER_VALIDATE_BOOLEAN,
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

		CheckboxField::make('show_num_views', $this->txt['show_num_views'])
			->setValue($options['show_num_views']);
	}

	public function getData(ParamWrapper $parameters): array
	{
		$excludeBoards = empty($parameters['exclude_boards']) ? null : explode(',', (string) $parameters['exclude_boards']);
		$includeBoards = empty($parameters['include_boards']) ? null : explode(',', (string) $parameters['include_boards']);
		$topicsCount   = Typed::int($parameters['num_topics']);

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
			while ($row = Db::$db->fetch_assoc($result)) {
				$topicIds[] = $row['id_topic'];
			}

			Db::$db->free_result($result);

			if (empty($topicIds)) {
				$parameters['num_topics'] = $topicsCount - 1;

				return $this->getData($parameters);
			}

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
					COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read') . ', t.num_views
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

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$topics[] = [
				'author_id'   => (int) $row['id_member'],
				'author_name' => $row['poster_name'],
				'time'        => (int) $row['poster_time'],
				'num_views'   => (int) $row['num_views'],
				'href'        => Config::$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new',
				'title'       => $row['subject'],
				'is_new'      => empty($row['is_read']),
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
			->setFallback(fn() => $this->getData($parameters));

		if ($randomTopics) {
			$ul = Str::html('ul', ['class' => $this->name . ' noup']);

			$i = 0;
			foreach ($randomTopics as $topic) {
				$li = Str::html('li', ['class' => 'generic_list_wrapper bg ' . ($i % 2 === 0 ? 'odd' : 'even')]);
				$link = Str::html('a', $topic['title'])->href($topic['href']);
				$author = empty($topic['author_id']) ? $topic['author_name'] : Str::html('a', $topic['author_name'])
					->href(Config::$scripturl . '?action=profile;u=' . $topic['author_id']);

				if ($topic['is_new']) {
					$li->addHtml(
						Str::html('span', Lang::$txt['new'])
							->class('new_posts')
							->style('margin-right: 4px')
					);
				}

				$li
					->addHtml($link)
					->addText(' ' . Lang::$txt['by'] . ' ')
					->addHtml($author)
					->addHtml(', ' . DateTime::relative($topic['time']));

				$parameters['show_num_views'] && $li
					->addText(' (' . Lang::getTxt('lp_views_set', ['views' => $topic['num_views']]) . ')');

				$ul->addHtml($li);
				$i++;
			}

			echo $ul;
		} else {
			echo Str::html('div', ['class' => 'infobox'])
				->setText($this->txt['none']);
		}
	}
}
