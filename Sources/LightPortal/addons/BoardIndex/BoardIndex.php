<?php

/**
 * BoardIndex.php
 *
 * @package BoardIndex (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\BoardIndex;

use Bugo\LightPortal\Addons\Plugin;

class BoardIndex extends Plugin
{
	public string $type = 'other';

	public function init()
	{
		add_integration_function('integrate_mark_read_button', __CLASS__ . '::toggleRobotNoIndex#', false, __FILE__);
	}

	public function addSettings(array &$config_vars)
	{
		global $txt, $scripturl, $modSettings;

		$txt['lp_board_index']['description'] = sprintf($txt['lp_board_index']['description'], $scripturl . '?action=forum');

		if (! isset($modSettings['lp_board_index_addon_allow_for_spiders']))
			updateSettings(['lp_board_index_addon_allow_for_spiders' => false]);

		$config_vars['board_index'][] = array('check', 'allow_for_spiders');
	}

	public function toggleRobotNoIndex()
	{
		global $modSettings, $context;

		if (! empty($modSettings['lp_frontpage_mode']))
			$context['robot_no_index'] = empty($modSettings['lp_board_index_addon_allow_for_spiders']);
	}
}
