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

namespace LightPortal\Utils;

use RuntimeException;

if (! defined('SMF'))
	die('No direct access...');

class Filesystem implements FilesystemInterface
{
	public function read(string $path): string
	{
		if (! $this->exists($path)) {
			throw new RuntimeException("File does not exist: $path");
		}

		$content = file_get_contents($path);

		if ($content === false) {
			throw new RuntimeException("Cannot read file: $path");
		}

		return $content;
	}

	public function write(string $path, string $content): bool
	{
		$result = file_put_contents($path, $content);

		if ($result === false) {
			throw new RuntimeException("Cannot write to file: $path");
		}

		return true;
	}

	public function exists(string $path): bool
	{
		return file_exists($path);
	}

	public function delete(string $path): bool
	{
		if ($this->isDir($path)) {
			$result = $this->deleteDirectory($path);
		} else {
			$result = unlink($path);
		}

		if (! $result) {
			throw new RuntimeException("Cannot delete path: $path");
		}

		return true;
	}

	public function listDir(string $path): array
	{
		if (! $this->isDir($path)) {
			throw new RuntimeException("Path is not a directory: $path");
		}

		$contents = scandir($path);

		if ($contents === false) {
			throw new RuntimeException("Cannot read directory: $path");
		}

		return array_diff($contents, ['.', '..']);
	}

	public function isDir(string $path): bool
	{
		return is_dir($path);
	}

	public function isFile(string $path): bool
	{
		return is_file($path);
	}

	public function mkdir(string $path, int $mode = 0755): bool
	{
		if ($this->exists($path)) {
			if ($this->isDir($path)) {
				return true;
			}

			throw new RuntimeException("Path exists but is not a directory: $path");
		}

		$result = mkdir($path, $mode, true);

		if (! $result) {
			throw new RuntimeException("Cannot create directory: $path");
		}

		return true;
	}

	public function copy(string $source, string $destination): bool
	{
		if (! $this->exists($source)) {
			throw new RuntimeException("Source does not exist: $source");
		}

		if ($this->isDir($source)) {
			$result = $this->copyDirectory($source, $destination);
		} else {
			$result = copy($source, $destination);
		}

		if (! $result) {
			throw new RuntimeException("Cannot copy from $source to $destination");
		}

		return true;
	}

	public function move(string $source, string $destination): bool
	{
		if (! $this->exists($source)) {
			throw new RuntimeException("Source does not exist: $source");
		}

		$result = rename($source, $destination);

		if (! $result) {
			throw new RuntimeException("Cannot move from $source to $destination");
		}

		return true;
	}

	public function getSize(string $path): int
	{
		if (! $this->exists($path)) {
			throw new RuntimeException("File does not exist: $path");
		}

		if (! $this->isFile($path)) {
			throw new RuntimeException("Path is not a file: $path");
		}

		$size = filesize($path);

		if ($size === false) {
			throw new RuntimeException("Cannot get file size: $path");
		}

		return $size;
	}

	public function getPermissions(string $path): int
	{
		if (! $this->exists($path)) {
			throw new RuntimeException("Path does not exist: $path");
		}

		$perms = fileperms($path);

		if ($perms === false) {
			throw new RuntimeException("Cannot get permissions for: $path");
		}

		return $perms;
	}

	public function openFile(string $path, string $mode)
	{
		$handle = fopen($path, $mode);

		if ($handle === false) {
			throw new RuntimeException("Cannot open file: $path");
		}

		return $handle;
	}

	public function readFile($handle, int $length): string
	{
		$data = fread($handle, $length);

		if ($data === false) {
			return '';
		}

		return $data;
	}

	public function isEndOfFile($handle): bool
	{
		return feof($handle);
	}

	public function closeFile($handle): bool
	{
		return fclose($handle);
	}

	private function deleteDirectory(string $dir): bool
	{
		$files = array_diff(scandir($dir), ['.', '..']);

		foreach ($files as $file) {
			$path = $dir . DIRECTORY_SEPARATOR . $file;

			if (is_dir($path)) {
				$this->deleteDirectory($path);
			} else {
				unlink($path);
			}
		}

		return rmdir($dir);
	}

	private function copyDirectory(string $src, string $dst): bool
	{
		if (! $this->exists($dst)) {
			$this->mkdir($dst);
		}

		$files = array_diff(scandir($src), ['.', '..']);

		foreach ($files as $file) {
			$srcPath = $src . DIRECTORY_SEPARATOR . $file;
			$dstPath = $dst . DIRECTORY_SEPARATOR . $file;

			if (is_dir($srcPath)) {
				$this->copyDirectory($srcPath, $dstPath);
			} else {
				copy($srcPath, $dstPath);
			}
		}

		return true;
	}
}
