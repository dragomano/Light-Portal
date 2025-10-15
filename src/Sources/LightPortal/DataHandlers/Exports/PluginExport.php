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

namespace Bugo\LightPortal\DataHandlers\Exports;

use AppendIterator;
use Bugo\Compat\Sapi;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

use function Bugo\LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

class PluginExport extends AbstractExport
{
	protected string $entity = 'plugins';

	public function __construct(
		PortalSqlInterface $sql,
		FilesystemInterface $filesystem,
		ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($this->entity, $sql, $filesystem, $errorHandler);
	}

	protected function setupUi(): void
	{
		parent::setupUi();

		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_export_plugins';

		Utils::$context['lp_plugins'] = app(PluginList::class)();
	}

	protected function getData(): array
	{
		if ($this->isEntityEmpty())
			return [];

		return $this->request()->has('export_all')
			? Utils::$context['lp_plugins']
			: $this->request()->get($this->entity);
	}

	protected function getFile(): string
	{
		if (empty($dirs = $this->getData()))
			return '';

		return $this->createPackage($dirs) ?? '';
	}

	protected function createPackage(array $dirs): string
	{
		$addonDir = Utils::$context['lp_addon_dir'] ?? LP_ADDON_DIR;
		$archive  = count($dirs) === 1 ? $dirs[0] : 'lp_plugins';
		$filename = Sapi::getTempDir() . DIRECTORY_SEPARATOR . $archive . '.zip';

		$zip = new ZipArchive();
		if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
			return '';
		}

		$zip->setCompressionIndex(ZipArchive::CM_DEFAULT, ZipArchive::CM_DEFLATE);

		$iterator = new AppendIterator();
		foreach ($dirs as $dir) {
			$pluginDir = $addonDir . DIRECTORY_SEPARATOR . $dir;
			if (! is_dir($pluginDir)) {
				$zip->close();
				return '';
			}
			$iterator->append(new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$pluginDir,
					FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
				)
			));
		}

		foreach ($iterator as $file) {
			$name = substr((string) $file->getPathname(), strlen($addonDir) + 1);
			$zip->addFile($file->getPathname(), $name);
		}

		$zip->close();

		return $filename;
	}
}
