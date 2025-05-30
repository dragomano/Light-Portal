<?php declare(strict_types=1);

/**
 * @package Calculator (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\Calculator;

use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class Calculator extends Block
{
	public string $icon = 'fas fa-calculator';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params['no_content_class'] = true;
	}

	public function prepareContent(Event $e): void
	{
		echo $this->getFromTemplate('show_calculator_block', $e->args->id);
	}
}
