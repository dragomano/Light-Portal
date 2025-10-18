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

use Bugo\Compat\ErrorHandler as SmfErrorHandler;
use Throwable;

if (! defined('SMF'))
	die('No direct access...');

class ErrorHandler implements ErrorHandlerInterface
{
	private string $logLevel = 'error';

	/**
	 * @var array<array{message: string, level: string, context: array, timestamp: int}>
	 */
	private array $logs = [];

	public function log(string $message, string $level, array $context): void
	{
		// Store in memory for getLogs()
		$this->logs[] = [
			'message'   => $message,
			'level'     => $level,
			'context'   => $context,
			'timestamp' => time(),
		];

		// Log to SMF if level matches or is higher priority
		if ($this->shouldLogToSmf($level)) {
			$smfLogType = $this->mapLevelToSmfType($level);
			SmfErrorHandler::log($message, $smfLogType);
		}
	}

	public function handle(Throwable $exception): void
	{
		$this->log(
			$exception->getMessage(),
			'error',
			[
				'file'  => $exception->getFile(),
				'line'  => $exception->getLine(),
				'trace' => $exception->getTraceAsString(),
			]
		);
	}

	public function setLevel(string $level): void
	{
		$this->logLevel = $level;
	}

	public function getLevel(): string
	{
		return $this->logLevel;
	}

	public function clear(): void
	{
		$this->logs = [];
	}

	public function getLogs(): array
	{
		return $this->logs;
	}

	public function fatal(string $message, bool $log = true): void
	{
		SmfErrorHandler::fatalLang($message, $log ? 'general' : false);
	}

	private function shouldLogToSmf(string $level): bool
	{
		$levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];

		$currentPriority = $levels[$this->logLevel] ?? 3;
		$messagePriority = $levels[$level] ?? 1;

		return $messagePriority >= $currentPriority;
	}

	private function mapLevelToSmfType(string $level): string
	{
		return match ($level) {
			'warning'           => 'user',
			'error', 'critical' => 'critical',
			default             => 'general',
		};
	}
}
