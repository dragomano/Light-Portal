<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\DataHandlers\Imports;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Exception;
use ZipArchive;

if (! defined('SMF'))
	die('No direct access...');

class PluginImport extends AbstractImport
{
	protected string $entity = 'plugins';

	public function getEntity(): string
	{
		return $this->entity;
	}

	protected function setupUi(): void
	{
		parent::setupUi();

		Utils::$context['lp_file_type'] = 'application/zip';
	}

	protected function run(): void
	{
		if ($this->extractPackage() === false)
			return;

		Utils::$context['import_successful'] = Lang::$txt['lp_plugins_import_success'];
	}

	protected function extractPackage(): bool
	{
		$file = $this->files()->get('import_file');

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
			if ($zip->open($file['tmp_name']) !== true) {
				return false;
			}

			$plugin = pathinfo((string) $file['name'], PATHINFO_FILENAME);
			$pluginPhp = $plugin . '/' . $plugin . '.php';
			$addonDir = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $plugin;

			if ($zip->locateName($pluginPhp) !== false) {
				return $zip->extractTo(LP_ADDON_DIR);
			}

			if ($zip->locateName($plugin . '.php') !== false) {
				return $zip->extractTo($addonDir);
			}

			$this->errorHandler->fatal('lp_wrong_import_file', false);
		} catch (Exception) {
			$this->errorHandler->fatal('lp_import_failed', false);
		}

		return false;
	}
}
