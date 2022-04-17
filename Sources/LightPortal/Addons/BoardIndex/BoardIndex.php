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
 * @version 16.04.22
 */

namespace Bugo\LightPortal\Addons\BoardIndex;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardIndex extends Plugin
{
	public string $type = 'seo';

	public function init()
	{
		add_integration_function('integrate_mark_read_button', __CLASS__ . '::toggleRobotNoIndex#', false, __FILE__);
	}

	public function addSettings(array &$config_vars)
	{
		$this->txt['lp_board_index']['description'] = sprintf($this->txt['lp_board_index']['description'], $this->scripturl . '?action=forum');

		$config_vars['board_index'][] = ['check', 'allow_for_spiders'];
	}

	/**
	 * @hook integrate_mark_read_button
	 */
	public function toggleRobotNoIndex()
	{
		$this->context['robot_no_index'] = ! empty($this->modSettings['lp_frontpage_mode']) && empty($this->context['lp_board_index_plugin']['allow_for_spiders']);
	}
}
