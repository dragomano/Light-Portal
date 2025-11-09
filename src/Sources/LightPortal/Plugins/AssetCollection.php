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

namespace LightPortal\Plugins;

use Bugo\Compat\Config;

if (! defined('LP_NAME'))
	die('No direct access...');

abstract class AssetCollection
{
	protected array $items = [];

	protected string $type;

	public function __construct(protected string $pluginName) {}

	public function add(string $filename): static
	{
		$url = $this->buildUrl($filename);
		$this->items[] = $url;

		return $this;
	}

	public function addMultiple(array $filenames): static
	{
		foreach ($filenames as $filename) {
			$this->add($filename);
		}

		return $this;
	}

	public function toArray(): array
	{
		return $this->items;
	}

	protected function buildUrl(string $filename): string
	{
		if (str_starts_with($filename, 'http') || str_starts_with($filename, '//')) {
			return $filename;
		}

		return Config::$boardurl . '/Sources/LightPortal/Plugins/' . $this->pluginName . '/' . $filename;
	}

	public function getType(): string
	{
		return $this->type;
	}
}
