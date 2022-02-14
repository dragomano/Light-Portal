<?php declare(strict_types=1);

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

use Bugo\LightPortal\Utils\{Cache, File, Post, Request, Session};

use DateTime;
use DateTimeZone;
use Exception;
use IntlDateFormatter;

use function getLanguages;
use function loadMemberContext;
use function loadTemplate;
use function log_error;
use function shorten_subject;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property array $context
 * @property array $modSettings
 * @property array $txt
 * @property array $db_cache
 * @property array $db_temp_cache
 * @property-read array $smcFunc
 * @property-read array $user_info
 * @property-read array $user_profile
 * @property-read array $user_settings
 * @property-read array $memberContext
 * @property-read array $settings
 * @property-read array $options
 * @property-read string $db_type
 * @property-read string $language
 * @property-read string $scripturl
 * @property-read string $boardurl
 * @property-read string $boarddir
 * @property-read string $sourcedir
 */
trait Helper
{
	/**
	 * @return mixed
	 */
	public function &__get(string $name)
	{
		return $GLOBALS[$name];
	}

	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function request(?string $key = null, $default = null)
	{
		return $key ? ((new Request())->get($key) ?? $default) : new Request();
	}

	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function post(?string $key = null, $default = null)
	{
		return $key ? ((new Post())->get($key) ?? $default) : new Post();
	}

	public function cache(?string $key = null): Cache
	{
		return (new Cache($key))->setLifeTime(LP_CACHE_TIME);
	}

	public function files(?string $key = null)
	{
		return $key ? (new File())->get($key) : new File();
	}

	public function session(): Session
	{
		return new Session;
	}

	public function hook(string $hook, array $vars = [], array $plugins = [])
	{
		(new Addon)->run($hook, $vars, $plugins);
	}

	public function require(string $filename)
	{
		if (is_file($path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $filename . '.php'))
			require_once $path;
	}

	public function getAllTitles(string $type = 'page'): array
	{
		if (($titles = $this->cache()->get('all_titles')) === null) {
			$request = $this->smcFunc['db_query']('', '
				SELECT item_id, lang, title
				FROM {db_prefix}lp_titles
				WHERE type = {string:type}
					AND title <> {string:blank_string}
				ORDER BY lang, title',
				[
					'type'         => $type,
					'blank_string' => '',
				]
			);

			$titles = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
				$titles[$row['item_id']][$row['lang']] = $row['title'];
			}

			$this->smcFunc['db_free_result']($request);
			$this->context['lp_num_queries']++;

			$this->cache()->put('all_titles', $titles);
		}

		return $titles;
	}

	/**
	 * @return mixed
	 */
	public function getAllCategories()
	{
		return $this->cache('all_categories')->setFallback(Lists\Category::class, 'getList');
	}

	/**
	 * @return mixed
	 */
	public function getAllTags()
	{
		return $this->cache('all_tags')->setFallback(Lists\Tag::class, 'getList');
	}

	public function getAllAddons(): array
	{
		return (new Addon)->getAll();
	}

	public function getUserAvatar(int $userId, array $userData = []): string
	{
		if (empty($userId))
			return '';

		if (empty($userData))
			$userData = loadMemberData($userId);

		if (! isset($this->memberContext[$userId]) && in_array($userId, $userData)) {
			try {
				loadMemberContext($userId, true);
			} catch (Exception $e) {
				log_error('[LP] getUserAvatar helper: ' . $e->getMessage(), 'user');
			}
		}

		if (empty($this->memberContext[$userId]))
			return '';

		if (isset($this->memberContext[$userId]['avatar']) && isset($this->memberContext[$userId]['avatar']['image'])) {
			return $this->memberContext[$userId]['avatar']['image'];
		}

		return '<img class="avatar" src="' . $this->modSettings['avatar_url'] . '/default.png" loading="lazy" alt="' . $this->memberContext[$userId]['name'] . '">';
	}

	public function getItemsWithUserAvatars(array $items, string $entity = 'author'): array
	{
		$userData = loadMemberData(array_map(fn($item) => $item[$entity]['id'], $items));

		return array_map(function ($item) use ($userData, $entity) {
			$item[$entity]['avatar'] = $this->getUserAvatar((int) $item[$entity]['id'], $userData);
			return $item;
		}, $items);
	}

	public function getFrontPageLayouts(): array
	{
		$layouts = $values = [];

		$allFunctions = get_defined_functions()['user'];

		loadTemplate('LightPortal/ViewFrontPage');

		// Support of custom templates
		if (is_file($customTemplates = $this->settings['theme_dir'] . '/CustomFrontPage.template.php'))
			require_once $customTemplates;

		$frontPageFunctions = array_values(array_diff(get_defined_functions()['user'], $allFunctions));

		preg_match_all('/template_show_([a-z]+)(.*)/', implode("\n", $frontPageFunctions), $matches);

		if ($matches[1]) {
			foreach ($matches[1] as $k => $v) {
				$layouts[] = $name = $v . ($matches[2][$k] ?? '');
				$values[]  = strpos($name, '_') === false ? $this->txt['lp_default'] : ucfirst(explode('_', $name)[1]);
			}

			$layouts = array_combine($layouts, $values);
		}

		return $layouts;
	}

	public function getContentTypes(): array
	{
		$types = array_combine(['bbc', 'html', 'php'], $this->txt['lp_page_types']);

		return $this->user_info['is_admin'] || empty($this->modSettings['lp_prohibit_php']) ? $types : array_slice($types, 0, 2);
	}

	public function getForumThemes(): array
	{
		if (($themes = $this->cache()->get('forum_themes')) === null) {
			$result = $this->smcFunc['db_query']('', '
				SELECT id_theme, value
				FROM {db_prefix}themes
				WHERE variable = {literal:name}',
				[]
			);

			$themes = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($result))
				$themes[$row['id_theme']] = $row['value'];

			$this->smcFunc['db_free_result']($result);
			$this->context['lp_num_queries']++;

			$this->cache()->put('forum_themes', $themes);
		}

		return $themes;
	}

	public function prepareForumLanguages()
	{
		getLanguages();

		if (empty($this->modSettings['userLanguage'])) {
			$default_lang = $this->context['languages'][$this->language];
			$this->context['languages'] = [];
			$this->context['languages'][$this->language] = $default_lang;
		}

		// Move default lang to the top
		$default_lang = $this->context['languages'][$this->language];
		unset($this->context['languages'][$this->language]);
		array_unshift($this->context['languages'], $default_lang);
	}

	public function getIcon(?string $icon = ''): string
	{
		if (empty($icon))
			return '';

		$template = '<i class="' . $icon . '" aria-hidden="true"></i> ';

		$this->hook('prepareIconTemplate', [&$template, $icon]);

		return $template;
	}

	/**
	 * @param array|string $data
	 * @return void
	 */
	public function cleanBbcode(&$data)
	{
		$data = preg_replace('~\[[^]]+]~', '', $data);
	}

	public function getSnakeName(string $value): string
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
	}

	public function getCamelName(string $value): string
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
	}

	public function getTeaser(string $text, int $length = 150): string
	{
		return shorten_subject(strip_tags($text), $length) ?: '...';
	}

	/**
	 * Check whether the current user can view the portal item according to their access rights
	 *
	 * Проверяем, может ли текущий пользователь просматривать элемент портала, согласно его правам доступа
	 */
	public function canViewItem(int $permissions, int $check_id = 0): bool
	{
		switch ($permissions) {
			case 0:
				return $this->user_info['is_admin'];
				// no break
			case 1:
				return $this->user_info['is_guest'];
				// no break
			case 2:
				return $this->user_info['id'] > 0;
				// no break
			case 4:
				return $this->user_info['id'] === $check_id;
				// no break
			default:
				return true;
		}
	}

	/**
	 * Returns a valid set of access rights for the current user
	 *
	 * Возвращает допустимый набор прав доступа текущего пользователя
	 */
	public function getPermissions(): array
	{
		if ($this->user_info['is_admin'])
			return [0, 1, 2, 3];
		elseif ($this->user_info['is_guest'])
			return [1, 3];
		elseif ($this->user_info['id'])
			return [2, 3];

		return [3];
	}

	public function isFrontpage(string $alias): bool
	{
		if (empty($alias) || empty($this->modSettings['lp_frontpage_mode']))
			return false;

		return $this->modSettings['lp_frontpage_mode'] === 'chosen_page'
			&& $this->modSettings['lp_frontpage_alias'] && $this->modSettings['lp_frontpage_alias'] === $alias;
	}

	public function getTranslatedTitle(array $titles): string
	{
		return $titles[$this->user_info['language']] ?? $titles[$this->language] ?? $titles['english'] ?? '';
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
	public function validate(string $key, $type = 'string')
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
	public function getFriendlyNumber(int $value = 0)
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

	public function getDateTime(int $timestamp = 0): DateTime
	{
		$dateTime = new DateTime;
		$dateTime->setTimestamp($timestamp ?: time());
		$dateTime->setTimezone(new DateTimeZone($this->user_settings['timezone'] ?? $this->modSettings['default_timezone']));

		return $dateTime;
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
					return sprintf($this->txt['lp_time_label_in'], __('lp_days_set', compact('days')));

				// Future date in current month
				if ($m === date('m', $now) && $y === date('Y', $now))
					return $this->getLocalDate($timestamp, 'full');
				// Future date in current year
				elseif ($y === date('Y', $now))
					return $this->getLocalDate($timestamp, 'medium');

				// Other future date
				return $this->getLocalDate($timestamp, 'long', 'none');
			}

			// like "In n hours"
			$hours = ($timestamp - $now) / 60 / 60;
			if ($hours >= 1)
				return sprintf($this->txt['lp_time_label_in'], __('lp_hours_set', ['hours' => ceil($hours)]));

			// like "In n minutes"
			$minutes = ($timestamp - $now) / 60;
			if ($minutes >= 1)
				return sprintf($this->txt['lp_time_label_in'], __('lp_minutes_set', ['minutes' => ceil($minutes)]));

			// like "In n seconds"
			return sprintf($this->txt['lp_time_label_in'], __('lp_seconds_set', ['seconds' => abs($timeDifference)]));
		}

		// Less than an hour
		$lastMinutes = round($timeDifference / 60);

		// like "n seconds ago"
		if ($timeDifference < 60)
			return $this->smcFunc['ucfirst'](__('lp_seconds_set', ['seconds' => $timeDifference])) . $this->txt['lp_time_label_ago'];
		// like "n minutes ago"
		elseif ($lastMinutes < 60)
			return $this->smcFunc['ucfirst'](__('lp_minutes_set', ['minutes' => (int) $lastMinutes])) . $this->txt['lp_time_label_ago'];
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

		// like "20 February 2019" (last year)
		return $this->getLocalDate($timestamp, 'long', 'none');
	}

	public function getLocalDate(int $timestamp, string $dateType = 'long', string $timeType = 'short'): string
	{
		if (extension_loaded('intl')) {
			return (new IntlDateFormatter($this->txt['lang_locale'], $this->getPredefinedConstant($dateType), $this->getPredefinedConstant($timeType)))->format($timestamp);
		}

		log_error('[LP] getLocalDate helper: enable intl extension', 'critical');

		return '';
	}

	/**
	 * @see https://www.php.net/manual/en/class.intldateformatter.php
	 */
	public function getPredefinedConstant(string $type): int
	{
		switch ($type) {
			case 'full':
				$const = IntlDateFormatter::FULL;
				break;
			case 'long':
				$const = IntlDateFormatter::LONG;
				break;
			case 'medium':
				$const = IntlDateFormatter::MEDIUM;
				break;
			default:
				$const = IntlDateFormatter::NONE;
		}

		return $const;
	}

	public function getImageFromText(string $text): string
	{
		preg_match('/<img(.*)src(.*)=(.*)"(?<src>.*)"/U', $text, $value);

		return $value['src'] ??= '';
	}

	public function addLazyLoadingForImages()
	{
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/vanilla-lazyload@17/dist/lazyload.min.js', ['external' => true]);
		addInlineJavaScript('
		new LazyLoad();', true);
	}
}
