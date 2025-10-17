<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace Bugo\LightPortal\DataHandlers\Imports;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\DataHandlers\DataHandler;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use SimpleXMLElement;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractImport extends DataHandler
{
	use HasRequest;

	public function __construct(
		protected PortalSqlInterface $sql,
		protected FileInterface $file,
		protected ErrorHandlerInterface $errorHandler,
		protected bool|SimpleXMLElement $xml = false
	)
	{
		parent::__construct($sql, $errorHandler);

		Utils::$context['max_file_size'] = Sapi::memoryReturnBytes(ini_get('upload_max_filesize'));
	}

	public function files(): FileInterface
	{
		return $this->file;
	}

	public function main(): void
	{
		$this->setupUi();
		$this->run();
	}

	abstract protected function run(): void;

	protected function setupUi(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_' . $this->entity . '_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_' . $this->entity . '_import'];
		Utils::$context['page_area_info'] = Lang::$txt['lp_' . $this->entity . '_import_info'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_' . $this->entity . ';sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_' . $this->entity . '_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';
	}

	protected function getFile(string $name = 'import_file'): SimpleXMLElement|bool
	{
		if (empty($file = $this->files()->get($name)))
			return false;

		Sapi::setTimeLimit();

		if ($file['type'] !== 'text/xml')
			return false;

		return simplexml_load_file($file['tmp_name']);
	}
}
