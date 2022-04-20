<?php declare(strict_types=1);

/**
 * PluginRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class PluginRepository
{
	use Helper;

	public function addSettings(array $params = [])
	{
		if (empty($params))
			return;

		$this->smcFunc['db_insert']('replace',
			'{db_prefix}lp_plugins',
			[
				'name'   => 'string',
				'option' => 'string',
				'value'  => 'string',
			],
			$params,
			['name', 'option']
		);

		$this->context['lp_num_queries']++;

		$this->cache()->forget('plugin_settings');
	}

	public function getSettings(): array
	{
		if (($settings = $this->cache()->get('plugin_settings', 259200)) === null) {
			$request = $this->smcFunc['db_query']('', /** @lang text */ '
				SELECT name, option, value
				FROM {db_prefix}lp_plugins',
				[]
			);

			$settings = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($request))
				$settings[$row['name']][$row['option']] = $row['value'];

			$this->smcFunc['db_free_result']($request);
			$this->context['lp_num_queries']++;

			$this->cache()->put('plugin_settings', $settings, 259200);
		}

		return $settings;
	}
}
