<?php declare(strict_types=1);

/**
 * PluginRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class PluginRepository
{
	use Helper;

	public function addSettings(array $settings = []): void
	{
		if (empty($settings))
			return;

		Utils::$smcFunc['db_insert']('replace',
			'{db_prefix}lp_plugins',
			[
				'name'   => 'string',
				'config' => 'string',
				'value'  => 'string',
			],
			$settings,
			['name', 'config']
		);

		Utils::$context['lp_num_queries']++;

		$this->cache()->forget('plugin_settings');
	}

	public function getSettings(): array
	{
		if (($settings = $this->cache()->get('plugin_settings', 259200)) === null) {
			$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
				SELECT name, config, value
				FROM {db_prefix}lp_plugins',
				[]
			);

			$settings = [];
			while ($row = Utils::$smcFunc['db_fetch_assoc']($result))
				$settings[$row['name']][$row['config']] = $row['value'];

			Utils::$smcFunc['db_free_result']($result);
			Utils::$context['lp_num_queries']++;

			$this->cache()->put('plugin_settings', $settings, 259200);
		}

		return $settings;
	}

	public function changeSettings(string $plugin_name, array $settings = []): void
	{
		if (empty($settings))
			return;

		$new_settings = $old_settings = [];
		foreach ($settings as $config => $value) {
			if (empty($value))
				$old_settings[] = $config;

			if ($value) {
				$new_settings[] = [
					'name'   => $plugin_name,
					'config' => $config,
					'value'  => $value,
				];
			}
		}

		$this->removeSettings($plugin_name, $old_settings);

		$this->addSettings($new_settings);
	}

	public function removeSettings(string $plugin_name, array $settings = []): void
	{
		if (empty($settings))
			return;

		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_plugins
			WHERE name = {string:name}
				AND config IN ({array_string:settings})',
			[
				'name'     => $plugin_name,
				'settings' => $settings,
			]
		);

		Utils::$context['lp_num_queries']++;

		$this->cache()->forget('plugin_settings');
	}
}
