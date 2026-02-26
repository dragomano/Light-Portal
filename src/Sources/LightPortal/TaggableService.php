<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal;

if (! defined('SMF'))
	die('No direct access...');

readonly class TaggableService
{
	public function __construct(private string $className, private Container $container) {}

	public function addArgument(string $dependency): self
	{
		$this->container->registerFactory(
			$this->className,
			fn() => new $this->className($this->container->get($dependency))
		);

		return $this;
	}

	public function addArguments(array $dependencies): self
	{
		$this->container->registerFactory(
			$this->className,
			function () use ($dependencies) {
				$args = array_map(fn($dep) => $this->container->get($dep), $dependencies);

				return new $this->className(...$args);
			}
		);

		return $this;
	}

	public function addTag(string $tag): self
	{
		Container::addServiceToTag($this->className, $tag);

		return $this;
	}
}
