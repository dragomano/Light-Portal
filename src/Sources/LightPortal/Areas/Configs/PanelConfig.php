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

namespace LightPortal\Areas\Configs;

use Bugo\Bricks\Settings\CallbackConfig;
use Bugo\Bricks\Settings\CheckConfig;
use Bugo\Bricks\Settings\ConfigBuilder;
use Bugo\Compat\Actions\Admin\ACP;
use Bugo\Compat\{Config, Lang, Theme};
use Bugo\Compat\{User, Utils};
use LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

final class PanelConfig extends AbstractConfig
{
	public function show(): void
	{
		Utils::$context['page_title'] = Utils::$context['settings_title'] = Lang::$txt['lp_panels'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_settings;sa=panels;save';

		Theme::addInlineCss('
		dl.settings {
			overflow: hidden;
		}');

		$this->addDefaultValues([
			'lp_header_panel_width' => 12,
			'lp_left_panel_width'   => json_encode(Setting::getLeftPanelWidth()),
			'lp_right_panel_width'  => json_encode(Setting::getRightPanelWidth()),
			'lp_footer_panel_width' => 12,
			'lp_left_panel_sticky'  => 1,
			'lp_right_panel_sticky' => 1,
		]);

		Utils::$context['lp_left_right_width_values'] = [2, 3, 4];
		Utils::$context['lp_header_footer_width_values'] = [6, 8, 10, 12];

		$vars = ConfigBuilder::make()->addVars([
			CheckConfig::make('lp_swap_header_footer'),

			CheckConfig::make('lp_swap_left_right'),

			CheckConfig::make('lp_swap_top_bottom'),

			/* @uses template_callback_panel_layout */
			CallbackConfig::make('panel_layout'),

			/* @uses template_callback_panel_direction */
			CallbackConfig::make('panel_direction'),
		]);

		$configVars = $vars->build();

		Utils::$context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			User::$me->checkSession();

			$this->post()->put('lp_left_panel_width', json_encode($this->request()->get('lp_left_panel_width')));
			$this->post()->put('lp_right_panel_width', json_encode($this->request()->get('lp_right_panel_width')));
			$this->post()->put('lp_panel_direction', json_encode($this->request()->get('lp_panel_direction')));

			$saveVars = $configVars;

			$saveVars[] = ['int', 'lp_header_panel_width'];
			$saveVars[] = ['text', 'lp_left_panel_width'];
			$saveVars[] = ['text', 'lp_right_panel_width'];
			$saveVars[] = ['int', 'lp_footer_panel_width'];
			$saveVars[] = ['check', 'lp_left_panel_sticky'];
			$saveVars[] = ['check', 'lp_right_panel_sticky'];
			$saveVars[] = ['text', 'lp_panel_direction'];

			ACP::saveDBSettings($saveVars);

			$this->session()->put('adm-save', true);

			$this->response()->redirect('action=admin;area=lp_settings;sa=panels');
		}

		ACP::prepareDBSettingContext($configVars);
	}
}
