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

namespace LightPortal\Utils;

use Throwable;

interface ErrorHandlerInterface
{
	public function log(string $message, string $level, array $context): void;

	public function handle(Throwable $exception): void;

	public function setLevel(string $level): void;

	public function getLevel(): string;

	public function clear(): void;

	public function getLogs(): array;

	public function fatal(string $message, bool $log = true): void;
}
