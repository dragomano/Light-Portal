<?php declare(strict_types=1);

use Bugo\LightPortal\Migrations\Installer;

class PortalSetup
{
	private array $classMap = [];
	private ?object $installer = null;

	private static function checkRequirements(): void
	{
		if (version_compare(PHP_VERSION, '8.2', '<')) {
			die('This mod needs PHP 8.2 or greater. You will not be able to install/use this mod. Please, contact your host and ask for a php upgrade.');
		}

		if (! extension_loaded('intl')) {
			die('This mod needs intl extension to properly work with plurals, locale-aware numbers, and much more. Contact your host or install this extension by manual.');
		}
	}

	public static function init(): self
	{
		self::checkRequirements();

		$instance = new self();

		$instance->setupAutoload();

		include_once __DIR__ . '/Sources/LightPortal/Libs/bugo/smf-compat/src/app.php';

		$instance->installer = new Installer();

		return $instance;
	}

	private function setupAutoload(): void
	{
		$autoload = __DIR__ . '/Sources/LightPortal/Libs/composer/autoload_classmap.php';
		if (file_exists($autoload)) {
			$this->classMap = include $autoload;
		}

		spl_autoload_register(function ($class) {
			if (isset($this->classMap[$class]) && file_exists($this->classMap[$class])) {
				include_once $this->classMap[$class];
			}
		});
	}

	public function getInstaller(): object
	{
		return $this->installer;
	}

	public function copyPortalFiles(): void
	{
		self::copyDirectory(__DIR__ . '/Sources/LightPortal', dirname(__DIR__, 2) . '/Sources/LightPortal');
	}

	public function deletePortalFiles(): void
	{
		self::deleteDirectory(dirname(__DIR__, 2) . '/Sources/LightPortal');
	}

	public function handleError(string $message): void
	{
		echo "Error: " . $message;
	}

	public function finalize(string $message): void
	{
		echo $message;
	}

	public static function copyDirectory(string $source, string $destination): bool
	{
		if (! is_dir($source)) {
			throw new RuntimeException("Source directory '$source' does not exist or is not a directory.");
		}

		if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
			throw new RuntimeException("Failed to create destination directory '$destination'.");
		}

		$directory = new RecursiveDirectoryIterator(
			$source,
			FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
		);
		$iterators = new RecursiveIteratorIterator(
			$directory,
			RecursiveIteratorIterator::SELF_FIRST
		);

		/** @var SplFileInfo $item */
		foreach ($iterators as $item) {
			$subPathName = str_replace($source . DIRECTORY_SEPARATOR, '', $item->getPathname());
			$targetPath = $destination . DIRECTORY_SEPARATOR . $subPathName;

			if ($item->isDir()) {
				if (! is_dir($targetPath) && ! mkdir($targetPath, 0777, true) && ! is_dir($targetPath)) {
					throw new RuntimeException("Failed to create directory '$targetPath'.");
				}
			} elseif ($item->isFile()) {
				if (! copy($item->getPathname(), $targetPath)) {
					throw new RuntimeException("Failed to copy file '{$item->getPathname()}' to '$targetPath'.");
				}
			}
		}

		return true;
	}

	public static function deleteDirectory(string $dir): bool
	{
		if (! is_dir($dir)) {
			return false;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			if ($item->isDir()) {
				rmdir($item->getPathname());
			} else {
				unlink($item->getPathname());
			}
		}

		return rmdir($dir);
	}
}
