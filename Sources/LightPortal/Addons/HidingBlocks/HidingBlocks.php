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
 * @version 12.05.22
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
		if (empty($this->context['lp_active_blocks']))
			return;

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
		$this->context['posting_fields']['hidden_breakpoints']['label']['html'] = '<label for="hidden_breakpoints">' . $this->txt['lp_hiding_blocks']['hidden_breakpoints'] . '</label>';
		$this->context['posting_fields']['hidden_breakpoints']['input']['html'] = '<div id="hidden_breakpoints" name="hidden_breakpoints"></div>';
		$this->context['posting_fields']['hidden_breakpoints']['input']['tab']  = 'access_placement';

		$current_breakpoints = $this->context['lp_block']['options']['parameters']['hidden_breakpoints'] ?? [];
		$current_breakpoints = is_array($current_breakpoints) ? $current_breakpoints : explode(',', $current_breakpoints);

		$breakpoints = array_combine(['xs', 'sm', 'md', 'lg', 'xl'], $this->txt['lp_hiding_blocks']['hidden_breakpoints_set']);

		$data = $items = [];

		foreach ($breakpoints as $bp => $name) {
			$data[] = '{label: "' . $name . '", value: "' . $bp . '"}';

			if (in_array($bp, $current_breakpoints)) {
				$items[] = JavaScriptEscape($bp);
			}
		}

		$this->addInlineJavaScript('
		VirtualSelect.init({
			ele: "#hidden_breakpoints",' . ($this->context['right_to_left'] ? '
			textDirection: "rtl",' : '') . '
			dropboxWrapper: "body",
			maxWidth: "100%",
			showValueAsTags: true,
			placeholder: "' . $this->txt['lp_hiding_blocks']['hidden_breakpoints_subtext'] . '",
			clearButtonText: "' . $this->txt['remove'] . '",
			selectAllText: "' . $this->txt['check_all'] . '",
			multiple: true,
			search: false,
			options: [' . implode(',', $data) . '],
			selectedValue: [' . implode(',', $items) . ']
		});', true);
	}
}
