<?php declare(strict_types=1);

/**
 * @package HidingBlocks (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\HidingBlocks;

use Bugo\Compat\Lang;
use LightPortal\UI\Partials\AbstractSelect;

if (! defined('LP_NAME'))
	die('No direct access...');

final class BreakpointSelect extends AbstractSelect
{
	public function getData(): array
	{
		$breakpoints = array_combine(
			$this->params['classes'],
			Lang::$txt['lp_hiding_blocks']['hidden_breakpoints_set']
		);

		$data = [];
		foreach ($breakpoints as $bp => $name) {
			$data[] = [
				'label' => $name,
				'value' => $bp,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'hidden_breakpoints',
			'multiple' => true,
			'hint'     => Lang::$txt['lp_hiding_blocks']['hidden_breakpoints_subtext'],
			'value'    => $this->normalizeValue($this->params['hidden_breakpoints']),
		];
	}
}
