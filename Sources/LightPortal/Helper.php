<?php declare(strict_types=1);

/**
 * Helper.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Utils\{Cache, File, Post, Request, Session, SMFTrait};

use MessageFormatter;
use DateTime;
use DateTimeZone;
use Exception;
use IntlDateFormatter;

if (! defined('SMF'))
	die('No direct access...');

trait Helper
{
	use SMFTrait;

	/**
	 * @see https://symfony.com/doc/current/translation/message_format.html
	 * @see https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html
	 * @see https://www.php.net/manual/en/class.messageformatter.php
	 * @see https://intl.rmcreative.ru
	 */
	public function translate(string $pattern, array $values = []): string
	{
		if (empty($this->txt['lang_locale']))
			return '';

		if (extension_loaded('intl'))
			return MessageFormatter::formatMessage($this->txt['lang_locale'], $this->txt[$pattern] ?? $pattern, $values) ?? '';

		$this->logError('[LP] translate helper: enable intl extension', 'critical');

		return '';
	}

	/**
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function request(?string $key = null, mixed $default = null): mixed
	{
		return $key ? ((new Request())->get($key) ?? $default) : new Request();
	}

	/**
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function post(?string $key = null, mixed $default = null): mixed
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

	public function hook(string $hook, array $vars = [], array $plugins = []): void
	{
		AddonHandler::getInstance()->run($hook, $vars, $plugins);
	}

	public function require(string $filename): void
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
	public function getAllCategories(): mixed
	{
		return $this->cache('all_categories')->setFallback(Lists\Category::class, 'getList');
	}

	/**
	 * @return mixed
	 */
	public function getAllTags(): mixed
	{
		return $this->cache('all_tags')->setFallback(Lists\Tag::class, 'getList');
	}

	public function getAllAddons(): array
	{
		return AddonHandler::getInstance()->getAll();
	}

	public function getUserAvatar(int $userId, array $userData = []): string
	{
		if (empty($userId))
			return '';

		if (empty($userData))
			$userData = $this->loadMemberData($userId);

		if (! isset($this->memberContext[$userId]) && in_array($userId, $userData)) {
			try {
				$this->loadMemberContext($userId, true);
			} catch (Exception $e) {
				$this->logError('[LP] getUserAvatar helper: ' . $e->getMessage());
			}
		}

		if (empty($this->memberContext[$userId]))
			return '';

		if (isset($this->memberContext[$userId]['avatar']) && isset($this->memberContext[$userId]['avatar']['image']))
			return $this->memberContext[$userId]['avatar']['image'];

		return '<img class="avatar" width="100" height="100" src="' . $this->modSettings['avatar_url'] . '/default.png" loading="lazy" alt="' . $this->memberContext[$userId]['name'] . '">';
	}

	public function getItemsWithUserAvatars(array $items, string $entity = 'author'): array
	{
		$userData = $this->loadMemberData(array_map(fn($item) => $item[$entity]['id'], $items));

		return array_map(function ($item) use ($userData, $entity) {
			$item[$entity]['avatar'] = $this->getUserAvatar((int) $item[$entity]['id'], $userData);
			return $item;
		}, $items);
	}

	public function getContentTypes(): array
	{
		$types = array_combine(['bbc', 'html', 'php'], $this->txt['lp_page_types']);

		return $this->user_info['is_admin'] || empty($this->modSettings['lp_prohibit_php']) ? $types : array_slice($types, 0, 2);
	}

	public function getForumThemes(bool $only_available = false): array
	{
		if (($themes = $this->cache()->get('forum_themes')) === null) {
			$this->prepareInstalledThemes();

			$themes = $this->context['themes'];

			if ($only_available)
				$themes = array_filter($themes, fn ($theme) => $theme['known'] && $theme['enable']);

			$themes = array_column($themes, 'name', 'id');

			$this->cache()->put('forum_themes', $themes);
		}

		return $themes;
	}

	public function prepareForumLanguages(): void
	{
		$this->getLanguages();

		$temp = $this->context['languages'];

		if (empty($this->modSettings['userLanguage'])) {
			$this->context['languages'] = [];
			$this->context['languages'][$this->language] = $temp[$this->language];

			return;
		}

		$this->context['languages'] = array_merge(
			[
				'english'                    => $temp['english'],
				$this->user_info['language'] => $temp[$this->user_info['language']],
				$this->language              => $temp[$this->language]
			],
			$this->context['languages']
		);
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
	public function cleanBbcode(array|string &$data): void
	{
		$data = preg_replace('~\[[^]]+]~', '', $data);
	}

	public function getSnakeName(string $value): string
	{
		return $this->smcFunc['strtolower'](preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
	}

	public function getCamelName(string $value): string
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
	}

	public function getTeaser(string $text, int $length = 150): string
	{
		$text = preg_replace('#(<cite.*?>).*?(</cite>)#', '$1$2', $text);

		return $this->getShortenText(strip_tags($text), $length) ?: '...';
	}

	/**
	 * Check whether the current user can view the portal item according to their access rights
	 *
	 * Проверяем, может ли текущий пользователь просматривать элемент портала, согласно его правам доступа
	 */
	public function canViewItem(int $permissions, int $check_id = 0): bool
	{
		return match ($permissions) {
			0 => $this->user_info['is_admin'],
			1 => $this->user_info['is_guest'],
			2 => $this->user_info['id'] > 0,
			4 => $this->user_info['id'] === $check_id,
			default => true,
		};
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
	 * @param array|string $type
	 * @return mixed
	 */
	public function validate(string $key, array|string $type = 'string'): mixed
	{
		if (is_array($type)) {
			return filter_var($key, FILTER_VALIDATE_REGEXP, $type);
		}

		$filter = match ($type) {
			'string' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'int'    => FILTER_VALIDATE_INT,
			'float'  => FILTER_VALIDATE_FLOAT,
			'bool'   => FILTER_VALIDATE_BOOLEAN,
			'url'    => FILTER_VALIDATE_URL,
			default  => FILTER_DEFAULT,
		};

		return filter_var($key, $filter);
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

	public function getLocalDate(int $timestamp, string $dateType = 'long', string $timeType = 'short'): string
	{
		if (extension_loaded('intl')) {
			return (new IntlDateFormatter($this->txt['lang_locale'], $this->getPredefinedConstant($dateType), $this->getPredefinedConstant($timeType)))->format($timestamp);
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

	public function getImageFromText(string $text): string
	{
		preg_match('/<img(.*)src(.*)=(.*)"(?<src>.*)"/U', $text, $value);

		return $value['src'] ??= '';
	}

	public function makeNotify(string $type, string $action, array $options = []): void
	{
		if (empty($options))
			return;

		$this->smcFunc['db_insert']('',
			'{db_prefix}background_tasks',
			[
				'task_file'  => 'string',
				'task_class' => 'string',
				'task_data'  => 'string'
			],
			[
				'task_file'  => '$sourcedir/LightPortal/Tasks/Notifier.php',
				'task_class' => '\Bugo\LightPortal\Tasks\Notifier',
				'task_data'  => $this->smcFunc['json_encode']([
					'time'              => $options['time'],
					'sender_id'	        => $this->user_info['id'],
					'sender_name'       => $this->user_info['name'],
					'content_author_id' => $options['author_id'],
					'content_type'      => $type,
					'content_id'        => $options['item'],
					'content_action'    => $action,
					'extra'             => $this->smcFunc['json_encode']([
						'content_subject' => $options['title'],
						'content_link'    => $options['url'],
						'sender_gender'   => $this->getUserGender()
					], JSON_UNESCAPED_SLASHES)
				]),
			],
			['id_task']
		);

		$this->context['lp_num_queries']++;
	}

	public function getUserGender(): string
	{
		return empty($this->user_profile[$this->user_info['id']]) ? 'male' : (
			isset($this->user_profile[$this->user_info['id']]['options'])
				&& isset($this->user_profile[$this->user_info['id']]['options']['cust_gender'])
				&& $this->user_profile[$this->user_info['id']]['options']['cust_gender'] === '{gender_2}' ? 'female' : 'male'
		);
	}
}
