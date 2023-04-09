<?php declare(strict_types=1);

/**
 * Block.php
 *
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Addons;

if (! defined('SMF'))
	die('No direct access...');

abstract class Block extends Plugin
{
	public function isBlockInPlacements(int $block_id, array $positions): bool
	{
		return in_array(($this->context['lp_active_blocks'][$block_id] ?? $this->context['lp_block'])['placement'], $positions);
	}
}