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
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use DateTime as BaseDateTime;
use Exception;
use IntlDateFormatter;

use InvalidArgumentException;

use function ceil;
use function compact;
use function count;
use function date;
use function explode;
use function extension_loaded;
use function floor;
use function round;
use function sprintf;
use function strtotime;
use function time;

if (! defined('SMF'))
	die('No direct access...');

final class DateTime
{
	public static function relative(int $timestamp): string
	{
		$now = time();

		$dateTime = self::get($timestamp);

		$t = $dateTime->format('H:i');
		$d = $dateTime->format('j');
		$m = $dateTime->format('m');
		$y = $dateTime->format('Y');

		$timeDifference = $now - $timestamp;

		// Just now?
		if (empty($timeDifference)) {
			return Lang::$txt['lp_just_now'];
		}

		// Future time?
		if ($timeDifference < 0) {
			// like "Tomorrow at ..."
			if ($d.$m.$y === date('jmY', strtotime('+1 day'))) {
				return Lang::$txt['lp_tomorrow'] . $t;
			}

			// like "In n days"
			$days = floor(($timestamp - $now) / 60 / 60 / 24);
			if ($days > 1) {
				if ($days < 7) {
					return sprintf(Lang::$txt['lp_time_label_in'], Lang::getTxt(
						'lp_days_set', compact('days')
					));
				}

				if ($m === date('m', $now) && $y === date('Y', $now)) {
					// Future date in current month
					return self::getLocalDate($timestamp, 'full');
				} elseif ($y === date('Y', $now)) {
					// Future date in current year
					return self::getLocalDate($timestamp, 'medium');
				}

				// Other future date
				return self::getLocalDate($timestamp, timeType: 'none');
			}

			// like "In n hours"
			$hours = ($timestamp - $now) / 60 / 60;
			if ($hours >= 1) {
				return sprintf(Lang::$txt['lp_time_label_in'], Lang::getTxt(
					'lp_hours_set', ['hours' => ceil($hours)]
				));
			}

			// like "In n minutes"
			$minutes = ($timestamp - $now) / 60;
			if ($minutes >= 1) {
				return sprintf(Lang::$txt['lp_time_label_in'], Lang::getTxt(
					'lp_minutes_set', ['minutes' => ceil($minutes)]
				));
			}

			// like "In n seconds"
			return sprintf(Lang::$txt['lp_time_label_in'], Lang::getTxt(
				'lp_seconds_set', ['seconds' => abs($timeDifference)]
			));
		}

		// Less than an hour
		$lastMinutes = round($timeDifference / 60);

		// like "n seconds ago"
		if ($timeDifference < 60)
			return Utils::$smcFunc['ucfirst'](Lang::getTxt(
					'lp_seconds_set', ['seconds' => $timeDifference]
				)) . Lang::$txt['lp_time_label_ago'];
		// like "n minutes ago"
		elseif ($lastMinutes < 60)
			return Utils::$smcFunc['ucfirst'](Lang::getTxt(
					'lp_minutes_set', ['minutes' => (int) $lastMinutes]
				)) . Lang::$txt['lp_time_label_ago'];
		// like "Today at ..."
		elseif ($d.$m.$y === date('jmY', $now))
			return Lang::$txt['today'] . $t;
		// like "Yesterday at ..."
		elseif ($d.$m.$y === date('jmY', strtotime('-1 day')))
			return Lang::$txt['yesterday'] . $t;
		// like "Thursday, 20 February, H:m" (current month)
		elseif ($m === date('m', $now) && $y === date('Y', $now))
			return self::getLocalDate($timestamp);
		// like "20 February, H:m" (current year)
		elseif ($y === date('Y', $now))
			return self::getLocalDate($timestamp);

		// like "20 February 2019" (past year)
		return self::getLocalDate($timestamp, timeType: 'none');
	}

	public static function get(int $timestamp = 0): BaseDateTime
	{
		$dateTime = new BaseDateTime();
		$dateTime->setTimestamp($timestamp ?: time());

		return $dateTime;
	}

	public static function getValueForDate(): string
	{
		$dateTime = new BaseDateTime();

		return match($dateTime->format('m-d')) {
			'04-01' => "\x4C\x61\x7A\x79\x20\x50\x61\x6E\x64\x61",
			'07-09' => "\x46\x61\x6E\x63\x79\x20\x50\x6F\x72\x74\x61\x6C",
			default => "\x4C\x69\x67\x68\x74\x20\x50\x6F\x72\x74\x61\x6C",
		};
	}

	public static function dateCompare(string $date1, string $date2, string $operator = '<'): bool {
		$parseDate = function ($dateStr) {
			$parts = explode('.', $dateStr);

			if (count($parts) !== 3)
				return null;

			try {
				return new BaseDateTime("20$parts[2]-$parts[1]-$parts[0]");
			} catch (Exception) {
				return null;
			}
		};

		$dateTime1 = $parseDate($date1);
		$dateTime2 = $parseDate($date2);

		if (! $dateTime1 || ! $dateTime2) {
			return false;
		}

		return match ($operator) {
			'<'        => $dateTime1 < $dateTime2,
			'<='       => $dateTime1 <= $dateTime2,
			'>'        => $dateTime1 > $dateTime2,
			'>='       => $dateTime1 >= $dateTime2,
			'==', '='  => $dateTime1 == $dateTime2,
			'!=', '<>' => $dateTime1 != $dateTime2,
			default    => throw new InvalidArgumentException("Unknown operator: $operator")
		};
	}

	private static function getLocalDate(
		int $timestamp,
		string $dateType = 'long',
		string $timeType = 'short'
	): string
	{
		if (extension_loaded('intl')) {
			$formatter = new IntlDateFormatter(
				Lang::$txt['lang_locale'],
				self::getPredefinedConstant($dateType),
				self::getPredefinedConstant($timeType)
			);

			return $formatter->format($timestamp);
		}

		ErrorHandler::log('[LP] getLocalDate helper: enable intl extension', 'critical');

		return '';
	}

	/**
	 * @see https://www.php.net/manual/en/class.intldateformatter.php
	 */
	private static function getPredefinedConstant(string $type): int
	{
		return match ($type) {
			'full'   => IntlDateFormatter::FULL,
			'long'   => IntlDateFormatter::LONG,
			'medium' => IntlDateFormatter::MEDIUM,
			default  => IntlDateFormatter::NONE,
		};
	}
}
