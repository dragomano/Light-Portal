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

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\ErrorHandler;
use Fiber;
use Throwable;

class Weaver
{
	public function __invoke(callable $callback): array
	{
		$fiber = new Fiber(function () use ($callback) {
			$data = $callback();
			Fiber::suspend($data);
		});

		try {
			return $fiber->start();
		} catch (Throwable $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return [];
	}
}
