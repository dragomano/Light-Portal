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

namespace LightPortal\Plugins;

use Attribute;
use LightPortal\Enums\PluginType;

if (! defined('LP_NAME'))
	die('No direct access...');

#[Attribute(Attribute::TARGET_CLASS)]
class PluginAttribute
{
	public function __construct(
		public PluginType|array|null $type = null,
		public ?string $icon = null,
		public ?bool $saveable = null,
		public ?bool $showContentClass = null
	) {}
}
