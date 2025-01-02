<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal;

use League\Container\Container as LeagueContainer;

class Container
{
	private static ?LeagueContainer $leagueContainer = null;

	public static function getInstance(): LeagueContainer
	{
		if (self::$leagueContainer === null) {
			self::init();
		}

		return self::$leagueContainer;
	}

	public static function get(string $serviceName): mixed
	{
		return self::getInstance()->get($serviceName);
	}

	protected static function init(): void
	{
		self::$leagueContainer = new LeagueContainer();
		self::$leagueContainer->defaultToShared();
		self::$leagueContainer->addServiceProvider(new ServiceProvider());
	}
}