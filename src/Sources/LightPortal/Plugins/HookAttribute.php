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

namespace Bugo\LightPortal\Plugins;

use Attribute;
use Bugo\LightPortal\Enums\PortalHook;

if (! defined('LP_NAME'))
	die('No direct access...');

#[Attribute(Attribute::TARGET_METHOD)]
class HookAttribute
{
	public function __construct(public PortalHook $hook) {}
}
