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

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\IntegrationHook;
use Bugo\LightPortal\Enums\Hook;

use function method_exists;

if (! defined('SMF'))
	die('No direct access...');

trait SMFHookTrait
{
	protected function applyHook(Hook $hook, ?string $class = null): void
	{
		$class ??= static::class;

		$method = method_exists($class, $hook->name) ? $hook->name : '__invoke';

		IntegrationHook::add($hook->name(), "$class::$method#", false);
	}
}
