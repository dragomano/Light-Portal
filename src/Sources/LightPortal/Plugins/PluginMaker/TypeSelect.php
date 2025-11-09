<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.10.25
 */

namespace LightPortal\Plugins\PluginMaker;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\Partials\AbstractSelect;

if (! defined('LP_NAME'))
	die('No direct access...');

final class TypeSelect extends AbstractSelect
{
	public function getData(): array
	{
		$types = Utils::$context['lp_plugin_types'];

		$data = [];
		foreach ($types as $key => $value) {
			$data[] = [
				'label' => $value,
				'value' => $key,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'type',
			'multiple' => true,
			'hint'     => Lang::$txt['lp_plugin_maker']['type_select'],
			'value'    => Utils::$context['lp_plugin']['type'],
		];
	}
}
