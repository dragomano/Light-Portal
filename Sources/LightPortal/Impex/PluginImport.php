<?php declare(strict_types=1);

/**
 * PluginImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.2
 */

namespace Bugo\LightPortal\Impex;

use PharData;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

final class PluginImport extends AbstractImport
{
	public function main(): void
	{
		$this->loadTemplate('LightPortal/ManageImpex', 'manage_import');

		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_plugins_import'];
		$this->context['page_area_title'] = $this->txt['lp_plugins_import'];
		$this->context['page_area_info']  = $this->txt['lp_plugins_import_info'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_plugins;sa=import';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_plugins_import_description']
		];

		$this->context['lp_file_type'] = 'application/zip';

		$this->run();
	}

	protected function run(): void
	{
		if (empty($this->extractPackage()))
			return;

		$this->context['import_successful'] = $this->txt['lp_plugins_import_success'];
	}

	protected function extractPackage(): bool
	{
		$file = $this->files('import_file');
		if (empty($file) || ! empty($file['error']))
			return false;

		switch ($file['type']) {
			case 'application/zip':
			case 'application/x-zip':
			case 'application/x-zip-compressed':
			case 'application/octet-stream':
			case 'application/x-compress':
			case 'application/x-compressed':
			case 'multipart/x-zip':
				break;
			default:
				return false;
		}

		try {
			$phar = new PharData($file['tmp_name']);
			$phar->offsetUnset('package-info.xml');

			$plugin = pathinfo($file['name'], PATHINFO_FILENAME);

			if ($phar->offsetExists($plugin . '/' . $plugin . '.php') !== false) {
				return $phar->extractTo(LP_ADDON_DIR, null, true);
			}

			if ($phar->offsetExists($plugin . '.php') !== false) {
				return $phar->extractTo(LP_ADDON_DIR . DIRECTORY_SEPARATOR . $plugin, null, true);
			}

			foreach ($phar as $f) {
				if ($f->isDir() && $phar->offsetExists($f->getBasename() . '/' . $f->getBasename() . '.php')) {
					return $phar->extractTo(LP_ADDON_DIR, null, true);
				}
			}

			$this->fatalLangError('lp_wrong_import_file');
		} catch (Exception) {
			$this->fatalLangError('lp_import_failed');
		}

		return false;
	}
}
