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
 * @version 2.5
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\{Database as Db, Utils};
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class PluginRepository
{
	use Helper;

	public function addSettings(array $settings = []): void
	{
		if (empty($settings))
			return;

		Db::$db->insert('replace',
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
			$result = Db::$db->query('', /** @lang text */ '
				SELECT name, config, value
				FROM {db_prefix}lp_plugins',
				[]
			);

			$settings = [];
			while ($row = Db::$db->fetch_assoc($result))
				$settings[$row['name']][$row['config']] = $row['value'];

			Db::$db->free_result($result);
			Utils::$context['lp_num_queries']++;

			$this->cache()->put('plugin_settings', $settings, 259200);
		}

		return $settings;
	}

	public function changeSettings(string $name, array $settings = []): void
	{
		if (empty($settings))
			return;

		$newSettings = $oldSettings = [];
		foreach ($settings as $config => $value) {
			if (empty($value))
				$oldSettings[] = $config;

			if ($value) {
				$newSettings[] = [
					'name'   => $name,
					'config' => $config,
					'value'  => $value,
				];
			}
		}

		$this->removeSettings($name, $oldSettings);

		$this->addSettings($newSettings);
	}

	public function removeSettings(string $name, array $settings = []): void
	{
		if (empty($settings))
			return;

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_plugins
			WHERE name = {string:name}
				AND config IN ({array_string:settings})',
			[
				'name'     => $name,
				'settings' => $settings,
			]
		);

		Utils::$context['lp_num_queries']++;

		$this->cache()->forget('plugin_settings');
	}
}
