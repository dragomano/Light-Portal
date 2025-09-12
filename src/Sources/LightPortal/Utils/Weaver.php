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
use Traversable;

class Weaver
{
	public function __invoke(callable $callback, bool $asArray = true): iterable
	{
		$fiber = new Fiber(function () use ($callback) {
			$data = $callback();
			Fiber::suspend($data);
		});

		try {
			$data = $fiber->start();

			if ($asArray && $data instanceof Traversable) {
				return iterator_to_array($data);
			}

			return $data;
		} catch (Throwable $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return [];
	}
}
