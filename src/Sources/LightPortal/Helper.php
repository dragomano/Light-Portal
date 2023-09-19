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
 * @version 2.2
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Lists\{
	CategoryList,
	IconList,
	PageList,
	TagList,
	TitleList
};
use Bugo\LightPortal\Tasks\Notifier;
use Bugo\LightPortal\Utils\{
	File,
	IntlTrait,
	Post,
	Request,
	Session,
	SMFCache,
	SMFTrait,
};
use Exception;

if (! defined('SMF'))
	die('No direct access...');

trait Helper
{
	use SMFTrait;
	use IntlTrait;

	/**
	 * @param mixed|null $default
	 */
	public function request(?string $key = null, mixed $default = null): mixed
	{
		return $key ? ((new Request())->get($key) ?? $default) : new Request();
	}

	/**
	 * @param mixed|null $default
	 */
	public function post(?string $key = null, mixed $default = null): mixed
	{
		return $key ? ((new Post())->get($key) ?? $default) : new Post();
	}

	public function cache(?string $key = null): SMFCache
	{
		return (new SMFCache($key))->setLifeTime(LP_CACHE_TIME);
	}

	public function files(?string $key = null): mixed
	{
		return $key ? (new File())->get($key) : new File();
	}

	public function session(): Session
	{
		return new Session();
	}

	public function hook(string $hook, array $vars = [], array $plugins = []): void
	{
		call_portal_hook($hook, $vars, $plugins);
	}

	public function require(string $filename): void
	{
		if (is_file($path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $filename . '.php'))
			require_once $path;
	}

	public function getEntityList(string $entity): array
	{
		return match ($entity) {
			'category' => $this->cache('all_categories')->setFallback(CategoryList::class, 'getAll'),
			'page'     => $this->cache('all_pages')->setFallback(PageList::class, 'getAll'),
			'tag'      => $this->cache('all_tags')->setFallback(TagList::class, 'getAll'),
			'title'    => $this->cache('all_titles')->setFallback(TitleList::class, 'getAll'),
			'icon'     => (new IconList)->getAll(),
			'plugin'   => AddonHandler::getInstance()->getAll(),
			default    => [],
		};
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

		if (isset($this->memberContext[$userId]['avatar']['image']))
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
		$types = array_combine(['bbc', 'html', 'php'], [$this->txt['lp_bbc']['title'], $this->txt['lp_html']['title'], $this->txt['lp_php']['title']]);

		return $this->user_info['is_admin'] ? $types : array_slice($types, 0, 2);
	}

	public function getForumThemes(bool $only_available = false): array
	{
		$themes = $this->cache()->get('forum_themes');

		if ($themes === null) {
			$this->prepareInstalledThemes();
			$themes = $this->context['themes'];

			if ($only_available) {
				$themes = array_filter($themes, fn($theme) => $theme['known'] && $theme['enable']);
			}

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
			$this->context['languages'] = [
				$this->language => $temp[$this->language]
			];

			return;
		}

		$this->context['languages'] = array_merge(
			[
				$this->language => $temp[$this->language],
				$this->user_info['language'] => $temp[$this->user_info['language']],
				'english' => $temp['english'],
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

	public function cleanBbcode(array|string &$data): void
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
		$text = html_entity_decode($text);
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
				'task_class' => '\\' . Notifier::class,
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
