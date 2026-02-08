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

namespace LightPortal\Utils;

use Psr\Log\LoggerInterface;
use Stringable;

if (! defined('SMF'))
	die('No direct access...');

class PsrLoggerAdapter implements LoggerInterface
{
	private array $levelMapping = [
		'emergency' => 'critical',
		'alert'     => 'critical',
		'critical'  => 'critical',
		'error'     => 'general',
		'warning'   => 'general',
		'notice'    => 'general',
		'info'      => 'general',
		'debug'     => 'debug',
	];

	public function __construct(private readonly ErrorHandlerInterface $errorHandler) {}

	public function emergency(string|Stringable $message, array $context = []): void
	{
		$this->log('emergency', $message, $context);
	}

	public function alert(string|Stringable $message, array $context = []): void
	{
		$this->log('alert', $message, $context);
	}

	public function critical(string|Stringable $message, array $context = []): void
	{
		$this->log('critical', $message, $context);
	}

	public function error(string|Stringable $message, array $context = []): void
	{
		$this->log('error', $message, $context);
	}

	public function warning(string|Stringable $message, array $context = []): void
	{
		$this->log('warning', $message, $context);
	}

	public function notice(string|Stringable $message, array $context = []): void
	{
		$this->log('notice', $message, $context);
	}

	public function info(string|Stringable $message, array $context = []): void
	{
		$this->log('info', $message, $context);
	}

	public function debug(string|Stringable $message, array $context = []): void
	{
		$this->log('debug', $message, $context);
	}

	public function log($level, string|Stringable $message, array $context = []): void
	{
		$this->errorHandler->log((string) $message, $this->mapLevel($level), $context);
	}

	private function mapLevel(string $level): string
	{
		return $this->levelMapping[$level] ?? 'general';
	}
}
