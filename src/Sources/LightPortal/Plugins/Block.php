<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

use Bugo\Compat\Utils;
use LightPortal\Enums\Placement;
use LightPortal\Enums\PluginType;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::BLOCK)]
abstract class Block extends Plugin
{
	public function isInPlacements(int $id, array $placements): bool
	{
		$block = Utils::$context['lp_active_blocks'][$id] ?? Utils::$context['lp_block'];

		return in_array($block['placement'], $placements);
	}

	public function isInSidebar(int $id): bool
	{
		return $this->isInPlacements($id, [Placement::LEFT->name(), Placement::RIGHT->name()]);
	}
}
