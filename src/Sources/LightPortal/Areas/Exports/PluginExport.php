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

namespace Bugo\LightPortal\Areas\Exports;

use AppendIterator;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Lists\PluginList;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

use function count;
use function substr;
use function strlen;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class PluginExport extends AbstractExport
{
	protected string $entity = 'plugins';

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_export_plugins';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_plugins_export'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_plugins_export'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_plugins;sa=export';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_plugins_export_description'],
		];

		Utils::$context['lp_plugins'] = app(PluginList::class)();

		$this->run();
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
		$archive  = count($dirs) === 1 ? $dirs[0] : 'lp_plugins';
		$filename = Sapi::getTempDir() . DIRECTORY_SEPARATOR . $archive . '.zip';

		$zip = new ZipArchive();
		$zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$zip->setCompressionIndex(ZipArchive::CM_DEFAULT, ZipArchive::CM_DEFLATE);

		$iterator = new AppendIterator();
		foreach ($dirs as $dir) {
			$iterator->append(new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					LP_ADDON_DIR . DIRECTORY_SEPARATOR . $dir,
					FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
				)
			));
		}

		foreach ($iterator as $file) {
			$localname = substr((string) $file->getPathname(), strlen(LP_ADDON_DIR) + 1);
			$zip->addFile($file->getPathname(), $localname);
		}

		$zip->close();

		return $filename;
	}
}
