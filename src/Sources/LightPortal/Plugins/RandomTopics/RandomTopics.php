<?php declare(strict_types=1);

/**
 * @package RandomTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 06.11.25
 */

namespace LightPortal\Plugins\RandomTopics;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\NumberField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\Utils\DateTime;
use LightPortal\Utils\Str;
use Ramsey\Collection\Map\NamedParameterMap;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-random')]
class RandomTopics extends Block
{
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
			->setValue(fn() => SelectFactory::board([
				'id'    => 'exclude_boards',
				'hint'  => $this->txt['exclude_boards_select'],
				'value' => $options['exclude_boards'] ?? '',
			]));

		CustomField::make('include_boards', $this->txt['include_boards'])
			->setTab(Tab::CONTENT)
			->setValue(fn() => SelectFactory::board([
				'id'    => 'include_boards',
				'hint'  => $this->txt['include_boards_select'],
				'value' => $options['include_boards'] ?? '',
			]));

		NumberField::make('num_topics', $this->txt['num_topics'])
			->setAttribute('min', 1)
			->setValue($options['num_topics']);

		CheckboxField::make('show_num_views', $this->txt['show_num_views'])
			->setValue($options['show_num_views']);
	}


	public function getData(NamedParameterMap $parameters): array
	{
		$excludeBoards = array_filter(array_map(intval(...), explode(',', $parameters['exclude_boards'] ?? '')));
		$includeBoards = array_filter(array_map(intval(...), explode(',', $parameters['include_boards'] ?? '')));

		$topicsCount = Str::typed('int', $parameters['num_topics']);
		if (empty($topicsCount)) {
			return [];
		}

		$selectTopics = $this->sql->select()
			->from(['t' => 'topics'])
			->columns(['id_topic'])
			->where(['t.approved' => 1, 't.id_redirect_topic' => 0])
			->order(new Expression("MD5(CONCAT(t.id_topic, CURRENT_TIMESTAMP))"))
			->limit($topicsCount);

		if (! empty($excludeBoards)) {
			$selectTopics->where->notIn('t.id_board', $excludeBoards);
		}

		if (! empty($includeBoards)) {
			$selectTopics->where->in('t.id_board', $includeBoards);
		}

		$result = $this->sql->execute($selectTopics);

		$topicIds = [];
		foreach ($result as $row) {
			$topicIds[] = $row['id_topic'];
		}

		if (empty($topicIds)) {
			$parameters['num_topics'] = $topicsCount - 1;

			return $this->getData($parameters);
		}

		$columns = ['num_views'];

		$select = $this->sql->select()
			->from(['t' => 'topics'])
			->join(
				['ml' => 'messages'],
				't.id_last_msg = ml.id_msg', ['id_topic', 'id_msg', 'id_msg_modified']
			)
			->join(
				['mf' => 'messages'],
				't.id_first_msg = mf.id_msg',
				['poster_time', 'subject', 'id_member', 'icon']
			)
			->join(
				['mem' => 'members'],
				'mf.id_member = mem.id_member',
				['poster_name' => new Expression('COALESCE(mem.real_name, mf.poster_name)')]
			);

		if (User::$me->is_guest) {
			$columns['is_read'] = new Expression('"1"');
		} else {
			$select->join(
				['lt' => 'log_topics'],
				new Expression('t.id_topic = lt.id_topic AND lt.id_member = ?', [User::$me->id]),
				[],
				Select::JOIN_LEFT
			);
			$select->join(
				['lmr' => 'log_mark_read'],
				new Expression('t.id_board = lmr.id_board AND lmr.id_member = ?', [User::$me->id]),
				[],
				Select::JOIN_LEFT
			);
			$columns['is_read'] = new Expression('COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified');
		}

		$select->columns($columns);
		$select->where->in('t.id_topic', $topicIds);

		$result = $this->sql->execute($select);

		$topics = [];
		foreach ($result as $row) {
			$topics[] = [
				'author_id'   => $row['id_member'],
				'author_name' => $row['poster_name'],
				'time'        => $row['poster_time'],
				'num_views'   => $row['num_views'],
				'href'        => Config::$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new',
				'title'       => $row['subject'],
				'is_new'      => empty($row['is_read']),
			];
		}

		return $topics;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$randomTopics = $this->userCache($this->name . '_addon_b' . $e->args->id)
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
