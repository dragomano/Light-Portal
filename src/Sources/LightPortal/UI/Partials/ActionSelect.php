<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

final class ActionSelect extends AbstractSelect
{
	public function getData(): array
	{
		$actions = Setting::getDisabledActions();

		$data = [];
		foreach ($actions as $value) {
			$data[] = [
				'label' => $value,
				'value' => $value,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'lp_disabled_actions',
			'multiple' => true,
			'wide'     => true,
			'allowNew' => true,
			'hint'     => Lang::$txt['lp_example'] . 'mlist, calendar',
			'empty'    => Lang::$txt['no'],
			'value'    => array_column($this->getData(), 'value'),
		];
	}
}
