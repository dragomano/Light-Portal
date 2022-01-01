<?php

declare(strict_types = 1);

/**
 * File.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Utils;

final class File
{
	protected array $storage = [];

	public function __construct(?string $key = null)
	{
		$this->storage = &$_FILES;

		if ($key) {
			$storage = $this->storage[$key] ?? [];
			$this->storage = &$storage;
		}
	}

	public function get(): array
	{
		return $this->storage ? (is_array($this->storage['name']) ? $this->reArrayFiles($this->storage) : $this->storage) : [];
	}

	public function free(string $key)
	{
		unset($this->storage[$key]);
	}

	private function reArrayFiles(array $source): array
	{
		$files = [];
		$numFiles = count($source['name']);
		$keys = array_keys($source);

		for ($i = 0; $i < $numFiles; $i++) {
			if (empty($source['name'][$i]))
				continue;

			foreach ($keys as $key) {
				$files[$i][$key] = $source[$key][$i];
			}
		}

		return $files;
	}
}
