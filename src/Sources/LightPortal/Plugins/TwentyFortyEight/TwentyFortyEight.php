<?php declare(strict_types=1);

/**
 * @package TwentyFortyEight (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\TwentyFortyEight;

use Bugo\LightPortal\Plugins\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class TwentyFortyEight extends Block
{
	public string $icon = 'fas fa-table-cells';

	public function prepareContent(): void
	{
		echo '
		<div class="puzzle-container">
			<div class="grid"></div>
			<div class="controls">
				<div class="button-control" data-direction="up">↑</div>
				<div class="button-control" data-direction="left">←</div>
				<div class="button-control" data-direction="right">→</div>
				<div class="button-control" data-direction="down">↓</div>
			</div>
		</div>';
	}
}