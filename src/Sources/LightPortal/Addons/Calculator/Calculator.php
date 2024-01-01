<?php

/**
 * Calculator.php
 *
 * @package Calculator (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 02.12.23
 */

namespace Bugo\LightPortal\Addons\Calculator;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class Calculator extends Block
{
	public string $icon = 'fas fa-calculator';

	public function blockOptions(array &$options): void
	{
		$options['calculator']['no_content_class'] = true;
	}

	public function prepareContent(object $data): void
	{
		if ($data->type !== 'calculator')
			return;

		echo $this->getFromTemplate('show_calculator_block', $data->block_id);
	}
}
