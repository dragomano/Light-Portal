<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Bricks\Settings\CallbackConfig;
use Bugo\Bricks\Settings\CheckConfig;
use Bugo\Bricks\Settings\ConfigBuilder;
use Bugo\Bricks\Settings\DividerConfig;
use Bugo\Bricks\Settings\IntConfig;
use Bugo\Bricks\Settings\SelectConfig;
use Bugo\Bricks\Settings\TextConfig;
use Bugo\Bricks\Settings\TitleConfig;
use Bugo\Compat\{Config, Lang, Theme};
use Bugo\Compat\{User, Utils};
use Bugo\Compat\Actions\Admin\ACP;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasSession;

if (! defined('SMF'))
	die('No direct access...');

final class ExtraConfig extends AbstractConfig
{
	use HasSession;

	public function show(): void
	{
		Utils::$context['page_title'] = Utils::$context['settings_title'] = Lang::$txt['lp_extra'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_settings;sa=extra;save';

		Lang::$txt['lp_comment_block_set']['none']    = Lang::$txt['lp_comment_block_set'][0];
		Lang::$txt['lp_comment_block_set']['default'] = Lang::$txt['lp_comment_block_set'][1];

		unset(Lang::$txt['lp_comment_block_set'][0], Lang::$txt['lp_comment_block_set'][1]);
		asort(Lang::$txt['lp_comment_block_set']);

		Lang::$txt['lp_fa_source_title'] .= ' ' . Str::html('img', [
			'class' => 'floatright',
			'src'   => 'https://data.jsdelivr.com/v1/package/npm/@fortawesome/fontawesome-free/badge?style=rounded',
			'alt'   => '',
		]);

		$this->addDefaultValues([
			'lp_num_comments_per_page' => 10,
			'lp_page_maximum_tags'     => 10,
		]);

		$vars = ConfigBuilder::make()->addVars([
			CheckConfig::make('lp_show_tags_on_page'),
			SelectConfig::make('lp_page_og_image')
				->setOptions(Lang::$txt['lp_page_og_image_set']),
			CheckConfig::make('lp_show_prev_next_links'),
			CheckConfig::make('lp_show_related_pages'),
			DividerConfig::make(),
			CallbackConfig::make('comment_settings_before'),
			SelectConfig::make('lp_comment_block')
				->setOptions(Lang::$txt['lp_comment_block_set'])
				->setJavaScript('@change="comment_block = $event.target.value"'),
			IntConfig::make('lp_time_to_change_comments')
				->setPostInput(Lang::$txt['manageposts_minutes'])
				->setJavaScript(':disabled="comment_block !== \'default\'"'),
			IntConfig::make('lp_num_comments_per_page')
				->setJavaScript(':disabled="comment_block !== \'default\'"'),
			SelectConfig::make('lp_comment_sorting')
				->setOptions([Lang::$txt['lp_sort_by_created'], Lang::$txt['lp_sort_by_created_desc']])
				->setJavaScript(':disabled="comment_block !== \'default\'"'),
			CallbackConfig::make('comment_settings_after'),
			DividerConfig::make(),
			IntConfig::make('lp_page_maximum_tags')
				->setMin(1),
			SelectConfig::make('lp_permissions_default')
				->setOptions(Lang::$txt['lp_permissions']),
			CheckConfig::make('lp_hide_blocks_in_acp'),
			TitleConfig::make('mobile_user_menu'),
			CallbackConfig::make('menu_settings_before'),
			CheckConfig::make('lp_menu_separate_subsection')
				->setHelp('lp_menu_separate_subsection_help')
				->setJavaScript('@change="separate_subsection = ! separate_subsection"'),
			TextConfig::make('lp_menu_separate_subsection_title')
				->setHelp('lp_menu_separate_subsection_title_help')
				->setJavaScript(':disabled="separate_subsection === false"')
				->setSize('75" placeholder="{lp_pages}'),
			TextConfig::make('lp_menu_separate_subsection_href')
				->setJavaScript(':disabled="separate_subsection === false"')
				->setSize('75" placeholder="' . Config::$scripturl),
			CallbackConfig::make('menu_settings_after'),
			TitleConfig::make('lp_fa_source_title'),
			SelectConfig::make('lp_fa_source')
				->setOptions([
					'none'      => Lang::$txt['no'],
					'css_cdn'   => Lang::$txt['lp_fa_source_css_cdn'],
					'css_local' => Lang::$txt['lp_fa_source_css_local'],
					'custom'    => Lang::$txt['lp_fa_custom'],
					'kit'       => Lang::$txt['lp_fa_kit'],
				])
				->setOnChange('document.getElementById(\'lp_fa_custom\').disabled = this.value !== \'custom\';
					document.getElementById(\'lp_fa_kit\').disabled = this.value !== \'kit\';'),
			TextConfig::make('lp_fa_custom')
				->setDisabled(Setting::get('lp_fa_source', 'string', '') !== 'custom')
				->setSize('75'),
			TextConfig::make('lp_fa_kit')
				->setDisabled(isset(Config::$modSettings['lp_fa_kit']) && Config::$modSettings['lp_fa_source'] !== 'kit')
				->setSize('75" placeholder="https://kit.fontawesome.com/xxx.js'),
		]);

		$configVars = $vars->build();

		Theme::loadTemplate('LightPortal/ManageSettings');

		// Save
		if ($this->request()->has('save')) {
			User::$me->checkSession();

			if ($this->request()->isNotEmpty('lp_menu_separate_subsection_href')) {
				$this->post()->put(
					'lp_menu_separate_subsection_href',
					VarType::URL->filter($this->request()->get('lp_menu_separate_subsection_href'))
				);
			}

			if ($this->request()->isNotEmpty('lp_fa_custom')) {
				$this->post()->put('lp_fa_custom', VarType::URL->filter($this->request()->get('lp_fa_custom')));
			}

			if ($this->request()->isNotEmpty('lp_fa_kit')) {
				$this->post()->put('lp_fa_kit', VarType::URL->filter($this->request()->get('lp_fa_kit')));
			}

			$saveVars = $configVars;
			ACP::saveDBSettings($saveVars);

			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			$this->response()->redirect('action=admin;area=lp_settings;sa=extra');
		}

		ACP::prepareDBSettingContext($configVars);
	}
}
