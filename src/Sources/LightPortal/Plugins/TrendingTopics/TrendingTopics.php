<?php declare(strict_types=1);

/**
 * @package TrendingTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 10.10.25
 */

namespace Bugo\LightPortal\Plugins\TrendingTopics;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Str;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-arrow-trend-up', showContentClass: false)]
class TrendingTopics extends Block
{
	private array $timePeriod = [
		'1 day', '1 week', '2 week', '1 month', '2 month', '4 month', '6 month', '8 month', '1 year'
	];

	#[HookAttribute(PortalHook::prepareBlockParams)]
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_avatars' => true,
			'time_period'  => '1 week',
			'min_replies'  => 10,
			'num_topics'   => 10,
		];
	}

	#[HookAttribute(PortalHook::validateBlockParams)]
	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_avatars' => FILTER_VALIDATE_BOOLEAN,
			'time_period'  => FILTER_DEFAULT,
			'min_replies'  => FILTER_VALIDATE_INT,
			'num_topics'   => FILTER_VALIDATE_INT,
		];
	}

	#[HookAttribute(PortalHook::prepareBlockFields)]
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
		$timePeriod = Str::typed('string', $parameters['time_period'], default: $this->timePeriod[1]);
		$numTopics  = Str::typed('int', $parameters['num_topics'], default: 10);

		if (empty($numTopics))
			return [];

		$interval = strtoupper($timePeriod);

		$select = $this->sql->select()
			->quantifier(Select::QUANTIFIER_DISTINCT)
			->from(['t' => 'topics'])
			->columns(['id_topic', 'id_member_started', 'num_replies'])
			->join(
				['ml' => 'messages'],
				't.id_last_msg = ml.id_msg',
				['id_msg', 'poster_time']
			)
			->join(
				['mf' => 'messages'],
				't.id_first_msg = mf.id_msg',
				['subject']
			)
			->join(
				['mem' => 'members'],
				'mem.id_member = t.id_member_started',
				['poster_name' => new Expression('COALESCE(mem.real_name, mf.poster_name)')],
				Select::JOIN_LEFT
			)
			->where(new Expression("ml.poster_time >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL $interval))"))
			->order('t.num_replies DESC')
			->limit($numTopics);

		$result = $this->sql->execute($select);

		$topics = [];
		foreach ($result as $row) {
			$topics[$row['id_topic']] = [
				'subject'     => $row['subject'],
				'id_msg'      => $row['id_msg'],
				'poster_time' => DateTime::relative($row['poster_time']),
				'num_replies' => $row['num_replies'],
				'poster'      => [
					'id'   => $row['id_member_started'],
					'name' => $row['poster_name'],
				],
			];
		}

		return $parameters['show_avatars'] ? Avatar::getWithItems($topics, 'poster') : $topics;
	}

	#[HookAttribute(PortalHook::prepareContent)]
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
