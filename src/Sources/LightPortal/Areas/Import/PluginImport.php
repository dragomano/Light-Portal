<?php declare(strict_types=1);

/**
 * PluginImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Import;

use Bugo\LightPortal\Utils\{Config,Lang, Utils};
use Exception;
use ZipArchive;

if (! defined('SMF'))
	die('No direct access...');

final class PluginImport extends AbstractImport
{
	public function main(): void
	{
		$this->loadTemplate('LightPortal/ManageImpex', 'manage_import');

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_plugins_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_plugins_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_plugins_import_info'];
		Utils::$context['canonical_url']   = Config::$scripturl . '?action=admin;area=lp_plugins;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_plugins_import_description']
		];

		Utils::$context['lp_file_type'] = 'application/zip';

		$this->run();
	}

	protected function run(): void
	{
		if (empty($this->extractPackage()))
			return;

		Utils::$context['import_successful'] = Lang::$txt['lp_plugins_import_success'];
	}

	protected function extractPackage(): bool
	{
		$file = $this->files('import_file');
		if (empty($file) || $file['error'] !== UPLOAD_ERR_OK)
			return false;

		switch ($file['type']) {
			case 'application/zip':
			case 'application/x-zip':
			case 'application/x-zip-compressed':
				break;
			default:
				return false;
		}

		try {
			$zip = new ZipArchive();
			$zip->open($file['tmp_name']);
			$zip->deleteName('package-info.xml');

			$plugin = pathinfo($file['name'], PATHINFO_FILENAME);
			$pluginPhp = $plugin . '/' . $plugin . '.php';
			$addonDir = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $plugin;

			if ($zip->locateName($pluginPhp) !== false) {
				return $zip->extractTo(LP_ADDON_DIR);
			} elseif ($zip->locateName($plugin . '.php') !== false) {
				return $zip->extractTo($addonDir);
			}

			for ($i = 0; $i < $zip->numFiles; $i++) {
				$fileInfoArr = $zip->statIndex($i);

				if (! str_contains($fileInfoArr['name'], '/')) {
					continue;
				}

				[$dirName, $fileName] = explode('/', $fileInfoArr['name'], 2);
				if ($fileName === $dirName . '.php') {
					return $zip->extractTo(LP_ADDON_DIR);
				}
			}

			$this->fatalLangError('lp_wrong_import_file');
		} catch (Exception) {
			$this->fatalLangError('lp_import_failed');
		}

		return false;
	}
}
