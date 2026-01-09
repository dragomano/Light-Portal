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

namespace LightPortal\Hooks;

use LightPortal\Events\EventDispatcherInterface;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractHook implements HookInterface
{
	public function __construct(protected ?EventDispatcherInterface $dispatcher = null)
	{
		$this->dispatcher = $dispatcher ?: app(EventDispatcherInterface::class);
	}
}
