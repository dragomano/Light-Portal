<?php

namespace Bugo\LightPortal;

/**
 * Helpers.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Helpers
{
	/**
	 * Get language of the current user
	 *
	 * Получаем язык текущего пользователя
	 *
	 * @return string
	 */
	public static function getUserLanguage()
	{
		global $user_info, $language;

		return $user_info['language'] ?: $language;
	}

	/**
	 * Get the maximum possible length of the message, in accordance with the settings of the forum
	 *
	 * Получаем максимально возможную длину сообщения, в соответствии с настройками форума
	 *
	 * @return int
	 */
	public static function getMaxMessageLength()
	{
		global $modSettings;

		return !empty($modSettings['max_messageLength']) && $modSettings['max_messageLength'] > 65534 ? (int) $modSettings['max_messageLength'] : 65534;
	}

	/**
	 * Remove BBCode from transmitted data
	 *
	 * Убираем ББ-код из переданных данных
	 *
	 * @param array|string $data
	 * @return array|string
	 */
	public static function cleanBbcode($data)
	{
		if (is_array($data))
			return array_map('self::cleanBbcode', $data);

		return preg_replace('~\[[^]]+]~', '', $data);
	}

	/**	 * Get a title for preview block
	 *
	 * Получаем заголовок блока превью
	 *
	 * @param string $prefix
	 * @return string
	 */
	public static function getPreviewTitle($prefix = null)
	{
		global $context, $txt;

		return self::getFloatSpan((!empty($prefix) ? $prefix . ' ' : '') . $context['preview_title'], $context['right_to_left'] ? 'right' : 'left') . self::getFloatSpan($txt['preview'], $context['right_to_left'] ? 'left' : 'right');
	}

	/**
	 * Get text within span that is floating by defined direction
	 *
	 * Получаем текст внутри тега span, с float = $direction (left|right)
	 *
	 * @param string $text
	 * @param string $direction
	 * @return string
	 */
	private static function getFloatSpan($text, $direction = 'left')
	{
		return '<span class="float' . $direction . '">' . $text . '</span>';
	}

	/**
	 * The correct declination of words
	 *
	 * Правильное склонение слов
	 *
	 * https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
	 * http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
	 *
	 * @param int $num
	 * @param array|string $str массив или строка с формами склонения (если в языке только одна форма склонения, см. rule #0)
	 * @return string
	 */
	public static function getCorrectDeclension(int $num, $str)
	{
		global $txt;

		// Plural rule #0 (Chinese, Japanese, Persian, Turkish, Thai, Indonesian, Malay)
		$rule_zero = array('zh', 'ja', 'fa', 'tr', 'th', 'id', 'ms');
		if (in_array($txt['lang_dictionary'], $rule_zero))
			return $num . ' ' . (is_string($str) ? $str : $str[0]);

		// Plural rule #2 (French, Portuguese_brazilian)
		$rule_two = array('fr', 'pt');
		if (in_array($txt['lang_dictionary'], $rule_two))
			return $num . ' ' . $str[($num == 0 || $num == 1) ? 0 : 1];

		// Just in case
		if (!isset($str[2]))
			$str[2] = $str[1];

		// Plural rule #5 (Romanian)
		if ($txt['lang_dictionary'] == 'ro') {
			$cases = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19');
			return $num . ' ' . $str[$num == 1 ? 0 : ((empty($num) || in_array(substr((string) $num, -2, 2), $cases)) ? 1 : 2)];
		}

		// Plural rule #6 (Lithuanian)
		if ($txt['lang_dictionary'] == 'lt') {
			$cases = array('11', '12', '13', '14', '15', '16', '17', '18', '19');
			return $num . ' ' . $str[($num % 10 === 1 && substr((string) $num, -2, 2) != '11') ? 0 : (($num % 10 === 0 || in_array(substr((string) $num, -2, 2), $cases)) ? 1 : 2)];
		}

		// Plural rule #7 (Croatian, Serbian, Russian, Ukrainian)
		$rule_seven = array('hr', 'sr', 'ru', 'uk');
		if (in_array($txt['lang_dictionary'], $rule_seven)) {
			$cases = array(2, 0, 1, 1, 1, 2);
			return $num . ' ' . $str[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
		}

		// Plural rule #8 (Czech, Slovak)
		$rule_eight = array('cs', 'sk');
		if (in_array($txt['lang_dictionary'], $rule_eight))
			return $num . ' ' . $str[$num == 1 ? 0 : (in_array($num, array(2, 3, 4)) ? 1 : 2)];

		// Plural rule #9 (Polish)
		if ($txt['lang_dictionary'] == 'pl') {
			$cases = array('12', '13', '14');
			return $num . ' ' . $str[$num == 1 ? 0 : ((in_array(substr((string) $num, -1, 1), array(2, 3, 4)) || in_array(substr((string) $num, -2, 2), $cases)) ? 1 : 2)];
		}

		// Plural rule #15 (Macedonian)
		if ($txt['lang_dictionary'] == 'mk')
			return $num . ' ' . $str[($num % 10 === 1 && substr((string) $num, -2, 2) != '11') ? 0 : 1];

		// Urdu
		if ($txt['lang_dictionary'] == 'ur')
			return $str[$num == 1 ? 0 : 1] . ' ' . $num;

		// Arabic
		if ($txt['lang_dictionary'] == 'ar')
			return $str[in_array($num, array(0, 1, 2)) ? $num : ($num % 100 >= 3 && $num % 100 <= 10 ? 3 : ($num % 100 >= 11 ? 4 : 5))] . ' ' . $num;

		// Plural rule #1 (Danish, Dutch, English, German, Norwegian, Swedish, Finnish, Hungarian, Greek, Hebrew, Italian, Portuguese_pt, Spanish, Catalan, Vietnamese, Esperanto, Galician, Albanian, Bulgarian)
		return $num . ' ' . $str[$num == 1 ? 0 : 1];
	}

	/**
	 * Get the time in the format "Yesterday", "Today", "X minutes ago", etc.
	 *
	 * Получаем время в формате «Вчера», «Сегодня», «X минут назад» и т. д.
	 *
	 * @param integer $a — Unix time
	 * @return string
	 */
	public static function getFriendlyTime(int $a)
	{
		global $txt, $smcFunc;

		$time = time();
		$tm   = date('H:i', $a);
		$d    = date('d', $a);
		$m    = date('m', $a);
		$y    = date('Y', $a);
		$sec  = $time - $a;
		$last = round(($sec) / 60);

		// Future time?
		// Будущее время?
		if ($a > $time) {
			$days = ($a - $time) / 60 / 60 / 24;

			if ($days > 1)
				return sprintf($txt['lp_remained'], self::getCorrectDeclension((int) floor($days), $txt['lp_days_set']));

			$minutes = ($a - $time) / 60 / 60;

			if ($minutes > 1)
				return sprintf($txt['lp_remained'], self::getCorrectDeclension($minutes, $txt['lp_minutes_set']));
			else
				return sprintf($txt['lp_remained'], self::getCorrectDeclension($minutes * 60, $txt['lp_seconds_set']));
		}

		if ($last == 0)
			return self::getCorrectDeclension($sec, $txt['lp_seconds_set']) . $txt['lp_time_label_ago'];
		elseif ($last == 1)
			return $smcFunc['ucfirst']($txt['lp_minutes_set'][0]) . $txt['lp_time_label_ago'];
		elseif ($last < 55)
			return self::getCorrectDeclension((int) $last, $txt['lp_minutes_set']) . $txt['lp_time_label_ago'];
		elseif ($d.$m.$y == date('dmY', $time))
			return $txt['today'] . $tm;
		elseif ($d.$m.$y == date('dmY', strtotime('-1 day')))
			return $txt['yesterday'] . $tm;
		elseif ($y == date('Y', $time))
			return $d . ' ' . $txt['months'][date('n', $a)] . ', ' . $tm;
		elseif ($tm == '00:00')
			return $d . ' ' . $txt['months'][date('n', $a)] . ' ' . $y;
		else
			return timeformat($a);
	}

	/**
	 * Using cache
	 *
	 * Используем кэш
	 *
	 * @param string $data
	 * @param string $getData
	 * @param string $class
	 * @param int $time (in seconds)
	 * @param array $vars
	 * @return mixed
	 */
	public static function useCache($data, $getData, $class = 'self', $time = 3600, $vars = [])
	{
		if (($$data = cache_get_data('light_portal_' . $data, $time)) == null) {
			$$data = null;

			if (method_exists($class, $getData)) {
				if ($class == 'self')
					$$data = self::$getData($vars);
				else
					$$data = $class::$getData($vars);
			} elseif (function_exists($getData)) {
				$$data = $getData($vars);}

			cache_put_data('light_portal_' . $data, $$data, $time);
		}

		return $$data;
	}

	/**
	 * Form a list of addons that not installed
	 *
	 * Формируем список неустановленных плагинов
	 *
	 * @param string $type
	 * @return void
	 */
	public static function findMissingBlockTypes($type)
	{
		global $txt, $context;

		if (empty($txt['lp_block_types'][$type]))
			$context['lp_missing_block_types'][$type] = sprintf($txt['lp_addon_not_installed'], str_replace('_', '', ucwords($type, '_')));
	}
}
