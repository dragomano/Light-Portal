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

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\DataHandlers\DataHandler;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Closure;
use JetBrains\PhpStorm\NoReturn;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractExport extends DataHandler
{
	use HasRequest;

	public function __construct(
		protected string $entity,
		protected PortalSqlInterface $sql,
		protected FilesystemInterface $filesystem,
		protected ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($sql, $errorHandler);
	}

	abstract protected function getData(): array;

	abstract protected function getFile(): string;

	public function main(): void
	{
		$this->setupUi();
		$this->run();
	}

	protected function setupUi(): void
	{
		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_' . $this->entity . '_export'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_' . $this->entity . '_export'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_' . $this->entity . ';sa=export';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_' . $this->entity . '_export_description'],
		];
	}

	protected function run(): void
	{
		if (empty($file = $this->getFile()))
			return;

		$this->downloadFile($file);
	}

	protected function downloadFile(string $file): void
	{
		Sapi::setTimeLimit();

		if (! $this->fileExists($file))
			return;

		$this->obEndClean();

		$this->headerRemove('content-encoding');
		$this->sendHeader('Content-Description: File Transfer');
		$this->sendHeader('Content-Type: application/octet-stream');
		$this->sendHeader('Content-Disposition: attachment; filename=' . $this->basename($file));
		$this->sendHeader('Content-Transfer-Encoding: binary');
		$this->sendHeader('Expires: 0');
		$this->sendHeader('Cache-Control: must-revalidate');
		$this->sendHeader('Pragma: public');
		$this->sendHeader('Content-Length: ' . $this->fileSize($file));

		if ($fd = $this->fopen($file, 'rb')) {
			while (! $this->feof($fd)) {
				print $this->fread($fd, 1024);
			}

			$this->fclose($fd);
		}

		$this->unlink($file);

		$this->doExit();
	}

	protected function getGeneratorFrom(array $items): Closure
	{
		return static fn() => yield from $items;
	}

	protected function isEntityEmpty(): bool
	{
		return $this->request()->isEmpty($this->entity) && $this->request()->hasNot('export_all');
	}

	protected function hasEntityInRequest(): bool
	{
		return $this->request()->get($this->entity) && $this->request()->hasNot('export_all');
	}

	public function getEntity(): string
	{
		return $this->entity;
	}

	protected function fileExists(string $file): bool
	{
		return $this->filesystem->exists($file);
	}

	protected function obEndClean(): void
	{
		ob_end_clean();
	}

	protected function headerRemove(string $header): void
	{
		header_remove($header);
	}

	protected function sendHeader(string $header): void
	{
		header($header);
	}

	protected function basename(string $path): string
	{
		return basename($path);
	}

	protected function fileSize(string $file): int
	{
		return $this->filesystem->getSize($file);
	}

	protected function fopen(string $file, string $mode)
	{
		return $this->filesystem->openFile($file, $mode);
	}

	protected function feof($handle): bool
	{
		return $this->filesystem->isEndOfFile($handle);
	}

	protected function fread($handle, int $length): string
	{
		return $this->filesystem->readFile($handle, $length);
	}

	protected function fclose($handle): bool
	{
		return $this->filesystem->closeFile($handle);
	}

	protected function unlink(string $file): bool
	{
		return $this->filesystem->delete($file);
	}

	#[NoReturn]
	protected function doExit(): void
	{
		exit;
	}
}
