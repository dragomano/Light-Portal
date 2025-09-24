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

namespace Bugo\LightPortal\Utils\Traits;

use Bugo\LightPortal\Utils\FileInterface;
use Bugo\LightPortal\Utils\PostInterface;
use Bugo\LightPortal\Utils\RequestInterface;

use function Bugo\LightPortal\app;

trait HasRequest
{
	public function request(): RequestInterface
	{
		return app(RequestInterface::class);
	}

	public function post(): PostInterface
	{
		return app(PostInterface::class);
	}

	public function files(): FileInterface
	{
		return app(FileInterface::class);
	}
}
