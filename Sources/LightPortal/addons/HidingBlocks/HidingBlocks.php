<?php

/**
 * HidingBlocks.php
 *
 * @package HidingBlocks (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\HidingBlocks;

use Bugo\LightPortal\Addons\Plugin;

class HidingBlocks extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'block_options';

	/**
	 * @var array
	 */
	public $classes = ['xs', 'sm', 'md', 'lg', 'xl'];

	/**
	 * Fill additional block classes
	 *
	 * Заполняем дополнительные классы блока
	 *
	 * @return void
	 */
	public function init()
	{
		global $context;

		if (empty($context['lp_active_blocks']))
			return;

		foreach ($context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['hidden_breakpoints']))
				continue;

			$breakpoints = array_flip(explode(',', $block['parameters']['hidden_breakpoints']));
			foreach ($this->classes as $class) {
				if (array_key_exists($class, $breakpoints)) {
					if (empty($context['lp_active_blocks'][$id]['custom_class']))
						$context['lp_active_blocks'][$id]['custom_class'] = '';

					$context['lp_active_blocks'][$id]['custom_class'] .= ' hidden-' . $class;
				}
			}
		}
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		global $context;

		$options[$context['current_block']['type']]['parameters']['hidden_breakpoints'] = [];
	}

	/**
	 * @param array $parameters
	 * @return void
	 */
	public function validateBlockData(array &$parameters)
	{
		$parameters['hidden_breakpoints'] = array(
			'name'   => 'hidden_breakpoints',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		// Prepare the breakpoints list
		$current_breakpoints = $context['lp_block']['options']['parameters']['hidden_breakpoints'] ?? [];
		$current_breakpoints = is_array($current_breakpoints) ? $current_breakpoints : explode(',', $current_breakpoints);

		$breakpoints = array_combine(array('xs', 'sm', 'md', 'lg', 'xl'), $txt['lp_hiding_blocks']['hidden_breakpoints_set']);

		$data = [];
		foreach ($breakpoints as $bp => $name) {
			$data[] = "\t\t\t\t" . '{text: "' . $name . '", value: "' . $bp . '", selected: ' . (in_array($bp, $current_breakpoints) ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#hidden_breakpoints",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			placeholder: "' . $txt['lp_hiding_blocks']['hidden_breakpoints_subtext'] . '",
			searchHighlight: true,
			closeOnSelect: false
		});', true);

		$context['posting_fields']['hidden_breakpoints']['label']['text'] = $txt['lp_hiding_blocks']['hidden_breakpoints'];
		$context['posting_fields']['hidden_breakpoints']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'hidden_breakpoints',
				'name'     => 'hidden_breakpoints[]',
				'multiple' => true
			),
			'options' => array(),
			'tab' => 'access_placement'
		);
	}
}
