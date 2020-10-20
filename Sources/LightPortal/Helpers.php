<?php

namespace Bugo\LightPortal;

use Bugo\LightPortal\Utils\Cache;
use Bugo\LightPortal\Utils\Post;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\Server;
use Bugo\LightPortal\Utils\Session;

/**
 * Helpers.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Helpers
{
	/**
	 * Get the cache data or Cache class object
	 *
	 * Получаем данные из кэша или объект класса Cache
	 *
	 * @param string $key
	 * @param string|null $funcName
	 * @param string $class
	 * @param int $time (in seconds)
	 * @param mixed $vars
	 * @return mixed
	 */
	public static function cache(string $key = null, string $funcName = null, string $class = 'self', int $time = 3600, ...$vars)
	{
		return $key ? (new Cache)($key, $funcName, $class, $time, ...$vars) : new Cache;
	}

	/**
	 * Get $_POST object
	 *
	 * Получаем объект $_POST
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public static function post($key = null, $default = null)
	{
		return $key ? (new Post)($key, $default) : new Post;
	}

	/**
	 * Get $_REQUEST object
	 *
	 * Получаем объект $_REQUEST
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public static function request($key = null, $default = null)
	{
		return $key ? (new Request)($key, $default) : new Request;
	}

	/**
	 * Get $_SERVER object
	 *
	 * Получаем объект $_SERVER
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public static function server($key = null, $default = null)
	{
		return $key ? (new Server)($key, $default) : new Server;
	}

	/**
	 * Get $_SESSION object
	 *
	 * Получаем объект $_SESSION
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public static function session($key = null, $default = null)
	{
		return $key ? (new Session)($key, $default) : new Session;
	}

	/**
	 * Remove BBCode from transmitted data
	 *
	 * Убираем ББ-код из переданных данных
	 *
	 * @param array|string $data
	 * @return void
	 */
	public static function cleanBbcode(&$data)
	{
		$data = preg_replace('~\[[^]]+]~', '', $data);
	}

	/**
	 * Get the block icon
	 *
	 * Получаем иконку блока
	 *
	 * @param null|string $icon
	 * @param null|string $type
	 * @return string
	 */
	public static function getIcon($icon = null, $type = null)
	{
		global $context;

		$icon = $icon ?? ($context['lp_block']['icon'] ?? '');
		$type = $type ?? ($context['lp_block']['icon_type'] ?? 'fas');

		if (!empty($icon))
			return '<i class="' . $type . ' fa-' . $icon . '"></i> ';

		return '';
	}

	/**
	 * Checking whether the current theme contains a set of FontAwesome icons
	 *
	 * Проверяем, содержит ли текущая тема набор иконок FontAwesome
	 *
	 * @return bool
	 */
	public static function doesThisThemeUseFontAwesome()
	{
		global $settings;

		$supported_themes = [
			'Badem',
			'Endless',
			'Lunarfall',
			'Wide'
		];

		// Add ability to manually change the list of themes that support FontAwesome | Возможность вручную изменить список тем, поддерживающих FontAwesome
		Subs::runAddons('fontAwesomeThemes', array(&$supported_themes));

		return in_array(explode('_', $settings['name'])[0], $supported_themes);
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

		return self::getFloatSpan((!empty($prefix) ? $prefix . ' ' : '') . $context['preview_title'], $context['right_to_left'] ? 'right' : 'left') . self::getFloatSpan($txt['preview'], $context['right_to_left'] ? 'left' : 'right') . '<br>';
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
	private static function getFloatSpan(string $text, string $direction = 'left')
	{
		return '<span class="float' . $direction . '">' . $text . '</span>';
	}

	/**
	 * Get the word in the correct declension, depending on the number $num and the array|string $str with declension forms
	 *
	 * Получаем слово в правильном склонении, в зависимости от числа $num и массива|строки $str с формами склонения
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
	 * @see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
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
	 * @param int $timestamp — Unix time
	 * @param bool $use_user_offset
	 * @return string
	 */
	public static function getFriendlyTime(int $timestamp, bool $use_user_offset = false)
	{
		global $modSettings, $user_info, $txt, $smcFunc;

		$current_time = time();

		$tm = date('H:i', $timestamp);
		$d  = date('j', $timestamp);
		$m  = date('m', $timestamp);
		$y  = date('Y', $timestamp);

		// Use forum and user offsets
		if ($use_user_offset)
			$timestamp = $timestamp - ($modSettings['time_offset'] + $user_info['time_offset']) * 3600;

		// Difference between current time and $timestamp
		$time_difference = $current_time - $timestamp;

		// Just now?
		if (empty($time_difference))
			return $txt['lp_just_now'];

		// Future time?
		if ($time_difference < 0) {
			// like "Tomorrow at ..."
			if ($d.$m.$y == date('jmY', strtotime('+1 day')))
				return $txt['lp_tomorrow'] . $tm;

			$days = floor(($timestamp - $current_time) / 60 / 60 / 24);
			// like "In n days"
			if ($days > 1) {
				if ($days < 7)
					return sprintf($txt['lp_time_label_in'], self::getCorrectDeclension($days, $txt['lp_days_set']));

				// Future date in current month
				if ($m == date('m', $current_time) && $y == date('Y', $current_time))
					return $txt['days'][date('w', $timestamp)] . ', ' . self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);
				// Future date in current year
				elseif ($y == date('Y', $current_time))
					return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);

				// Other future date
				return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $y);
			}

			$hours = ($timestamp - $current_time) / 60 / 60;
			// like "In an hour"
			if ($hours == 1)
				return sprintf($txt['lp_time_label_in'], $txt['lp_hours_set'][0]);

			// like "In n hours"
			if ($hours > 1)
				return sprintf($txt['lp_time_label_in'], self::getCorrectDeclension($hours, $txt['lp_hours_set']));

			$minutes = ($timestamp - $current_time) / 60;
			// like "In a minute"
			if ($minutes == 1)
				return sprintf($txt['lp_time_label_in'], $txt['lp_minutes_set'][0]);

			// like "In n minutes"
			if ($minutes > 1)
				return sprintf($txt['lp_time_label_in'], self::getCorrectDeclension(ceil($minutes), $txt['lp_minutes_set']));

			// like "In n seconds"
			return sprintf($txt['lp_time_label_in'], self::getCorrectDeclension(abs($time_difference), $txt['lp_seconds_set']));
		}

		// Less than an hour
		$last_minutes = round($time_difference / 60);

		// like "n seconds ago"
		if ($time_difference < 60)
			return self::getCorrectDeclension($time_difference, $txt['lp_seconds_set']) . $txt['lp_time_label_ago'];
		// like "A minute ago"
		elseif ($last_minutes == 1)
			return $smcFunc['ucfirst']($txt['lp_minutes_set'][0]) . $txt['lp_time_label_ago'];
		// like "n minutes ago"
		elseif ($last_minutes < 60)
			return self::getCorrectDeclension((int) $last_minutes, $txt['lp_minutes_set']) . $txt['lp_time_label_ago'];
		// like "Today at ..."
		elseif ($d.$m.$y == date('jmY', $current_time))
			return $txt['today'] . $tm;
		// like "Yesterday at ..."
		elseif ($d.$m.$y == date('jmY', strtotime('-1 day')))
			return $txt['yesterday'] . $tm;
		// like "Tuesday, 20 February, H:m" (current month)
		elseif ($m == date('m', $current_time) && $y == date('Y', $current_time))
			return $txt['days'][date('w', $timestamp)] . ', ' . self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);
		// like "20 February, H:m" (current year)
		elseif ($y == date('Y', $current_time))
			return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);

		// like "20 February, 2019" (last year)
		return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $y);
	}

	/**
	 * Get a string with the day and month in European or American format
	 *
	 * Получаем запись дня и месяца в европейском или американском формате
	 *
	 * @param int $day
	 * @param string $month
	 * @param string $postfix
	 * @return string
	 */
	public static function getDateFormat(int $day, string $month, string $postfix)
	{
		global $txt;

		if ($txt['lang_locale'] == 'en_US')
			return $month . ' ' . $day . ', ' . $postfix;

		return $day . ' ' . $month . (strpos($postfix, ":") === false ? ' ' : ', ') . $postfix;
	}

	/**
	 * Form a list of addons that not installed
	 *
	 * Формируем список неустановленных плагинов
	 *
	 * @param string $type
	 * @return void
	 */
	public static function findMissingBlockTypes(string $type)
	{
		global $txt, $context;

		if (empty($txt['lp_block_types'][$type]))
			$context['lp_missing_block_types'][$type] = '<span class="error">' . sprintf($txt['lp_addon_not_installed'], str_replace('_', '', ucwords($type, '_'))) . '</span>';
	}

	/**
	 * Check whether the current user can view the portal item according to their access rights
	 *
	 * Проверяем, может ли текущий пользователь просматривать элемент портала, согласно его правам доступа
	 *
	 * @param int $permissions
	 * @return bool
	 */
	public static function canViewItem(int $permissions)
	{
		global $user_info;

		switch ($permissions) {
			case 0:
				return $user_info['is_admin'] == 1;
			case 1:
				return $user_info['is_guest'] == 1;
			case 2:
				return !empty($user_info['id']);
			default:
				return true;
		}
	}

	/**
	 * Returns a valid set of access rights for the current user
	 *
	 * Возвращает допустимый набор прав доступа текущего пользователя
	 *
	 * @return array
	 */
	public static function getPermissions()
	{
		global $user_info;

		if ($user_info['is_admin'] == 1)
			return [0, 1, 2, 3];
		elseif ($user_info['is_guest'] == 1)
			return [1, 3];
		elseif (!empty($user_info['id']))
			return [2, 3];

		return [3];
	}

	/**
	 * Check if the page with alias = $alias set as the portal frontpage
	 *
	 * Проверяет, установлена ли страница с alias = $alias как главная
	 *
	 * @param string $alias
	 * @return bool
	 */
	public static function isFrontpage(string $alias)
	{
		global $modSettings;

		if (empty($alias))
			return false;

		return !empty($modSettings['lp_frontpage_mode'])
			&& $modSettings['lp_frontpage_mode'] == 1
			&& !empty($modSettings['lp_frontpage_alias'])
			&& $modSettings['lp_frontpage_alias'] == $alias;
	}

	/**
	 * Get a public object title, according to the user's language, or the forum's language, or in English
	 *
	 * Получаем публичный заголовок объекта, в соответствии с языком пользователя или форума, или на английском
	 *
	 * @param array $object
	 * @return string
	 */
	public static function getPublicTitle(array $object)
	{
		global $user_info, $language;

		if (empty($object) || !isset($object['title']))
			return '';

		$lang1 = $object['title'][$user_info['language']] ?? null;
		$lang2 = $object['title'][$language] ?? null;
		$lang3 = $object['title']['english'] ?? null;

		return $lang1 ?: $lang2 ?: $lang3 ?: '';
	}

	/**
	 * Getting a string converted to snake_case
	 *
	 * Получаем строку, преобразованную в snake_case
	 *
	 * @param string $str
	 * @param string $glue
	 * @return string
	 */
	public static function getSnakeName(string $str, string $glue = '_')
	{
		$counter  = 0;
		$uc_chars = '';
		$new_str  = [];
		$str_len  = strlen($str);

		for ($x = 0; $x < $str_len; ++$x) {
			$ascii_val = ord($str[$x]);

			if ($ascii_val >= 65 && $ascii_val <= 90)
				$uc_chars .= $str[$x];
		}

		$tok = strtok($str, $uc_chars);

		while ($tok !== false) {
			$new_char  = chr(ord($uc_chars[$counter]) + 32);
			$new_str[] = $new_char . $tok;
			$tok       = strtok($uc_chars);

			++$counter;
		}

		return implode($glue, $new_str);
	}

	/**
	 * Get the article teaser
	 *
	 * Получаем тизер статьи
	 *
	 * @param string $text
	 * @return string
	 */
	public static function getTeaser($text)
	{
		global $modSettings;

		return !empty($modSettings['lp_teaser_size']) ? shorten_subject(trim($text), $modSettings['lp_teaser_size']) : trim($text);
	}

	/**
	 * Collecting the names of existing themes
	 *
	 * Собираем названия существующих тем оформления
	 *
	 * @return array
	 */
	public static function getForumThemes()
	{
		global $smcFunc, $context;

		$result = $smcFunc['db_query']('', '
			SELECT id_theme, variable, value
			FROM {db_prefix}themes
			WHERE variable = {string:name}',
			array(
				'name' => 'name'
			)
		);

		$current_themes = [];
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$current_themes[$row['id_theme']] = $row['value'];

		$smcFunc['db_free_result']($result);
		$context['lp_num_queries']++;

		return $current_themes;
	}

	/**
	 * Prepare content to display
	 *
	 * Готовим контент к отображению в браузере
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public static function prepareContent(string &$content, string $type = 'bbc', int $block_id = 0, int $cache_time = 0)
	{
		global $context;

		!empty($block_id) && !empty($context['lp_active_blocks'][$block_id])
			? $parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? []
			: $parameters = $context['lp_block']['options']['parameters'] ?? [];

		Subs::runAddons('prepareContent', array(&$content, $type, $block_id, $cache_time, $parameters));
	}

	/**
	 * Parse content depending on the type
	 *
	 * Парсим контент в зависимости от типа
	 *
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public static function parseContent(string &$content, string $type = 'bbc')
	{
		global $context;

		switch ($type) {
			case 'bbc':
				$content = parse_bbc($content);

				// Integrate with the Paragrapher mod
				call_integration_hook('integrate_paragrapher_string', array(&$content));

				break;
			case 'html':
				$content = un_htmlspecialchars($content);

				break;
			case 'php':
				$content = trim(un_htmlspecialchars($content));
				$content = trim($content, '<?php');
				$content = trim($content, '?>');

				ob_start();

				try {
					$content = html_entity_decode($content, ENT_COMPAT, $context['character_set'] ?? 'UTF-8');
					eval($content);
				} catch (\ParseError $p) {
					echo $p->getMessage();
				}

				$content = ob_get_clean();

				break;
			default:
				Subs::runAddons('parseContent', array(&$content, $type));
		}
	}

	/**
	 * Get the filtered $obj[$key]
	 *
	 * Получаем отфильтрованное значение $obj[$key]
	 *
	 * @param string $key
	 * @param string|array $type
	 * @return mixed
	 */
	public static function validate($key, $type = 'string')
	{
		if (is_array($type)) {
			return filter_var($key, FILTER_VALIDATE_REGEXP, $type);
		}

		switch ($type) {
			case 'string':
				$filter = FILTER_SANITIZE_STRING;
				break;
			case 'int':
				$filter = FILTER_VALIDATE_INT;
				break;
			case 'bool':
				$filter = FILTER_VALIDATE_BOOLEAN;
				break;
			case 'url':
				$filter = FILTER_VALIDATE_URL;
				break;
			default:
				$filter = FILTER_DEFAULT;
		}

		return filter_var($key, $filter);
	}

	/**
	 * Check whether need to display dates in lowercase for the current language
	 *
	 * Проверяем, нужно ли для текущего языка отображать даты в нижнем регистре
	 *
	 * @return bool
	 */
	public static function isLowerCaseForDates()
	{
		global $txt;

		return in_array($txt['lang_dictionary'], ['pl', 'es', 'ru', 'uk']);
	}
}
