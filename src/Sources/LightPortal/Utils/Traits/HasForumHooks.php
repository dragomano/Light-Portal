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

namespace LightPortal\Utils\Traits;

use Bugo\Compat\IntegrationHook;
use LightPortal\Enums\ForumHook;

if (! defined('SMF'))
	die('No direct access...');

trait HasForumHooks
{
	protected function applyHook(ForumHook $hook, ?string $class = null): void
	{
		$class ??= static::class;

		$method = method_exists($class, $hook->name) ? $hook->name : '__invoke';

		IntegrationHook::add($hook->name(), "$class::$method#", false);
	}
}
