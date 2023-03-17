<?php

/**
 * ExtendedBlockOptions.php
 *
 * @package ExtendedBlockOptions (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 3.02.23
 */

namespace Bugo\LightPortal\Addons\ExtendedBlockOptions;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class ExtendedBlockOptions extends Plugin
{
	public string $type = 'block_options';

	public function init()
	{
		if (empty($this->context['lp_active_blocks']))
			return;

		foreach ($this->context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['hide_header']))
				continue;

			$this->context['lp_active_blocks'][$id]['icon']  = '';
			$this->context['lp_active_blocks'][$id]['title'] = [];
		}
	}

	public function blockOptions(array &$options)
	{
		$options[$this->context['current_block']['type']]['parameters']['hide_header'] = false;
	}

	public function validateBlockData(array &$parameters)
	{
		$parameters['hide_header'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		$this->context['posting_fields']['hide_header']['label']['text'] = $this->txt['lp_extended_block_options']['hide_header'];
		$this->context['posting_fields']['hide_header']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'hide_header',
				'checked' => (bool) ($this->context['lp_block']['options']['parameters']['hide_header'] ?? false)
			]
		];
	}
}
