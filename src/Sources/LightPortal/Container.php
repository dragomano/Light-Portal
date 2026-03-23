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

use Bugo\Compat\ErrorHandler;
use Laminas\ServiceManager\ServiceManager;
use Throwable;

if (! defined('SMF'))
	die('No direct access...');

class Container
{
	private static ?ServiceManager $container = null;

	private static array $tags = [];

	private static array $taggedServices = [];

	private static ?self $instance = null;

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @template RequestedType
	 * @param class-string<RequestedType>|string $service
	 * @return RequestedType|mixed
	 */
	public function get(string $service): mixed
	{
		if (isset(self::$taggedServices[$service])) {
			return self::$taggedServices[$service];
		}

		try {
			return $this->getServiceManager()->get($service);
		} catch (Throwable $e) {
			ErrorHandler::log(
				'[LP] container (get): ' . $e->getMessage(),
				file: $e->getFile(),
				line: $e->getLine()
			);
		}

		return false;
	}

	public function has(string $service): bool
	{
		return $this->getServiceManager()->has($service) || isset(self::$taggedServices[$service]);
	}

	public function add(string $className, callable $factory = null): TaggableService
	{
		if ($factory === null) {
			$factory = fn() => new $className();
		}

		$this->getServiceManager()->setFactory($className, $factory);

		return new TaggableService($className, $this);
	}

	public function set(string $name, mixed $service): void
	{
		$this->getServiceManager()->setService($name, $service);
	}

	public function build(string $name, ?array $options = null): mixed
	{
		try {
			return $this->getServiceManager()->build($name, $options);
		} catch (Throwable $e) {
			ErrorHandler::log(
				'[LP] container (build): ' . $e->getMessage(),
				file: $e->getFile(),
				line: $e->getLine()
			);
		}

		return false;
	}

	public function registerFactory(string $className, callable $factory): void
	{
		$this->getServiceManager()->setFactory($className, $factory);
	}

	public static function addServiceToTag(string $className, string $tag): void
	{
		if (! isset(self::$tags[$tag])) {
			self::$tags[$tag] = [];
		}

		if (! in_array($className, self::$tags[$tag], true)) {
			self::$tags[$tag][] = $className;
		}

		self::updateTaggedServices($tag);
	}

	private function getServiceManager(): ServiceManager
	{
		if (self::$container === null) {
			$this->init();
		}

		return self::$container;
	}

	private static function updateTaggedServices(string $tag): void
	{
		$services = [];

		foreach (self::$tags[$tag] ?? [] as $className) {
			try {
				$services[] = self::getInstance()->get($className);
			} catch (Throwable $e) {
				ErrorHandler::log(
					"[LP] container: Failed to get tagged service '$className' for tag '$tag': " . $e->getMessage(),
					file: $e->getFile(),
					line: $e->getLine()
				);
			}
		}

		self::$taggedServices[$tag] = $services;
	}

	private function init(): void
	{
		$config = ServiceProvider::getConfig();

		self::$container = new ServiceManager($config);

		foreach ($config['tags'] ?? [] as $tag => $services) {
			self::$tags[$tag] = $services;
			self::updateTaggedServices($tag);
		}
	}
}
