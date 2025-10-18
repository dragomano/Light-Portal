<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal;

use Bugo\Compat\ErrorHandler;
use League\Container\Container as LeagueContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

if (! defined('SMF'))
	die('No direct access...');

class Container
{
	private static ?LeagueContainer $container = null;

	public static function getInstance(): LeagueContainer
	{
		if (self::$container === null) {
			self::init();
		}

		return self::$container;
	}

	/**
	 * @template RequestedType
	 * @param class-string<RequestedType>|string $service
	 * @return RequestedType|mixed
	 */
	public static function get(string $service): mixed
	{
		try {
			return self::getInstance()->get($service);
		} catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
			ErrorHandler::log('[LP] container: ' . $e->getMessage(), file: $e->getFile(), line: $e->getLine());
		}

		return false;
	}

	protected static function init(): void
	{
		self::$container = new LeagueContainer();
		self::$container->defaultToShared();
		self::$container->addServiceProvider(new ServiceProvider());
	}
}
