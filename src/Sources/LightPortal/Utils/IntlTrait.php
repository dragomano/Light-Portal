<?php declare(strict_types=1);

/**
 * IntlTrait.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use DateTime;
use IntlException;
use IntlDateFormatter;

if (! defined('SMF'))
	die('No direct access...');

trait IntlTrait
{
	/**
	 * Get the time in the format "Yesterday at ...", "Today at ...", "X minutes ago", etc.
	 *
	 * Получаем время в формате «Вчера в ...», «Сегодня в ...», «X минут назад» и т. д.
	 * @throws IntlException
	 */
	public function getFriendlyTime(int $timestamp): string
	{
		$now = time();

		$dateTime = $this->getDateTime($timestamp);

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
					return $this->getLocalDate($timestamp, 'full');
				} elseif ($y === date('Y', $now)) {
					// Future date in current year
					return $this->getLocalDate($timestamp, 'medium');
				}

				// Other future date
				return $this->getLocalDate($timestamp, timeType: 'none');
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
		// like "Tuesday, 20 February, H:m" (current month)
		elseif ($m === date('m', $now) && $y === date('Y', $now))
			return $this->getLocalDate($timestamp);
		// like "20 February, H:m" (current year)
		elseif ($y === date('Y', $now))
			return $this->getLocalDate($timestamp);

		// like "20 February 2019" (past year)
		return $this->getLocalDate($timestamp, 'long', 'none');
	}

	public function getDateTime(int $timestamp = 0): DateTime
	{
		$dateTime = new DateTime;
		$dateTime->setTimestamp($timestamp ?: time());

		return $dateTime;
	}

	public function getLocalDate(int $timestamp, string $dateType = 'long', string $timeType = 'short'): string
	{
		if (extension_loaded('intl')) {
			$formatter = new IntlDateFormatter(
				Lang::$txt['lang_locale'],
				$this->getPredefinedConstant($dateType),
				$this->getPredefinedConstant($timeType)
			);

			return $formatter->format($timestamp);
		}

		ErrorHandler::log('[LP] getLocalDate helper: enable intl extension', 'critical');

		return '';
	}

	/**
	 * @see https://www.php.net/manual/en/class.intldateformatter.php
	 */
	public function getPredefinedConstant(string $type): int
	{
		return match ($type) {
			'full'   => IntlDateFormatter::FULL,
			'long'   => IntlDateFormatter::LONG,
			'medium' => IntlDateFormatter::MEDIUM,
			default  => IntlDateFormatter::NONE,
		};
	}
}
