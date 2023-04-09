<?php declare(strict_types=1);

/**
 * IntlTrait.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Utils;

use MessageFormatter;
use IntlException;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;

if (! defined('SMF'))
	die('No direct access...');

trait IntlTrait
{
	/**
	 * Translates a message using the given pattern and values.
	 *
	 * @see https://github.com/dragomano/Light-Portal/wiki/Info-for-translators
	 * @see https://symfony.com/doc/6.1/translation/message_format.html
	 * @see https://intl.rmcreative.ru
	 *
	 * @param string $pattern The message pattern to translate.
	 * @param array  $values  The values to substitute in the message.
	 *
	 * @return string The translated message.
	 *
	 * @throws IntlException If the message pattern syntax is invalid.
	 */
	public function translate(string $pattern, array $values = []): string
	{
		if (!extension_loaded('intl')) {
			$this->logError('[LP] translate helper: you should enable the intl extension', 'critical');

			return '';
		}

		$message = $this->txt[$pattern] ?? $pattern;

		try {
			$formatter = new MessageFormatter($this->txt['lang_locale'] ?? 'en_US', $message);

			return $formatter->format($values);
		} catch (IntlException $e) {
			$this->logError("[LP] translate helper: {$e->getMessage()} in '\$txt[{$pattern}]'", 'critical');

			return '';
		}
	}

	/**
	 * Get the time in the format "Yesterday at ...", "Today at ...", "X minutes ago", etc.
	 *
	 * Получаем время в формате «Вчера в ...», «Сегодня в ...», «X минут назад» и т. д.
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
		if (empty($timeDifference))
			return $this->txt['lp_just_now'];

		// Future time?
		if ($timeDifference < 0) {
			// like "Tomorrow at ..."
			if ($d.$m.$y === date('jmY', strtotime('+1 day')))
				return $this->txt['lp_tomorrow'] . $t;

			// like "In n days"
			$days = floor(($timestamp - $now) / 60 / 60 / 24);
			if ($days > 1) {
				if ($days < 7)
					return sprintf($this->txt['lp_time_label_in'], $this->translate('lp_days_set', compact('days')));

				// Future date in current month
				if ($m === date('m', $now) && $y === date('Y', $now))
					return $this->getLocalDate($timestamp, 'full');
				// Future date in current year
				elseif ($y === date('Y', $now))
					return $this->getLocalDate($timestamp, 'medium');

				// Other future date
				return $this->getLocalDate($timestamp, timeType: 'none');
			}

			// like "In n hours"
			$hours = ($timestamp - $now) / 60 / 60;
			if ($hours >= 1)
				return sprintf($this->txt['lp_time_label_in'], $this->translate('lp_hours_set', ['hours' => ceil($hours)]));

			// like "In n minutes"
			$minutes = ($timestamp - $now) / 60;
			if ($minutes >= 1)
				return sprintf($this->txt['lp_time_label_in'], $this->translate('lp_minutes_set', ['minutes' => ceil($minutes)]));

			// like "In n seconds"
			return sprintf($this->txt['lp_time_label_in'], $this->translate('lp_seconds_set', ['seconds' => abs($timeDifference)]));
		}

		// Less than an hour
		$lastMinutes = round($timeDifference / 60);

		// like "n seconds ago"
		if ($timeDifference < 60)
			return $this->smcFunc['ucfirst']($this->translate('lp_seconds_set', ['seconds' => $timeDifference])) . $this->txt['lp_time_label_ago'];
		// like "n minutes ago"
		elseif ($lastMinutes < 60)
			return $this->smcFunc['ucfirst']($this->translate('lp_minutes_set', ['minutes' => (int) $lastMinutes])) . $this->txt['lp_time_label_ago'];
		// like "Today at ..."
		elseif ($d.$m.$y === date('jmY', $now))
			return $this->txt['today'] . $t;
		// like "Yesterday at ..."
		elseif ($d.$m.$y === date('jmY', strtotime('-1 day')))
			return $this->txt['yesterday'] . $t;
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
		//$dateTime->setTimezone(new DateTimeZone($this->user_settings['timezone'] ?? $this->modSettings['default_timezone']));

		return $dateTime;
	}

	public function getLocalDate(int $timestamp, string $dateType = 'long', string $timeType = 'short'): string
	{
		if (extension_loaded('intl')) {
			$formatter = new IntlDateFormatter($this->txt['lang_locale'], $this->getPredefinedConstant($dateType), $this->getPredefinedConstant($timeType));

			return $formatter->format($timestamp);
		}

		$this->logError('[LP] getLocalDate helper: enable intl extension', 'critical');

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
