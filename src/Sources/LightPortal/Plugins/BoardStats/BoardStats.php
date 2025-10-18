<?php declare(strict_types=1);

/**
 * @package BoardStats (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\BoardStats;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SsiBlock;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\NumberField;
use LightPortal\Utils\ParamWrapper;
use LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-chart-pie')]
class BoardStats extends SsiBlock
{
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'link_in_title'      => Config::$scripturl . '?action=stats',
			'show_latest_member' => false,
			'show_basic_info'    => true,
			'show_whos_online'   => true,
			'use_fa_icons'       => true,
			'update_interval'    => 600,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_latest_member' => FILTER_VALIDATE_BOOLEAN,
			'show_basic_info'    => FILTER_VALIDATE_BOOLEAN,
			'show_whos_online'   => FILTER_VALIDATE_BOOLEAN,
			'use_fa_icons'       => FILTER_VALIDATE_BOOLEAN,
			'update_interval'    => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CheckboxField::make('show_latest_member', $this->txt['show_latest_member'])
			->setTab(Tab::CONTENT)
			->setValue($options['show_latest_member']);

		CheckboxField::make('show_basic_info', $this->txt['show_basic_info'])
			->setTab(Tab::CONTENT)
			->setValue($options['show_basic_info']);

		CheckboxField::make('show_whos_online', $this->txt['show_whos_online'])
			->setTab(Tab::CONTENT)
			->setValue($options['show_whos_online']);

		CheckboxField::make('use_fa_icons', $this->txt['use_fa_icons'])
			->setTab(Tab::APPEARANCE)
			->setValue($options['use_fa_icons']);

		NumberField::make('update_interval', $this->txt['update_interval'])
			->setAttribute('min', 0)
			->setValue($options['update_interval']);
	}

	public function getData(ParamWrapper $parameters): array
	{
		if (
			empty($parameters['show_latest_member'])
			&& empty($parameters['show_basic_info'])
			&& empty($parameters['show_whos_online'])
		)
			return [];

		if ($parameters['show_basic_info']) {
			$info = $this->getFromSSI('boardStats', 'array');
			$info['max_online_today'] = Lang::numberFormat(Config::$modSettings['mostOnlineToday']);
			$info['max_online'] = Lang::numberFormat(Config::$modSettings['mostOnline']);
		}

		return [
			'latest_member' => Config::$modSettings['latestRealName'] ?? '',
			'basic_info'    => $info ?? [],
			'whos_online'   => empty($parameters['show_whos_online'])
				? [] : $this->getFromSSI('whosOnline', 'array')
		];
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$boardStats = $this->userCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime(Str::typed('int', $parameters['update_interval']))
			->setFallback(fn() => $this->getData($parameters));

		if (empty($boardStats))
			return;

		$content = Str::html('div')->class('board_stats_areas');

		if ($parameters['show_latest_member'] && $boardStats['latest_member']) {
			$latestMemberDiv = Str::html('div');
			$latestMemberHeader = Str::html('h4')->addHtml(
				($parameters['use_fa_icons']
					? Str::html('i', ['class' => 'fas fa-user'])
					: Str::html('span', ['class' => 'main_icons members'])) .
				' ' . $this->txt['newbie']
			);
			$latestMemberDiv->addHtml($latestMemberHeader);
			$latestMemberDiv->addHtml(
				Str::html('ul')->class('bbc_list')->addHtml(
					Str::html('li')->setText($boardStats['latest_member'])
				)
			);
			$content->addHtml($latestMemberDiv);
		}

		if ($parameters['show_basic_info'] && $boardStats['basic_info']) {
			$statsTitle = User::$me->allowedTo('view_stats')
				? Str::html('a', Lang::$txt['forum_stats'])->href(Config::$scripturl . '?action=stats')
				: Lang::$txt['forum_stats'];

			$basicInfoDiv = Str::html('div');
			$basicInfoHeader = Str::html('h4')->addHtml(
				($parameters['use_fa_icons']
					? Str::html('i', ['class' => 'fas fa-chart-pie'])
					: Str::html('span', ['class' => 'main_icons stats'])) .
				' ' . $statsTitle
			);
			$basicInfoDiv->addHtml($basicInfoHeader);

			$basicInfoList = Str::html('ul')->class('bbc_list');

			if (User::$me->allowedTo('view_stats')) {
				$basicInfoList->addHtml(
					Str::html('li')
						->setText(Lang::$txt['members'] . ': ' . $boardStats['basic_info']['members']) .
					Str::html('li')
						->setText(Lang::$txt['posts'] . ': ' . $boardStats['basic_info']['posts']) .
					Str::html('li')
						->setText(Lang::$txt['topics'] . ': ' . $boardStats['basic_info']['topics'])
				);
			}

			$basicInfoList->addHtml(
				Str::html('li')
					->setText($this->txt['online_today'] . ': ' . $boardStats['basic_info']['max_online_today']) .
				Str::html('li')
					->setText($this->txt['max_online'] . ': ' . $boardStats['basic_info']['max_online'])
			);

			$basicInfoDiv->addHtml($basicInfoList);
			$content->addHtml($basicInfoDiv);
		}

		if ($parameters['show_whos_online'] && $boardStats['whos_online']) {
			$onlineTitle = User::$me->allowedTo('who_view')
				? Str::html('a', Lang::$txt['online_users'])->href(Config::$scripturl . '?action=who')
				: Lang::$txt['online_users'];

			$whosOnlineDiv = Str::html('div');
			$whosOnlineHeader = Str::html('h4')->addHtml(
				($parameters['use_fa_icons']
					? Str::html('i', ['class' => 'fas fa-users'])
					: Str::html('span', ['class' => 'main_icons people'])) .
				' ' . $onlineTitle
			);
			$whosOnlineDiv->addHtml($whosOnlineHeader);

			$whosOnlineList = Str::html('ul')
				->class('bbc_list')
				->addHtml(
					Str::html('li')
						->setText(Lang::$txt['members'] . ': ' . Lang::numberFormat($boardStats['whos_online']['num_users_online'])) .
					Str::html('li')
						->setText($this->txt['guests'] . ': ' . Lang::numberFormat($boardStats['whos_online']['num_guests'])) .
					Str::html('li')
						->setText($this->txt['spiders'] . ': ' . Lang::numberFormat($boardStats['whos_online']['num_spiders'])) .
					Str::html('li')
						->setText(Lang::$txt['total'] . ': ' . Lang::numberFormat($boardStats['whos_online']['total_users']))
				);

			$whosOnlineDiv->addHtml($whosOnlineList);
			$content->addHtml($whosOnlineDiv);
		}

		echo $content;
	}
}
