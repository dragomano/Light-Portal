<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Utils\Traits\HasCache;

if (! defined('SMF'))
	die('No direct access...');

final class PluginRepository implements PluginRepositoryInterface
{
	use HasCache;

	public function __construct(protected PortalSqlInterface $sql) {}

	public function addSettings(array $settings = []): void
	{
		if ($settings === [])
			return;

		$replace = $this->sql->replace('lp_plugins')->batch($settings);

		$this->sql->execute($replace);

		$this->cache()->forget('plugin_settings');
	}

	public function getSettings(): array
	{
		return $this->cache()->remember('plugin_settings', function () {
			$select = $this->sql->select('lp_plugins')
				->columns(['name', 'config', 'value']);

			$result = $this->sql->execute($select);

			$settings = [];
			foreach ($result as $row) {
				$settings[$row['name']][$row['config']] = $row['value'];
			}

			return $settings;
		}, 3 * 24 * 60 * 60);
	}

	public function changeSettings(string $name, array $settings = []): void
	{
		if ($settings === [])
			return;

		$newSettings = [];
		foreach ($settings as $config => $value) {
			$newSettings[] = [
				'name'   => $name,
				'config' => $config,
				'value'  => $value,
			];
		}

		$this->addSettings($newSettings);

		$this->cache()->forget('plugin_settings');
	}
}
