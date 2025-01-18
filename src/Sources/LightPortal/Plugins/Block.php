<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\Utils;

if (! defined('LP_NAME'))
	die('No direct access...');

abstract class Block extends Plugin
{
	public string $type = 'block';

	public function isInPlacements(int $id, array $placements): bool
	{
		$block = Utils::$context['lp_active_blocks'][$id] ?? Utils::$context['lp_block'];

		return in_array($block['placement'], $placements);
	}

	public function isInSidebar(int $id): bool
	{
		return $this->isInPlacements($id, ['left', 'right']);
	}
}
