<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils\Traits;

use Bugo\Compat\User;
use LightPortal\Utils\CacheInterface;

use function LightPortal\app;

trait HasCache
{
	public function cache(?string $key = null): CacheInterface
	{
		return $this->getCacheInstance($key);
	}

	public function langCache(?string $key = null): CacheInterface
	{
		return $this->getCacheInstance($this->appendLangSuffix($key));
	}

	public function userCache(?string $key = null): CacheInterface
	{
		return $this->getCacheInstance($this->appendUserSuffix($key));
	}

	protected function appendUserSuffix(?string $key): ?string
	{
		return $key ? $key . $this->getUserSuffix() : null;
	}

	protected function appendLangSuffix(?string $key): ?string
	{
		return $key ? $key . $this->getLangSuffix() : null;
	}

	protected function getUserSuffix(): string
	{
		return '_u' . User::$me->id;
	}

	protected function getLangSuffix(): string
	{
		return $this->getUserSuffix() . '_' . User::$me->language;
	}

	protected function getCacheInstance(?string $key = null): CacheInterface
	{
		return $key === null
			? app(CacheInterface::class)
			: app(CacheInterface::class)->withKey($key);
	}
}
