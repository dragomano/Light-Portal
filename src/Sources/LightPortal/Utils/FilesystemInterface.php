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

namespace Bugo\LightPortal\Utils;

interface FilesystemInterface
{
	public function read(string $path): string;

	public function write(string $path, string $content): bool;

	public function exists(string $path): bool;

	public function delete(string $path): bool;

	public function listDir(string $path): array;

	public function isDir(string $path): bool;

	public function isFile(string $path): bool;

	public function mkdir(string $path, int $mode = 0755): bool;

	public function copy(string $source, string $destination): bool;

	public function move(string $source, string $destination): bool;

	public function getSize(string $path): int;

	public function getPermissions(string $path): int;

	public function openFile(string $path, string $mode);

	public function readFile($handle, int $length): string;

	public function isEndOfFile($handle): bool;

	public function closeFile($handle): bool;
}
