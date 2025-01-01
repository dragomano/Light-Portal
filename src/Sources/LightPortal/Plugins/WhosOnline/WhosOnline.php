<?php declare(strict_types=1);

/**
 * @package WhosOnline (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\WhosOnline;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class WhosOnline extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-eye';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'link_in_title'   => Config::$scripturl . '?action=who',
			'show_group_key'  => false,
			'show_avatars'    => false,
			'update_interval' => 600,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_group_key'  => FILTER_VALIDATE_BOOLEAN,
			'show_avatars'    => FILTER_VALIDATE_BOOLEAN,
			'update_interval' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CheckboxField::make('show_group_key', $this->txt['show_group_key'])
			->setValue($options['show_group_key']);

		CheckboxField::make('show_avatars', $this->txt['show_avatars'])
			->setValue($options['show_avatars']);

		NumberField::make('update_interval', $this->txt['update_interval'])
			->setAttribute('min', 0)
			->setValue($options['update_interval']);
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		if ($this->request()->has('preview')) {
			$parameters['update_interval'] = 0;
		}

		$parameters['show_group_key'] ??= false;
		$parameters['show_avatars'] ??= false;

		$whoIsOnline = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $e->args->cacheTime)
			->setFallback(fn() => $this->getFromSSI('whosOnline', 'array'));

		if (empty($whoIsOnline))
			return;

		echo Lang::getTxt('lp_guests_set', ['guests' => $whoIsOnline['num_guests']]) .
			', ' . Lang::getTxt('lp_users_set', ['users' => $whoIsOnline['num_users_online']]);

		$onlineList = [];

		if (User::$info['buddies'] && $whoIsOnline['num_buddies'])
			$onlineList[] = Lang::getTxt('lp_buddies_set', ['buddies' => $whoIsOnline['num_buddies']]);

		if ($whoIsOnline['num_spiders'])
			$onlineList[] = Lang::getTxt('lp_spiders_set', ['spiders' => $whoIsOnline['num_spiders']]);

		if ($whoIsOnline['num_users_hidden'])
			$onlineList[] = Lang::getTxt('lp_hidden_set', ['hidden' => $whoIsOnline['num_users_hidden']]);

		if ($onlineList)
			echo ' (' . Lang::sentenceList($onlineList) . ')';

		// With avatars
		if ($parameters['show_avatars']) {
			$users = array_map(fn($item) => Avatar::get($item['id']), $whoIsOnline['users_online']);

			$whoIsOnline['list_users_online'] = [];
			foreach ($whoIsOnline['users_online'] as $key => $user) {
				$whoIsOnline['list_users_online'][] = Str::html('a', '')
					->href(Config::$scripturl . '?action=profile;u=' . $user['id'])
					->title($user['name'])
					->addHtml($users[$key]);
			}
		}

		echo Str::html('br') . implode(', ', $whoIsOnline['list_users_online']);

		if ($parameters['show_group_key'] && $whoIsOnline['online_groups']) {
			$groups = [];

			foreach ($whoIsOnline['online_groups'] as $group) {
				if ($group['hidden'] != 0 || $group['id'] == 3)
					continue;

				$color = empty($group['color']) ? null : 'color: ' . $group['color'];

				if (User::hasPermission('view_mlist')) {
					$groups[] = Str::html('a', $group['name'])
						->href(Config::$scripturl . '?action=groups;sa=members;group=' . $group['id'])
						->style($color);
				} else {
					$groups[] = Str::html('span', $group['name'])->style($color);
				}
			}

			echo Str::html('br') . implode(', ', $groups);
		}
	}
}
