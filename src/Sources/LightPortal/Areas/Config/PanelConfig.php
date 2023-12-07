<?php declare(strict_types=1);

/**
 * PanelConfig.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Areas\Config;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class PanelConfig
{
	use Helper;

	public function show(): void
	{
		$this->loadTemplate('LightPortal/ManagePanels');

		$this->addInlineCss('
		dl.settings {
			overflow: hidden;
		}');

		$this->context['page_title'] = $this->context['settings_title'] = $this->txt['lp_panels'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_settings;sa=panels;save';

		$this->addDefaultValues([
			'lp_header_panel_width' => 12,
			'lp_left_panel_width'   => json_encode($this->context['lp_left_panel_width']),
			'lp_right_panel_width'  => json_encode($this->context['lp_right_panel_width']),
			'lp_footer_panel_width' => 12,
			'lp_left_panel_sticky'  => 1,
			'lp_right_panel_sticky' => 1,
		]);

		$this->context['lp_left_right_width_values']    = [2, 3, 4];
		$this->context['lp_header_footer_width_values'] = [6, 8, 10, 12];

		$config_vars = [
			['check', 'lp_swap_header_footer'],
			['check', 'lp_swap_left_right'],
			['check', 'lp_swap_top_bottom'],
			['callback', 'panel_layout'],
			['callback', 'panel_direction']
		];

		$this->context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			$this->checkSession();

			$this->post()->put('lp_left_panel_width', json_encode($this->request('lp_left_panel_width')));
			$this->post()->put('lp_right_panel_width', json_encode($this->request('lp_right_panel_width')));
			$this->post()->put('lp_panel_direction', json_encode($this->request('lp_panel_direction')));

			$save_vars = $config_vars;

			$save_vars[] = ['int', 'lp_header_panel_width'];
			$save_vars[] = ['text', 'lp_left_panel_width'];
			$save_vars[] = ['text', 'lp_right_panel_width'];
			$save_vars[] = ['int', 'lp_footer_panel_width'];
			$save_vars[] = ['check', 'lp_left_panel_sticky'];
			$save_vars[] = ['check', 'lp_right_panel_sticky'];
			$save_vars[] = ['text', 'lp_panel_direction'];

			$this->saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);

			$this->redirect('action=admin;area=lp_settings;sa=panels');
		}

		$this->prepareDBSettingContext($config_vars);
	}
}
