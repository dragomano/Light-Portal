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

if (! defined('LP_NAME'))
	die('No direct access...');

class SettingsFactory
{
	private array $settings = [];

	public static function make(): self
	{
		return new self();
	}

	private function add(string $type, string $key, ...$args): self
	{
		$this->settings[] = [$type, $key, ...$args];

		return $this;
	}

	public function toArray(): array
	{
		return $this->settings;
	}

	public function multiselect(string $key, array $options): self
	{
		return $this->add('multiselect', $key, $options);
	}

	public function select(string $key, array $options, array $extra = []): self
	{
		return $this->add('select', $key, $options, ...$extra);
	}

	public function text(string $key, array $extra = []): self
	{
		return $this->add('text', $key, ...$extra);
	}

	public function check(string $key, array $extra = []): self
	{
		return $this->add('check', $key, ...$extra);
	}

	public function color(string $key): self
	{
		return $this->add('color', $key);
	}

	public function int(string $key, array $extra = []): self
	{
		return $this->add('int', $key, ...$extra);
	}

	public function float(string $key): self
	{
		return $this->add('float', $key);
	}

	public function url(string $key): self
	{
		return $this->add('url', $key);
	}

	public function range(string $key, array $extra = []): self
	{
		return $this->add('range', $key, ...$extra);
	}

	public function desc(string $key): self
	{
		return $this->add('desc', $key);
	}

	public function title(string $key): self
	{
		return $this->add('title', $key);
	}

	public function custom(string $key, string $text): self
	{
		return $this->add('callback', $key, $text);
	}
}
