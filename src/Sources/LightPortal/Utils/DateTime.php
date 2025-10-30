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

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use DateTime as BaseDateTime;
use IntlDateFormatter;
use InvalidArgumentException;

if (! defined('SMF'))
	die('No direct access...');

final class DateTime
{
	private const SECONDS_IN_MINUTE = 60;

	private const SECONDS_IN_HOUR = 3600;

	private const SECONDS_IN_DAY = 86400;

	private const DAYS_IN_WEEK = 7;

	public static function relative(int $timestamp): string
	{
		$now = time();
		$timeDifference = $now - $timestamp;

		// Just now?
		if ($timeDifference === 0) {
			return Lang::$txt['lp_just_now'];
		}

		$dateTime = self::get($timestamp);
		$dateKey = $dateTime->format('jmY');

		// Future time?
		if ($timeDifference < 0) {
			return self::formatFutureTime($timestamp, $now, $dateTime, $dateKey, $timeDifference);
		}

		return self::formatPastTime($timestamp, $now, $dateTime, $dateKey, $timeDifference);
	}

	public static function get(int $timestamp = 0): BaseDateTime
	{
		$dateTime = new BaseDateTime();
		$dateTime->setTimestamp($timestamp ?: time());

		return $dateTime;
	}

	public static function getValueForDate(?BaseDateTime $dateTime = null): string
	{
		$dateTime ??= new BaseDateTime();

		return match($dateTime->format('m-d')) {
			'04-01' => "\x4C\x61\x7A\x79\x20\x50\x61\x6E\x64\x61",
			'07-09' => "\x46\x61\x6E\x63\x79\x20\x50\x6F\x72\x74\x61\x6C",
			default => "\x4C\x69\x67\x68\x74\x20\x50\x6F\x72\x74\x61\x6C",
		};
	}

	public static function dateCompare(string $date1, string $date2, string $operator = '<'): bool
	{
		$dateTime1 = self::parseDate($date1);
		$dateTime2 = self::parseDate($date2);

		if ($dateTime1 === null || $dateTime2 === null) {
			return false;
		}

		return match ($operator) {
			'<'        => $dateTime1 < $dateTime2,
			'<='       => $dateTime1 <= $dateTime2,
			'>'        => $dateTime1 > $dateTime2,
			'>='       => $dateTime1 >= $dateTime2,
			'==', '='  => $dateTime1 == $dateTime2,
			'!=', '<>' => $dateTime1 != $dateTime2,
			default    => throw new InvalidArgumentException("Unknown operator: $operator"),
		};
	}

	private static function formatFutureTime(
		int $timestamp,
		int $now,
		BaseDateTime $dateTime,
		string $dateKey,
		int $timeDifference
	): string
	{
		// Tomorrow at ...
		if ($dateKey === date('jmY', strtotime('+1 day'))) {
			return Lang::$txt['lp_tomorrow'] . $dateTime->format('H:i');
		}

		$secondsUntil = $timestamp - $now;
		$days = floor($secondsUntil / self::SECONDS_IN_DAY);

		// In n days (within a week)
		if ($days > 1 && $days < self::DAYS_IN_WEEK) {
			return sprintf(
				Lang::$txt['lp_time_label_in'],
				Lang::getTxt('lp_days_set', ['days' => $days])
			);
		}

		// Future date - more than a week
		if ($days >= self::DAYS_IN_WEEK) {
			$month = $dateTime->format('m');
			$year = $dateTime->format('Y');
			$currentMonth = date('m', $now);
			$currentYear = date('Y', $now);

			// Future date in current month
			if ($month === $currentMonth && $year === $currentYear) {
				return self::getLocalDate($timestamp, IntlDateFormatter::FULL);
			}

			// Future date in current year
			if ($year === $currentYear) {
				return self::getLocalDate($timestamp, IntlDateFormatter::MEDIUM);
			}

			// Other future date
			return self::getLocalDate($timestamp, timeType: IntlDateFormatter::NONE);
		}

		// In n hours
		$hours = $secondsUntil / self::SECONDS_IN_HOUR;
		if ($hours >= 1) {
			return sprintf(
				Lang::$txt['lp_time_label_in'],
				Lang::getTxt('lp_hours_set', ['hours' => ceil($hours)])
			);
		}

		// In n minutes
		$minutes = $secondsUntil / self::SECONDS_IN_MINUTE;
		if ($minutes >= 1) {
			return sprintf(
				Lang::$txt['lp_time_label_in'],
				Lang::getTxt('lp_minutes_set', ['minutes' => ceil($minutes)])
			);
		}

		// In n seconds
		return sprintf(
			Lang::$txt['lp_time_label_in'],
			Lang::getTxt('lp_seconds_set', ['seconds' => abs($timeDifference)])
		);
	}

	private static function formatPastTime(
		int $timestamp,
		int $now,
		BaseDateTime $dateTime,
		string $dateKey,
		int $timeDifference
	): string
	{
		$minutes = $timeDifference / self::SECONDS_IN_MINUTE;

		// n seconds ago
		if ($timeDifference < self::SECONDS_IN_MINUTE) {
			$secondsText = Lang::getTxt('lp_seconds_set', ['seconds' => $timeDifference]);
			return Utils::$smcFunc['ucfirst']($secondsText) . Lang::$txt['lp_time_label_ago'];
		}

		// n minutes ago
		if ($minutes < 60) {
			$minutesText = Lang::getTxt('lp_minutes_set', ['minutes' => (int) $minutes]);
			return Utils::$smcFunc['ucfirst']($minutesText) . Lang::$txt['lp_time_label_ago'];
		}

		$time = $dateTime->format('H:i');
		$todayKey = date('jmY', $now);
		$yesterdayKey = date('jmY', strtotime('-1 day'));

		// Today at ...
		if ($dateKey === $todayKey) {
			return Lang::$txt['today'] . $time;
		}

		// Yesterday at ...
		if ($dateKey === $yesterdayKey) {
			return Lang::$txt['yesterday'] . $time;
		}

		$month = $dateTime->format('m');
		$year = $dateTime->format('Y');
		$currentMonth = date('m', $now);
		$currentYear = date('Y', $now);

		// Date in current month
		if ($month === $currentMonth && $year === $currentYear) {
			return self::getLocalDate($timestamp);
		}

		// Date in current year
		if ($year === $currentYear) {
			return self::getLocalDate($timestamp);
		}

		// Past year date
		return self::getLocalDate($timestamp, timeType: IntlDateFormatter::NONE);
	}

	private static function parseDate(string $dateStr): ?BaseDateTime
	{
		$dateTime = BaseDateTime::createFromFormat('d.m.y', $dateStr);

		if ($dateTime === false) {
			return null;
		}

		$errors = BaseDateTime::getLastErrors();
		if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
			return null;
		}

		if ($dateTime->format('d.m.y') !== $dateStr) {
			return null;
		}

		return $dateTime;
	}

	private static function getLocalDate(
		int $timestamp,
		int $dateType = IntlDateFormatter::LONG,
		int $timeType = IntlDateFormatter::SHORT
	): string
	{
		if (! extension_loaded('intl')) {
			ErrorHandler::log('[LP] getLocalDate helper: enable intl extension', 'critical');
			return '';
		}

		$formatter = new IntlDateFormatter(Lang::$txt['lang_locale'], $dateType, $timeType);

		return $formatter->format($timestamp);
	}
}
