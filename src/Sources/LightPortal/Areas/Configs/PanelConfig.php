<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\{Actions\ACP, Config, Lang, Theme, User, Utils};
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionTrait;

use function json_encode;

if (! defined('SMF'))
	die('No direct access...');

final class PanelConfig extends AbstractConfig
{
	use RequestTrait;
	use SessionTrait;

	public function show(): void
	{
		Theme::loadTemplate('LightPortal/ManagePanels');

		Theme::addInlineCss('
		dl.settings {
			overflow: hidden;
		}');

		Utils::$context['page_title'] = Utils::$context['settings_title'] = Lang::$txt['lp_panels'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_settings;sa=panels;save';

		$this->addDefaultValues([
			'lp_header_panel_width' => 12,
			'lp_left_panel_width'   => json_encode(Utils::$context['lp_left_panel_width']),
			'lp_right_panel_width'  => json_encode(Utils::$context['lp_right_panel_width']),
			'lp_footer_panel_width' => 12,
			'lp_left_panel_sticky'  => 1,
			'lp_right_panel_sticky' => 1,
		]);

		Utils::$context['lp_left_right_width_values']    = [2, 3, 4];
		Utils::$context['lp_header_footer_width_values'] = [6, 8, 10, 12];

		$configVars = [
			['check', 'lp_swap_header_footer'],
			['check', 'lp_swap_left_right'],
			['check', 'lp_swap_top_bottom'],
			['callback', 'panel_layout'],
			['callback', 'panel_direction']
		];

		Utils::$context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			User::$me->checkSession();

			$this->post()->put('lp_left_panel_width', json_encode($this->request('lp_left_panel_width')));
			$this->post()->put('lp_right_panel_width', json_encode($this->request('lp_right_panel_width')));
			$this->post()->put('lp_panel_direction', json_encode($this->request('lp_panel_direction')));

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

			Utils::redirectexit('action=admin;area=lp_settings;sa=panels');
		}

		ACP::prepareDBSettingContext($configVars);
	}
}
