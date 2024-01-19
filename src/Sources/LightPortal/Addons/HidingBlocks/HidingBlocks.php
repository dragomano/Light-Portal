<?php

/**
 * HidingBlocks.php
 *
 * @package HidingBlocks (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\HidingBlocks;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Utils\{Lang, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

class HidingBlocks extends Plugin
{
	public string $type = 'block_options';

	private array $classes = ['xs', 'sm', 'md', 'lg', 'xl'];

	public function init(): void
	{
		foreach (Utils::$context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['hidden_breakpoints']))
				continue;

			$breakpoints = array_flip(explode(',', $block['parameters']['hidden_breakpoints']));
			foreach ($this->classes as $class) {
				if (array_key_exists($class, $breakpoints)) {
					if (empty(Utils::$context['lp_active_blocks'][$id]['custom_class']))
						Utils::$context['lp_active_blocks'][$id]['custom_class'] = '';

					Utils::$context['lp_active_blocks'][$id]['custom_class'] .= ' hidden-' . $class;
				}
			}
		}
	}

	public function prepareBlockParams(array &$params): void
	{
		$params['hidden_breakpoints'] = [];
	}

	public function validateBlockParams(array &$params): void
	{
		$params['hidden_breakpoints'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		CustomField::make('hidden_breakpoints', Lang::$txt['lp_hiding_blocks']['hidden_breakpoints'])
			->setTab('access_placement')
			->setValue(fn() => new BreakpointSelect);
	}
}
