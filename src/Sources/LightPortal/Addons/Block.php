<?php declare(strict_types=1);

/**
 * Block.php
 *
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Addons;

use Bugo\Compat\Utils;

if (! defined('SMF'))
	die('No direct access...');

abstract class Block extends Plugin
{
	public function isInPlacements(int $block_id, array $positions): bool
	{
		return in_array((Utils::$context['lp_active_blocks'][$block_id] ?? Utils::$context['lp_block'])['placement'], $positions);
	}

	public function isInSidebar(int $block_id): bool
	{
		return $this->isInPlacements($block_id, ['left', 'right']);
	}
}
