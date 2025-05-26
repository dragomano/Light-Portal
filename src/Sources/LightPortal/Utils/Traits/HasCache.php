<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Utils\Traits;

use Bugo\Compat\User;
use Bugo\LightPortal\Utils\CacheInterface;

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
		if ($key) {
			$key .= $this->getUserSuffix();
		}

		return $key;
	}

	protected function appendLangSuffix(?string $key): ?string
	{
		if ($key) {
			$key .= $this->getLangSuffix();
		}

		return $key;
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
