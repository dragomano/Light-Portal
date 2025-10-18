<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\AdsBlock;

use Bugo\Compat\Lang;
use LightPortal\UI\Partials\AbstractSelect;

if (! defined('LP_NAME'))
	die('No direct access...');

final class PlacementSelect extends AbstractSelect
{
	public function getData(): array
	{
		$data = [];
		foreach ($this->params['data'] as $position => $title) {
			$data[] = [
				'label' => $title,
				'value' => $position,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'ads_placement',
			'multiple' => true,
			'search'   => false,
			'hint'     => Lang::$txt['lp_block_placement_select'],
			'data'     => Placement::all(),
			'value'    => $this->normalizeValue($this->params['placements']),
		];
	}
}
