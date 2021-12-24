<?php

declare(strict_types = 1);

/**
 * Helper.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Utils\{Cache, File, Request, Session};

if (! defined('SMF'))
	die('No direct access...');

final class Helper
{
	public static function cache(?string $key = null): Cache
	{
		return (new Cache($key))->setLifeTime(LP_CACHE_TIME);
	}

	public static function file(?string $key = null): File
	{
		return new File($key);
	}

	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function post(?string $key = null, $default = null)
	{
		return $key ? ((new Request(true))->get($key) ?? $default) : new Request(true);
	}

	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function request(?string $key = null, $default = null)
	{
		return $key ? ((new Request)->get($key) ?? $default) : new Request;
	}

	public static function session(): Session
	{
		return new Session;
	}

	public static function require(string $filename)
	{
		if (empty($filename))
			return;

		if (is_file($path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $filename . '.php'))
			require_once $path;
	}

	/**
	 * @param array|string $data
	 * @return void
	 */
	public static function cleanBbcode(&$data)
	{
		$data = preg_replace('~\[[^]]+]~', '', $data);
	}

	public static function prepareIconList()
	{
		global $smcFunc;

		if (Helper::request()->has('icons') === false)
			return;

		$data = Helper::request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim($smcFunc['strtolower']($search));

		$all_icons = [];
		$template = '<i class="%1$s"></i>&nbsp;%1$s';

		Addon::run('prepareIconList', array(&$all_icons, &$template));

		$all_icons = $all_icons ?: Helper::getFaIcons();
		$all_icons = array_filter($all_icons, function ($item) use ($search) {
			return strpos($item, $search) !== false;
		});

		$results = [];
		foreach ($all_icons as $icon) {
			$results[] = [
				'innerHTML' => sprintf($template, $icon),
				'text'      => $icon
			];
		}

		exit(json_encode($results));
	}

	public static function getIcon(?string $icon = ''): string
	{
		if (empty($icon))
			return '';

		$template = '<i class="' . $icon . '"></i> ';

		Addon::run('prepareIconTemplate', array(&$template, $icon));

		return $template;
	}

	public static function getPreviewTitle(string $prefix = ''): string
	{
		global $context, $txt;

		return self::getFloatSpan(
			(! empty($prefix) ? $prefix . ' ' : '') . $context['preview_title'],
			$context['right_to_left'] ? 'right' : 'left'
		) . self::getFloatSpan(
			$txt['preview'],
			$context['right_to_left'] ? 'left' : 'right'
		) . '<br>';
	}

	private static function getFloatSpan(string $text, string $direction = 'left'): string
	{
		return '<span class="float' . $direction . '">' . $text . '</span>';
	}

	/**
	 * Get the time in the format "Yesterday", "Today", "X minutes ago", etc.
	 *
	 * Получаем время в формате «Вчера», «Сегодня», «X минут назад» и т. д.
	 */
	public static function getFriendlyTime(int $timestamp, bool $use_time_offset = false): string
	{
		global $modSettings, $user_info, $txt, $smcFunc;

		$currentTime = time();

		$tm = date('H:i', $timestamp); // Use 'g:i a' for am/pm
		$d  = date('j', $timestamp);
		$m  = date('m', $timestamp);
		$y  = date('Y', $timestamp);

		if ($use_time_offset)
			$timestamp = $timestamp - $user_info['time_offset'] * 3600;

		// Difference between current time and $timestamp
		$timeDifference = $currentTime - $timestamp;

		// Just now?
		if (empty($timeDifference))
			return $txt['lp_just_now'];

		// Future time?
		if ($timeDifference < 0) {
			// like "Tomorrow at ..."
			if ($d.$m.$y == date('jmY', strtotime('+1 day')))
				return $txt['lp_tomorrow'] . $tm;

			$days = floor(($timestamp - $currentTime) / 60 / 60 / 24);
			// like "In n days"
			if ($days > 1) {
				if ($days < 7)
					return sprintf($txt['lp_time_label_in'], self::getSmartContext('lp_days_set', compact('days')));

				// Future date in current month
				if ($m == date('m', $currentTime) && $y == date('Y', $currentTime))
					return $txt['days'][date('w', $timestamp)] . ', ' . self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);
				// Future date in current year
				elseif ($y == date('Y', $currentTime))
					return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);

				// Other future date
				return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $y);
			}

			$hours = ($timestamp - $currentTime) / 60 / 60;
			// like "In an hour"
			if ($hours == 1)
				return sprintf($txt['lp_time_label_in'], $txt['lp_hours_set'][0]);

			// like "In n hours"
			if ($hours > 1)
				return sprintf($txt['lp_time_label_in'], self::getSmartContext('lp_hours_set', compact('hours')));

			$minutes = ($timestamp - $currentTime) / 60;
			// like "In a minute"
			if ($minutes == 1)
				return sprintf($txt['lp_time_label_in'], explode(',', $txt['lp_minutes_set'])[0]);

			// like "In n minutes"
			if ($minutes > 1)
				return sprintf($txt['lp_time_label_in'], self::getSmartContext('lp_minutes_set', ['minutes' => ceil($minutes)]));

			// like "In n seconds"
			return sprintf($txt['lp_time_label_in'], self::getSmartContext('lp_seconds_set', ['seconds' => abs($timeDifference)]));
		}

		// Less than an hour
		$last_minutes = round($timeDifference / 60);

		// like "n seconds ago"
		if ($timeDifference < 60)
			return self::getSmartContext('lp_seconds_set', ['seconds' => $timeDifference]) . $txt['lp_time_label_ago'];
		// like "A minute ago"
		elseif ($last_minutes == 1)
			return $smcFunc['ucfirst'](explode(',', $txt['lp_minutes_set'])[0]) . $txt['lp_time_label_ago'];
		// like "n minutes ago"
		elseif ($last_minutes < 60)
			return self::getSmartContext('lp_minutes_set', ['minutes' => (int) $last_minutes]) . $txt['lp_time_label_ago'];
		// like "Today at ..."
		elseif ($d.$m.$y == date('jmY', $currentTime))
			return $txt['today'] . $tm;
		// like "Yesterday at ..."
		elseif ($d.$m.$y == date('jmY', strtotime('-1 day')))
			return $txt['yesterday'] . $tm;
		// like "Tuesday, 20 February, H:m" (current month)
		elseif ($m == date('m', $currentTime) && $y == date('Y', $currentTime))
			return $txt['days'][date('w', $timestamp)] . ', ' . self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);
		// like "20 February, H:m" (current year)
		elseif ($y == date('Y', $currentTime))
			return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $tm);

		// like "20 February 2019" (last year)
		return self::getDateFormat($d, $txt['months'][date('n', $timestamp)], $y);
	}

	/**
	 * Get a string with the day and month in European or American format
	 *
	 * Получаем запись дня и месяца в европейском или американском формате
	 */
	public static function getDateFormat(string $day, string $month, string $postfix): string
	{
		global $txt;

		$comma = strpos($postfix, ":") === false ? ' ' : ', ';

		if ($txt['lang_locale'] == 'en_US')
			return $month . ' ' . $day . $comma . $postfix;

		return $day . ' ' . $month . $comma . $postfix;
	}

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

		Helper::require('Subs-Editor');
		create_control_richedit($editorOptions);

		$context['post_box_name'] = $editorOptions['id'];

		addJavaScriptVar('oEditorID', $context['post_box_name'], true);
		addJavaScriptVar('oEditorObject', 'oEditorHandle_' . $context['post_box_name'], true);
	}

	/**
	 * Check whether the current user can view the portal item according to their access rights
	 *
	 * Проверяем, может ли текущий пользователь просматривать элемент портала, согласно его правам доступа
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
				return ! empty($user_info['id']);

			default:
				return true;
		}
	}

	/**
	 * Returns a valid set of access rights for the current user
	 *
	 * Возвращает допустимый набор прав доступа текущего пользователя
	 */
	public static function getPermissions(): array
	{
		global $user_info;

		if ($user_info['is_admin'] == 1)
			return [0, 1, 2, 3];
		elseif ($user_info['is_guest'] == 1)
			return [1, 3];
		elseif (! empty($user_info['id']))
			return [2, 3];

		return [3];
	}

	public static function isFrontpage(string $alias): bool
	{
		global $modSettings;

		if (empty($alias))
			return false;

		return ! empty($modSettings['lp_frontpage_mode'])
			&& $modSettings['lp_frontpage_mode'] === 'chosen_page'
			&& ! empty($modSettings['lp_frontpage_alias'])
			&& $modSettings['lp_frontpage_alias'] === $alias;
	}

	public static function getTranslatedTitle(array $titleArray): string
	{
		global $user_info, $language;

		if (empty($titleArray))
			return '';

		if (! empty($titleArray[$user_info['language']]))
			return $titleArray[$user_info['language']];

		if (! empty($titleArray[$language]))
			return $titleArray[$language];

		if (! empty($titleArray['english']))
			return $titleArray['english'];

		return '';
	}

	public static function getSnakeName(string $value): string
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
	}

	public static function getCamelName(string $value): string
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
	}

	public static function getTeaser(string $text, int $length = 150): string
	{
		return shorten_subject(strip_tags($text), $length) ?: '...';
	}

	public static function getForumThemes(): array
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT id_theme, value
			FROM {db_prefix}themes
			WHERE variable = {literal:name}',
			array()
		);

		$themes = [];
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$themes[$row['id_theme']] = $row['value'];

		$smcFunc['db_free_result']($result);
		$smcFunc['lp_num_queries']++;

		return $themes;
	}

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

	public static function prepareContent(string &$content, string $type = 'bbc', int $block_id = 0, int $cache_time = 0)
	{
		global $context;

		! empty($block_id) && ! empty($context['lp_active_blocks'][$block_id])
			? $parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? []
			: $parameters = $context['lp_block']['options']['parameters'] ?? [];

		ob_start();

		Addon::run('prepareContent', array($type, $block_id, $cache_time, $parameters));

		$content = ob_get_clean();
	}

	public static function prepareBbcContent(array &$entity)
	{
		global $smcFunc;

		if ($entity['type'] !== 'bbc')
			return;

		$entity['content'] = $smcFunc['htmlspecialchars']($entity['content'], ENT_QUOTES);

		Helper::require('Subs-Post');

		preparsecode($entity['content']);
	}

	public static function parseContent(string &$content, string $type = 'bbc')
	{
		if ($type === 'bbc') {
			$content = parse_bbc($content);

			// Integrate with the Paragrapher mod
			call_integration_hook('integrate_paragrapher_string', array(&$content));

			return;
		} elseif ($type === 'html') {
			$content = un_htmlspecialchars($content);

			return;
		} elseif ($type === 'php') {
			$content = trim(un_htmlspecialchars($content));
			$content = trim($content, '<?php');
			$content = trim($content, '?>');

			ob_start();

			try {
				eval(html_entity_decode($content, ENT_COMPAT, 'UTF-8'));
			} catch (\ParseError $p) {
				echo $p->getMessage();
			}

			$content = ob_get_clean();

			return;
		}

		Addon::run('parseContent', array(&$content, $type));
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

			case 'float':
				$filter = FILTER_VALIDATE_FLOAT;
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
	 * @return int|float
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
				if (! empty($row['lang']))
					$titles[$row['item_id']][$row['lang']] = $row['title'];
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			self::cache()->put('all_titles', $titles);
		}

		return $titles;
	}

	/**
	 * @return mixed
	 */
	public static function getAllCategories()
	{
		return self::cache('all_categories')->setFallback(Lists\Category::class, 'getList');
	}

	/**
	 * @return mixed
	 */
	public static function getAllTags()
	{
		return self::cache('all_tags')->setFallback(Lists\Tag::class, 'getList');
	}

	public static function getFaIcons(): array
	{
		if (($icons = self::cache()->get('all_icons', LP_CACHE_TIME * 7)) === null) {
			$content = file_get_contents('https://raw.githubusercontent.com/FortAwesome/Font-Awesome/master/metadata/icons.json');
			$json = json_decode($content);

			if (empty($json))
				return [];

			$icons = [];
			foreach ($json as $icon => $value) {
				foreach ($value->styles as $style) {
					$icons[] = 'fa' . substr($style, 0, 1) . ' fa-' . $icon;
				}
			}

			self::cache()->put('all_icons', $icons, LP_CACHE_TIME * 7);
		}

		return $icons;
	}

	public static function getUserAvatar(int $userId): array
	{
		global $memberContext;

		if (empty($userId))
			return [];

		if (! isset($memberContext[$userId]) && in_array($userId, loadMemberData($userId))) {
			try {
				loadMemberContext($userId, true);
			} catch (\Exception $e) {
				log_error('[LP] getUserAvatar helper: ' . $e->getMessage(), 'user');
			}
		}

		return $memberContext[$userId]['avatar'] ?? [];
	}

	public static function preparePostFields(string $defaultTab = 'tuning')
	{
		global $context;

		foreach ($context['posting_fields'] as $item => $data) {
			if (! empty($data['input']['after'])) {
				$tag = 'div';

				if (isset($data['input']['type']) && in_array($data['input']['type'], ['checkbox', 'number']))
					$tag = 'span';

				$context['posting_fields'][$item]['input']['after'] = "<$tag class=\"descbox alternative2 smalltext\">{$data['input']['after']}</$tag>";
			}

			// Fancy checkbox
			if (isset($data['input']['type']) && $data['input']['type'] == 'checkbox') {
				$data['input']['attributes']['class'] = 'checkbox';
				$data['input']['after'] = '<label class="label" for="' . $item . '"></label>' . ($context['posting_fields'][$item]['input']['after'] ?? '');
				$context['posting_fields'][$item] = $data;
			}

			if (empty($data['input']['tab']))
				$context['posting_fields'][$item]['input']['tab'] = $defaultTab;
		}

		loadTemplate('LightPortal/ManageSettings');
	}

	public static function getImageFromText(string $text): string
	{
		preg_match('/<img(.*)src(.*)=(.*)"(?<src>.*)"/U', $text, $value);

		return $value['src'] ??= '';
	}

	/**
	 * @see https://symfony.com/doc/current/translation/message_format.html
	 * @see https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html
	 * @see https://www.php.net/manual/en/class.messageformatter.php
	 */
	public static function getSmartContext(string $pattern, array $values = []): string
	{
		global $txt;

		return \MessageFormatter::formatMessage($txt['lang_locale'], $txt[$pattern] ?? $pattern, $values) ?: '';
	}
}
