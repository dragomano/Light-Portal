<?php declare(strict_types=1);

/**
 * @package Likely (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\Likely;

use Bugo\Compat\Lang;
use LightPortal\UI\Partials\AbstractSelect;

if (! defined('LP_NAME'))
	die('No direct access...');

final class ButtonSelect extends AbstractSelect
{
	public function getData(): array
	{
		$buttons = $this->params['data'] ?? [];

		$data = [];
		foreach ($buttons as $button) {
			$data[] = [
				'label' => $button,
				'value' => $button,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return array_merge(['showSelectedOptionsFirst' => true], [
			'id'       => 'buttons',
			'multiple' => true,
			'hint'     => Lang::$txt['lp_likely']['select_buttons'],
			'value'    => $this->normalizeValue($this->params['buttons']),
		]);
	}
}
