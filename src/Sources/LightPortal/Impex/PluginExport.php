<?php declare(strict_types=1);

/**
 * PluginExport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Impex;

use ZipArchive;
use AppendIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

if (! defined('SMF'))
	die('No direct access...');

final class PluginExport extends AbstractExport
{
	public function main(): void
	{
		$this->loadTemplate('LightPortal/ManageImpex', 'manage_export_plugins');

		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_plugins_export'];
		$this->context['page_area_title'] = $this->txt['lp_plugins_export'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_plugins;sa=export';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_plugins_export_description']
		];

		$this->context['lp_plugins'] = $this->getEntityList('plugin');

		$this->run();
	}

	protected function getData(): array
	{
		if ($this->request()->isEmpty('plugins') && $this->request()->hasNot('export_all'))
			return [];

		return $this->request()->has('export_all') ? $this->context['lp_plugins'] : $this->request('plugins');
	}

	protected function getFile(): string
	{
		if (empty($dirs = $this->getData()))
			return '';

		return $this->createPackage($dirs) ?? '';
	}

	protected function createPackage(array $dirs): string
	{
		$archive  = count($dirs) === 1 ? $dirs[0] : 'lp_plugins';
		$filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $archive . '.zip';

		$zip = new ZipArchive();
		$zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$zip->setCompressionIndex(ZipArchive::CM_DEFAULT, ZipArchive::CM_DEFLATE);

		$iterator = new AppendIterator();
		foreach ($dirs as $dir) {
			$iterator->append(new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(LP_ADDON_DIR . DIRECTORY_SEPARATOR . $dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS)
			));
		}

		foreach ($iterator as $file) {
			$localname = substr($file->getPathname(), strlen(LP_ADDON_DIR) + 1);
			$zip->addFile($file->getPathname(), $localname);
		}

		$zip->close();

		return $filename;
	}
}
