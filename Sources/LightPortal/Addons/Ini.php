<?php declare(strict_types=1);

/**
 * Ini.php
 *
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Addons;

/**
 * @see https://habr.com/ru/post/519952/
 */
final class Ini
{
	private array $structure = [];

	private int $scannerMode;

	private string $path;

	public function __construct(string $path, int $scannerMode = INI_SCANNER_TYPED)
	{
		$this->path = $path;

		file_put_contents($path, null, LOCK_EX | FILE_APPEND);

		$this->scannerMode = $scannerMode;

		$this->setInitStructure();
	}

	public function getStructure(): array
	{
		return $this->structure;
	}

	public function getSection(string $section): array
	{
		return $this->structure[$section];
	}

	public function getValue(string $section, string $key)
	{
		return $this->getSection($section)[$key];
	}

	public function addSection(string $section): Ini
	{
		if (! array_key_exists($section, $this->structure)) {
			$this->structure[$section] = [];
		}

		return $this;
	}

	public function addValues(string $section, array $values): Ini
	{
		$this->structure[$section] = array_merge($values, $this->structure[$section]);

		return $this;
	}

	public function setValues(string $section, array $values): Ini
	{
		foreach ($values as $key => $value) {
			$this->structure[$section][$key] = $value;
		}

		return $this;
	}

	public function removeSection(string $section): Ini
	{
		unset($this->structure[$section]);

		return $this;
	}

	public function removeKeys(string $section, array $keys): Ini
	{
		foreach ($keys as $key) {
			unset($this->structure[$section][$key]);
		}

		return $this;
	}

	public function write()
	{
		$iniContent = null;

		ksort($this->structure);

		foreach ($this->structure as $section => $data) {
			$iniContent .= "[{$section}]" . PHP_EOL;

			foreach ($data as $key => $value) {
				$value = var_export($value, true);
				$iniContent .= "{$key} = {$value}" . PHP_EOL;
			}

			$iniContent .= PHP_EOL;
		}

		file_put_contents($this->path, $iniContent, LOCK_EX);

		$this->setInitStructure();
	}

	private function setInitStructure()
	{
		$this->structure = parse_ini_file($this->path, true, $this->scannerMode);
	}
}
