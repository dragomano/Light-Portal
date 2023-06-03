<?php

/**
 * HidingBlocks.php
 *
 * @package HidingBlocks (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 09.05.23
 */

namespace Bugo\LightPortal\Addons\HidingBlocks;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class HidingBlocks extends Plugin
{
	public string $type = 'block_options';

	private array $classes = ['xs', 'sm', 'md', 'lg', 'xl'];

	public function init()
	{
		foreach ($this->context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['hidden_breakpoints']))
				continue;

			$breakpoints = array_flip(explode(',', $block['parameters']['hidden_breakpoints']));
			foreach ($this->classes as $class) {
				if (array_key_exists($class, $breakpoints)) {
					if (empty($this->context['lp_active_blocks'][$id]['custom_class']))
						$this->context['lp_active_blocks'][$id]['custom_class'] = '';

					$this->context['lp_active_blocks'][$id]['custom_class'] .= ' hidden-' . $class;
				}
			}
		}
	}

	public function blockOptions(array &$options)
	{
		$options[$this->context['current_block']['type']]['parameters']['hidden_breakpoints'] = [];
	}

	public function validateBlockData(array &$parameters)
	{
		$parameters['hidden_breakpoints'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		$this->context['posting_fields']['hidden_breakpoints']['label']['html'] = $this->txt['lp_hiding_blocks']['hidden_breakpoints'];
		$this->context['posting_fields']['hidden_breakpoints']['input']['html'] = (new BreakpointSelect)();
		$this->context['posting_fields']['hidden_breakpoints']['input']['tab']  = 'access_placement';
	}
}