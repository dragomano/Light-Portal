<?php declare(strict_types=1);

/**
 * @package TrendingTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.03.25
 */

namespace Bugo\LightPortal\Plugins\TrendingTopics;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class TrendingTopics extends Block
{
	public string $icon = 'fas fa-arrow-trend-up';

	private array $timePeriod = [
		'1 day', '1 week', '2 week', '1 month', '2 month', '4 month', '6 month', '8 month', '1 year'
	];

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'no_content_class' => true,
			'show_avatars'     => true,
			'time_period'      => '1 week',
			'min_replies'      => 10,
			'num_topics'       => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_avatars' => FILTER_VALIDATE_BOOLEAN,
			'time_period'  => FILTER_DEFAULT,
			'min_replies'  => FILTER_VALIDATE_INT,
			'num_topics'   => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CheckboxField::make('show_avatars', $this->txt['show_avatars'])
			->setTab(Tab::APPEARANCE)
			->setValue($options['show_avatars']);

		SelectField::make('time_period', $this->txt['time_period'])
			->setOptions(array_combine($this->timePeriod, $this->txt['time_period_set']))
			->setValue($options['time_period']);

		NumberField::make('min_replies', $this->txt['min_replies'])
			->setAttribute('min', 1)
			->setValue($options['min_replies']);

		NumberField::make('num_topics', $this->txt['num_topics'])
			->setAttribute('min', 1)
			->setValue($options['num_topics']);
	}

	public function getData(ParamWrapper $parameters): array
	{
		$timePeriod = Typed::string($parameters['time_period'], default: $this->timePeriod[1]);
		$numTopics = Typed::int($parameters['num_topics'], default: 10);

		if (empty($numTopics))
			return [];

		$result = Db::$db->query('', '
			SELECT DISTINCT t.id_topic, t.id_member_started, t.num_replies,
				COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.subject,
				ml.id_msg, ml.poster_time
			FROM {db_prefix}topics t
				INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = t.id_member_started)
			WHERE ml.poster_time >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL {raw:period}))
			ORDER BY t.num_replies DESC
			LIMIT {int:limit}',
			[
				'period' => strtoupper($timePeriod),
				'limit'  => $numTopics,
			]
		);

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$topics[$row['id_topic']] = [
				'subject'     => $row['subject'],
				'id_msg'      => (int) $row['id_msg'],
				'poster_time' => DateTime::relative((int) $row['poster_time']),
				'num_replies' => (int) $row['num_replies'],
				'poster'      => [
					'id'   => (int) $row['id_member_started'],
					'name' => $row['poster_name'],
				],
			];
		}

		Db::$db->free_result($result);

		return $parameters['show_avatars'] ? Avatar::getWithItems($topics, 'poster') : $topics;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$topics = $this->userCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($parameters));

		if ($topics) {
			echo Str::html('ul', ['class' => $this->name . ' noup'])
				->setHtml(
					implode('', array_map(function ($id, $topic) use ($parameters) {
						$li = Str::html('li', ['class' => 'windowbg']);

						if (! empty($parameters['show_avatars']) && isset($topic['poster']['avatar'])) {
							$avatar = Str::html('span', ['class' => 'poster_avatar', 'title' => $topic['poster']['name']])
								->setHtml($topic['poster']['avatar']);

							$li->addHtml($avatar);
						}

						$link = Str::html('a', $topic['subject'])
							->href(Config::$scripturl . '?topic=' . $id . '.msg' . $topic['id_msg'] . ';topicseen#new');

						$info = Str::html('span')
							->setHtml($topic['poster_time'] . ' (' . Lang::getTxt('lp_replies_set', ['replies' => $topic['num_replies']]) . ')');

						$li->addHtml($link)->addHtml(' ')->addHtml($info);

						return $li->toHtml();
					}, array_keys($topics), $topics))
				);
		} else {
			echo Str::html('div', ['class' => 'infobox'])
				->setText($this->txt['none']);
		}
	}
}
