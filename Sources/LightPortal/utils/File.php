<?php

namespace Bugo\LightPortal\Utils;

/**
 * File.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

final class File
{
	/**
	 * @var array
	 */
	protected $storage = [];

	/**
	 * @param string|null $key
	 */
	public function __construct(?string $key)
	{
		$this->storage = &$_FILES;

		if ($key) {
			$this->storage = &$this->storage[$key] ?? [];
		}
	}

	/**
	 * @return array
	 */
	public function get(): array
	{
		return $this->storage ? (is_array($this->storage['name']) ? $this->reArrayFiles($this->storage) : $this->storage) : [];
	}

	/**
	 * @param string $key
	 * @return void
	 */
	public function free(string $key)
	{
		unset($this->storage[$key]);
	}

	/**
	 * @param array $source
	 * @return array
	 */
	private function reArrayFiles(&$source)
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
