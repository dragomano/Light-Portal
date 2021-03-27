<?php

namespace Bugo\LightPortal;

use Bugo\LightPortal\Utils\{Cache, Post, Request, Server, Session};

/**
 * Helpers.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
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
	 * @param string|null $key
	 * @param string|null $funcName
	 * @param string|null $class
	 * @param int $time (in seconds)
	 * @param mixed $vars
	 * @return mixed
	 */
	public static function cache(string $key = null, string $funcName = null, string $class = null, int $time = LP_CACHE_TIME, ...$vars)
	{
		return $key ? (new Cache)($key, $funcName, $class, $time, ...$vars) : new Cache;
	}

	/**
	 * Get $_POST object
	 *
	 * Получаем объект $_POST
	 *
	 * @param string|null $key
	 * @param mixed $default
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
	 * @param mixed $default
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
	 * @param mixed $default
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
	 * @param mixed $default
	 * @return mixed
	 */
	public static function session($key = null, $default = null)
	{
		return $key ? (new Session)($key, $default) : new Session;
	}

	/**
	 * Require $filename only once
	 *
	 * Подключаем $filename единожды
	 *
	 * @param string $filename
	 * @return void
	 */
	public static function require(string $filename)
	{
		global $sourcedir;

		if (empty($filename))
			return;

		require_once($sourcedir . '/' . $filename . '.php');
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
	 * @param string|null $icon
	 * @param string|null $type
	 * @return string
	 */
	public static function getIcon($icon = null, $type = null): string
	{
		global $context;

		$icon = $icon ?? ($context['lp_block']['icon'] ?? $context['lp_page']['options']['icon'] ?? '');
		$type = $type ?? ($context['lp_block']['icon_type'] ?? $context['lp_page']['options']['icon_type'] ?? 'fas');

		if (!empty($icon))
			return '<i class="' . $type . ' fa-' . $icon . '"></i> ';

		return '';
	}

	/**
	 * @param string|null $prefix
	 * @return string
	 */
	public static function getPreviewTitle($prefix = null): string
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
	private static function getFloatSpan(string $text, string $direction = 'left'): string
	{
		return '<span class="float' . $direction . '">' . $text . '</span>';
	}

	/**
	 * Get the word with the correct ending, depending on the number $num and the array|string $str with possible forms
	 *
	 * Получаем слово с правильным окончанием, в зависимости от числа $num и массива|строки $str с возможными формами
	 *
	 * @see https://github.com/dragomano/Light-Portal/wiki/To-translators
	 * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
	 * @see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
	 *
	 * @param int $num
	 * @param array|string $str массив или строка с возможными вариантами (если в языке только одна форма, см. rule #0)
	 * @return string
	 */
	public static function getText(int $num, $str): string
	{
		global $txt;

		$str = is_array($str) ? $str : explode(',', $str);
		$str = array_map('trim', $str);

		// Plural rule #0 (Chinese, Japanese, Persian, Turkish, Thai, Indonesian, Malay)
		$rule_zero = array('zh', 'ja', 'fa', 'tr', 'th', 'id', 'ms');
		if (in_array($txt['lang_dictionary'], $rule_zero))
			return $num . ' ' . $str[0];

		// Plural rule #2 (French, Portuguese_brazilian)
		$rule_two = array('fr', 'pt');
		if (in_array($txt['lang_dictionary'], $rule_two))
			return $num . ' ' . $str[($num == 0 || $num == 1) ? 0 : 1];

		// Just in case
		if (!isset($str[1]))
			$str[1] = $str[0];
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

		// Plural rule #7 (Bosnian, Croatian, Serbian, Russian, Ukrainian)
		$rule_seven = array('bs', 'hr', 'sr', 'ru', 'uk');
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

		// Plural rule #1 (Danish, Dutch, English, German, Norwegian, Swedish, Estonian, Finnish, Hungarian, Greek, Hebrew, Italian, Portuguese_pt, Spanish, Catalan, Vietnamese, Esperanto, Galician, Albanian, Bulgarian)
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
	public static function getFriendlyTime(int $timestamp, bool $use_user_offset = false): string
	{
		global $modSettings, $user_info, $txt, $smcFunc;

		$current_time = time();

		$tm = date('H:i', $timestamp); // Use 'g:i a' for am/pm
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
					return sprintf($txt['lp_time_label_in'], self::getText($days, $txt['lp_days_set']));

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
				return sprintf($txt['lp_time_label_in'], self::getText($hours, $txt['lp_hours_set']));

			$minutes = ($timestamp - $current_time) / 60;
			// like "In a minute"
			if ($minutes == 1)
				return sprintf($txt['lp_time_label_in'], explode(',', $txt['lp_minutes_set'])[0]);

			// like "In n minutes"
			if ($minutes > 1)
				return sprintf($txt['lp_time_label_in'], self::getText(ceil($minutes), $txt['lp_minutes_set']));

			// like "In n seconds"
			return sprintf($txt['lp_time_label_in'], self::getText(abs($time_difference), $txt['lp_seconds_set']));
		}

		// Less than an hour
		$last_minutes = round($time_difference / 60);

		// like "n seconds ago"
		if ($time_difference < 60)
			return self::getText($time_difference, $txt['lp_seconds_set']) . $txt['lp_time_label_ago'];
		// like "A minute ago"
		elseif ($last_minutes == 1)
			return $smcFunc['ucfirst'](explode(',', $txt['lp_minutes_set'])[0]) . $txt['lp_time_label_ago'];
		// like "n minutes ago"
		elseif ($last_minutes < 60)
			return self::getText((int) $last_minutes, $txt['lp_minutes_set']) . $txt['lp_time_label_ago'];
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
	public static function getDateFormat(int $day, string $month, string $postfix): string
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
	 * @param string $content
	 * @return void
	 */
	public static function createBbcEditor(string $content = '')
	{
		global $context;

		$editorOptions = array(
			'id'           => 'content',
			'value'        => $content,
			'height'       => '1px',
			'width'        => '100%',
			'preview_type' => 2,
			'required'     => true
		);

		Helpers::require('Subs-Editor');
		create_control_richedit($editorOptions);

		$context['post_box_name'] = $editorOptions['id'];

		addJavaScriptVar('oEditorID', $context['post_box_name'], true);
		addJavaScriptVar('oEditorObject', 'oEditorHandle_' . $context['post_box_name'], true);
	}

	/**
	 * Check whether the current user can view the portal item according to their access rights
	 *
	 * Проверяем, может ли текущий пользователь просматривать элемент портала, согласно его правам доступа
	 *
	 * @param int $permissions
	 * @return bool
	 */
	public static function canViewItem(int $permissions): bool
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
	public static function getPermissions(): array
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
	public static function isFrontpage(string $alias): bool
	{
		global $modSettings;

		if (empty($alias))
			return false;

		return !empty($modSettings['lp_frontpage_mode'])
			&& $modSettings['lp_frontpage_mode'] == 'chosen_page'
			&& !empty($modSettings['lp_frontpage_alias'])
			&& $modSettings['lp_frontpage_alias'] == $alias;
	}

	/**
	 * Get an object title, according to the user's language, or the forum's language, or in English
	 *
	 * Получаем заголовок объекта, в соответствии с языком пользователя или форума, или на английском
	 *
	 * @param array $object
	 * @return string
	 */
	public static function getTitle(array $object): string
	{
		global $user_info, $language;

		if (empty($object) || !isset($object['title']))
			return '';

		if (!empty($object['title'][$user_info['language']]))
			return $object['title'][$user_info['language']];

		if (!empty($object['title'][$language]))
			return $object['title'][$language];

		if (!empty($object['title']['english']))
			return $object['title']['english'];

		return '';
	}

	/**
	 * @param string $value
	 * @param string $delimiter
	 * @return string
	 */
	public static function getSnakeName(string $value, string $delimiter = '_'): string
	{
		if (!ctype_lower($value)) {
			$value = preg_replace('/\s+/u', '', ucwords($value));

			$value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
		}

		return $value;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public static function getTeaser($text): string
	{
		if (empty($text))
			return '...';

		$text = strip_tags(explode('<br>', $text)[0]);

		return $text ?: '...';
	}

	/**
	 * @return array
	 */
	public static function getForumThemes(): array
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT id_theme, value
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
		$smcFunc['lp_num_queries']++;

		return $current_themes;
	}

	/**
	 * @return void
	 */
	public static function prepareForumLanguages()
	{
		global $modSettings, $context, $language;

		getLanguages();

		if (empty($modSettings['userLanguage'])) {
			$default_lang = $context['languages'][$language];
			$context['languages'] = [];
			$context['languages'][$language] = $default_lang;
		}

		// Move default lang to the top
		$default_lang = $context['languages'][$language];
		unset($context['languages'][$language]);
		array_unshift($context['languages'], $default_lang);
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
					$content = html_entity_decode($content, ENT_COMPAT, 'UTF-8');

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
	public static function validate(string $key, $type = 'string')
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
	 * Get a number in friendly format ("1K" instead "1000", etc)
	 *
	 * Получаем число в приятном глазу формате (для чисел более 10к)
	 *
	 * @param int $value
	 * @return float
	 */
	public static function getFriendlyNumber(int $value = 0)
	{
		if ($value < 10000)
			return $value;

		$k   = pow(10, 3);
		$mil = pow(10, 6);
		$bil = pow(10, 9);

		if ($value >= $bil)
			return number_format($value / $bil, 1) . 'B';
		else if ($value >= $mil)
			return number_format($value / $mil, 1) . 'M';
		else if ($value >= $k)
			return number_format($value / $k, 1) . 'K';

		return $value;
	}

	/**
	 * Get array of titles for page/block object type
	 *
	 * Получаем массив всех заголовков для объекта типа page/block
	 *
	 * @param string $type
	 * @return array
	 */
	public static function getAllTitles(string $type = 'page'): array
	{
		global $smcFunc;

		if (($titles = self::cache()->get('all_titles')) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT item_id, lang, title
				FROM {db_prefix}lp_titles
				WHERE type = {string:type}
				ORDER BY lang, title',
				array(
					'type' => $type
				)
			);

			$titles = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				if (!empty($row['lang']))
					$titles[$row['item_id']][$row['lang']] = $row['title'];
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			self::cache()->put('all_titles', $titles);
		}

		return $titles;
	}

	/**
	 * Get the total number of active pages
	 *
	 * Подсчитываем общее количество активных страниц
	 *
	 * @param bool $all - подсчитывать все страницы
	 * @return int
	 */
	public static function getNumActivePages($all = false): int
	{
		global $user_info, $smcFunc;

		if (($num_pages = self::cache()->get('num_active_pages' . ($all ? '' : ('_u' . $user_info['id'])))) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}' . ($user_info['is_admin'] || $all ? '' : '
					AND author_id = {int:user_id}'),
				array(
					'status'  => Page::STATUS_ACTIVE,
					'user_id' => $user_info['id']
				)
			);

			[$num_pages] = $smcFunc['db_fetch_row']($request);

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			self::cache()->put('num_active_pages' . ($all ? '' : ('_u' . $user_info['id'])), $num_pages);
		}

		return (int) $num_pages;
	}

	/**
	 * @return array
	 */
	public static function getAllCategories()
	{
		return self::cache('all_categories', 'getList', Category::class);
	}

	/**
	 * @return array
	 */
	public static function getAllTags()
	{
		return self::cache('all_tags', 'getList', Tag::class);
	}

	/**
	 * Prepare field array with entity options
	 *
	 * Формируем массив полей с настройками сущности
	 *
	 * @return void
	 */
	public static function preparePostFields()
	{
		global $context;

		foreach ($context['posting_fields'] as $item => $data) {
			if ($item !== 'icon' && !empty($data['input']['after']))
				$context['posting_fields'][$item]['input']['after'] = '<div class="descbox alternative2 smalltext">' . $data['input']['after'] . '</div>';

			if (isset($data['input']['type']) && $data['input']['type'] == 'checkbox') {
				$data['input']['attributes']['class'] = 'checkbox';
				$data['input']['after'] = '<label class="label" for="' . $data['input']['attributes']['id'] . '"></label>' . ($context['posting_fields'][$item]['input']['after'] ?? '');
				$context['posting_fields'][$item] = $data;
			}

			if (empty($data['input']['tab']))
				$context['posting_fields'][$item]['input']['tab'] = 'tuning';
		}

		loadTemplate('LightPortal/ManageSettings');
	}
}
