<?php declare(strict_types=1);

/**
 * Helper.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal;

use Bugo\Compat\{Config, Db, ErrorHandler, Lang, User, Utils};
use Bugo\FontAwesome\IconBuilder;
use Bugo\LightPortal\Enums\{ContentType, Permission};
use Bugo\LightPortal\Utils\{Cache, EntityManager, File, Post};
use Bugo\LightPortal\Utils\{Request, Session, SMFTrait};
use Exception;
use Nette\Utils\Html;

if (! defined('SMF'))
	die('No direct access...');

trait Helper
{
	use SMFTrait;

	public function request(?string $key = null, mixed $default = null): mixed
	{
		return $key ? ((new Request())->get($key) ?? $default) : new Request();
	}

	public function post(?string $key = null, mixed $default = null): mixed
	{
		return $key ? ((new Post())->get($key) ?? $default) : new Post();
	}

	public function files(?string $key = null): mixed
	{
		return $key ? (new File())->get($key) : new File();
	}

	public function cache(?string $key = null): Cache
	{
		return new Cache($key, LP_CACHE_TIME);
	}

	public function session(?string $key = null): Session
	{
		return new Session($key);
	}

	public function hook(string $hook, array $vars = [], array $plugins = []): void
	{
		//AddonHandler::getInstance()->run($hook, $vars, $plugins);
		static::$addonHandler->run($hook, $vars, $plugins);
		//static::$addonHandler->$hook($vars, $plugins);
	}

	public function getEntityData(string $entity): array
	{
		return (new EntityManager())($entity);
	}

	public function require(string $filename, string $extension = '.php'): void
	{
		if (is_file($path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $filename . $extension))
			require_once $path;
	}

	public function callHelper(callable $callback): mixed
	{
		return call_user_func($callback);
	}

	public function getUserAvatar(int $userId, array $userData = []): string
	{
		if (empty($userId))
			return '';

		if (empty($userData))
			$userData = User::loadMemberData([$userId]);

		if (! isset(User::$memberContext[$userId]) && in_array($userId, $userData)) {
			try {
				User::loadMemberContext($userId, true);
			} catch (Exception $e) {
				ErrorHandler::log('[LP] getUserAvatar helper: ' . $e->getMessage());
			}
		}

		if (empty(User::$memberContext[$userId]))
			return '';

		return User::$memberContext[$userId]['avatar']['image']
			?? Html::el('img', [
				'class' => 'avatar',
				'width' => 100,
				'height' => 100,
				'src' => Config::$modSettings['avatar_url'] . '/default.png',
				'loading' => 'lazy',
				'alt' => User::$memberContext[$userId]['name'],
			])->toHtml();
	}

	public function getItemsWithUserAvatars(array $items, string $entity = 'author'): array
	{
		$userData = User::loadMemberData(array_map(static fn($item) => $item[$entity]['id'], $items));

		return array_map(function ($item) use ($userData, $entity) {
			$item[$entity]['avatar'] = $this->getUserAvatar((int) $item[$entity]['id'], $userData);
			return $item;
		}, $items);
	}

	public function getContentTypes(): array
	{
		$types = [
			ContentType::BBC->name()  => Lang::$txt['lp_bbc']['title'],
			ContentType::HTML->name() => Lang::$txt['lp_html']['title'],
			ContentType::PHP->name()  => Lang::$txt['lp_php']['title'],
		];

		return User::$info['is_admin'] ? $types : array_slice($types, 0, 2);
	}

	public function getForumThemes(): array
	{
		$themes = $this->cache()->get('forum_themes');

		if ($themes === null) {
			$result = Db::$db->query('', '
				SELECT id_theme, value
				FROM {db_prefix}themes
				WHERE id_theme IN ({array_int:themes})
					AND variable = {literal:name}',
				[
					'themes' => empty(Config::$modSettings['knownThemes'])
						? []
						: explode(',', (string) Config::$modSettings['knownThemes']),
				]
			);

			$themes = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$themes[$row['id_theme']] = [
					'id'   => (int) $row['id_theme'],
					'name' => $row['value'],
				];
			}

			Db::$db->free_result($result);

			$themes = array_column($themes, 'name', 'id');
			$this->cache()->put('forum_themes', $themes);
		}

		return $themes;
	}

	public function prepareForumLanguages(): void
	{
		$temp = Lang::get();

		if (empty(Config::$modSettings['userLanguage'])) {
			Utils::$context['lp_languages'] = [
				Config::$language => $temp[Config::$language]
			];

			return;
		}

		Utils::$context['lp_languages'] = array_merge([
			User::$info['language'] => $temp[User::$info['language']],
			Config::$language => $temp[Config::$language],
		], $temp);
	}

	public function getIcon(?string $icon = ''): string
	{
		if (empty($icon))
			return '';

		$template = (new IconBuilder($icon, ['aria-hidden' => true]))->html() . ' ';

		$this->hook('prepareIconTemplate', [&$template, $icon]);

		return $template;
	}

	public function cleanBbcode(array|string &$data): void
	{
		$data = preg_replace('~\[[^]]+]~', '', $data);
	}

	public function getSnakeName(string $value): string
	{
		return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
	}

	public function getCamelName(string $value): string
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
	}

	public function getTeaser(string $text, int $length = 150): string
	{
		$text = html_entity_decode($text);
		$text = preg_replace('#(<cite.*?>).*?(</cite>)#', '$1$2', $text);

		return Utils::shorten(strip_tags((string) $text), $length) ?: '...';
	}

	/**
	 * Check whether the current user can view the portal item according to their access rights
	 *
	 * Проверяем, может ли текущий пользователь просматривать элемент портала, согласно его правам доступа
	 */
	public function canViewItem(Permission|int $permission, int $userId = 0): bool
	{
		$permission = is_int($permission) ? Permission::tryFrom($permission) : $permission;

		return match ($permission) {
			Permission::ADMIN  => User::$info['is_admin'],
			Permission::GUEST  => User::$info['is_guest'],
			Permission::MEMBER => User::$info['id'] > 0,
			Permission::ALL    => true,
			Permission::OWNER  => User::$info['id'] === $userId,
			default             => false,
		};
	}

	/**
	 * Returns a valid set of access rights for the current user
	 *
	 * Возвращает допустимый набор прав доступа текущего пользователя
	 */
	public function getPermissions(): array
	{
		return match (true) {
			User::$info['is_admin'] => array_filter(Permission::values(), fn($value) => $value !== Permission::OWNER->value),
			User::$info['is_guest'] => [Permission::GUEST->value, Permission::ALL->value],
			User::$info['id'] > 0   => [Permission::MEMBER->value, Permission::ALL->value],
			default                 => [Permission::ALL->value],
		};
	}

	public function getTranslatedTitle(array $titles): string
	{
		return $titles[User::$info['language']] ?? $titles[Config::$language] ?? '';
	}

	/**
	 * Get the filtered $var
	 *
	 * Получаем отфильтрованное значение $var
	 */
	public function filterVar(mixed $var, array|string $type = 'string'): mixed
	{
		if (is_array($type)) {
			return filter_var($var, FILTER_VALIDATE_REGEXP, $type);
		}

		$filter = match ($type) {
			'string' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'int'    => FILTER_VALIDATE_INT,
			'float'  => FILTER_VALIDATE_FLOAT,
			'bool'   => FILTER_VALIDATE_BOOLEAN,
			'url'    => FILTER_VALIDATE_URL,
			default  => FILTER_DEFAULT,
		};

		return filter_var($var, $filter);
	}

	public function getImageFromText(string $text): string
	{
		preg_match('/<img(.*)src(.*)=(.*)"(?<src>.*)"/U', $text, $value);

		$result = $value['src'] ??= '';

		if (empty($result) || str_contains($result, (string) Config::$modSettings['smileys_url']))
			return '';

		return $result;
	}

	public function isFrontpage(string $slug): bool
	{
		if ($slug === '' || empty(Config::$modSettings['lp_frontpage_chosen_page']))
			return false;

		return $this->isFrontpageMode('chosen_page') && Config::$modSettings['lp_frontpage_chosen_page'] === $slug;
	}

	public function isFrontpageMode(string $mode): bool
	{
		if (empty(Config::$modSettings['lp_frontpage_mode']))
			return false;

		return Config::$modSettings['lp_frontpage_mode'] === $mode;
	}

	public function isStandaloneMode(): bool
	{
		if (empty(Config::$modSettings['lp_standalone_mode']))
			return false;

		return ! empty(Config::$modSettings['lp_standalone_url']);
	}

	public function getCommentBlockType(): string
	{
		return Config::$modSettings['lp_show_comment_block'] ?? '';
	}
}
