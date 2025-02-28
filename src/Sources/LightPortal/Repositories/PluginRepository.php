<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\Db;
use Bugo\LightPortal\Utils\Traits\HasCache;

if (! defined('SMF'))
	die('No direct access...');

final class PluginRepository
{
	use HasCache;

	public function addSettings(array $settings = []): void
	{
		if ($settings === [])
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

		$this->cache()->forget('plugin_settings');
	}

	public function getSettings(): array
	{
		$cacheTTL = 3 * 24 * 60 * 60;

		if (($settings = $this->cache()->get('plugin_settings', $cacheTTL)) === null) {
			$result = Db::$db->query('', /** @lang text */ '
				SELECT name, config, value
				FROM {db_prefix}lp_plugins',
			);

			$settings = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$settings[$row['name']][$row['config']] = $row['value'];
			}

			Db::$db->free_result($result);

			$this->cache()->put('plugin_settings', $settings, $cacheTTL);
		}

		return $settings;
	}

	public function changeSettings(string $name, array $settings = []): void
	{
		if ($settings === [])
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
		if ($settings === [])
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

		$this->cache()->forget('plugin_settings');
	}
}
