<?php declare(strict_types=1);

/**
 * IntegrationHook.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Utils;

use function call_integration_hook;

if (! defined('SMF'))
	die('No direct access...');

final class IntegrationHook
{
	public static function call(string $name, array $parameters = []): array
	{
		return call_integration_hook($name, $parameters);
	}
}
