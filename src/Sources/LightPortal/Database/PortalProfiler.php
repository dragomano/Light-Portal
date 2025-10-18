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

namespace LightPortal\Database;

use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\Profiler\Profiler;
use Laminas\Db\Adapter\StatementContainerInterface;
use Laminas\Db\Sql\Exception\InvalidArgumentException;

if (! defined('SMF'))
	die('No direct access...');

class PortalProfiler extends Profiler
{
	public function __construct(protected ?PlatformInterface $platform = null) {}

	public function profilerStart($target): self
	{
		$profileInformation = [
			'sql'        => '',
			'parameters' => null,
			'start'      => microtime(true),
			'end'        => null,
			'elapse'     => null,
			'backtrace'  => $this->captureBacktrace(),
		];

		if ($target instanceof StatementContainerInterface) {
			if ($this->platform) {
				$profileInformation['sql'] = $this->getRealSqlFromStatement($target);
			} else {
				$profileInformation['sql'] = $target->getSql();
			}

			$profileInformation['parameters'] = clone $target->getParameterContainer();
		} elseif (is_string($target)) {
			$profileInformation['sql'] = $target;
		} else {
			throw new InvalidArgumentException(
				__FUNCTION__ . ' takes either a StatementContainer or a string'
			);
		}

		$this->profiles[$this->currentIndex] = $profileInformation;

		return $this;
	}

	protected function captureBacktrace(): ?array
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

		$relevantFrame = null;
		foreach ($backtrace as $frame) {
			$file = $frame['file'] ?? '';
			$function = $frame['function'] ?? '';
			$class = $frame['class'] ?? '';
			$isIgnored = false;

			if (
				str_contains($function, 'profiler')
				|| str_contains($file ?? '', 'Profiler')
				|| str_contains($class, 'Laminas')
				|| str_starts_with($file, '/usr/')
				|| str_starts_with($file, 'phar://')
				|| $function === 'captureBacktrace'
			) {
				$isIgnored = true;
			}

			if (! $isIgnored && $file && $class) {
				$relevantFrame = $frame;
				break;
			}
		}

		if ($relevantFrame) {
			return [
				'file'     => $relevantFrame['file'] ?? 'unknown',
				'line'     => $relevantFrame['line'] ?? 0,
				'function' => $relevantFrame['function'] ?? 'unknown',
				'full_trace' => array_slice($backtrace, 0, 5),
			];
		}

		return null;
	}

	protected function getRealSqlFromStatement(StatementContainerInterface $statement): string
	{
		$sql    = $statement->getSql();
		$params = $statement->getParameterContainer()?->getNamedArray() ?? [];

		uksort($params, fn($a, $b) => strlen((string) $b) <=> strlen((string) $a));

		foreach ($params as $key => $value) {
			$placeholder = ':' . $key;
			if ($value === null) {
				$escapedValue = 'NULL';
			} else {
				$escapedValue = $this->platform->quoteTrustedValue($value);
			}
			$sql = str_replace($placeholder, $escapedValue, $sql);
		}

		$sql = preg_replace("/LIMIT\s+'?(\d+)'?/", 'LIMIT $1', $sql);
		$sql = preg_replace("/OFFSET\s+'?(\d+)'?/", 'OFFSET $1', $sql);
		$sql = preg_replace('/`([^`]+)`/', '$1', $sql);

		return trim($sql);
	}
}
