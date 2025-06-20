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

use Bugo\Bricks\Settings\CheckConfig;
use Bugo\Bricks\Settings\ConfigBuilder;
use Bugo\Bricks\Settings\IntConfig;
use Bugo\Bricks\Settings\TextConfig;
use Bugo\Bricks\Settings\TitleConfig;
use Bugo\Compat\{Config, Db, Lang};
use Bugo\Compat\{User, Utils};
use Bugo\Compat\Actions\Admin\ACP;
use Bugo\LightPortal\Tasks\Maintainer;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasSession;

use const LP_ACTION;
use const LP_CACHE_TIME;
use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

final class MiscConfig extends AbstractConfig
{
	use HasRequest;
	use HasSession;

	public function show(): void
	{
		Utils::$context['page_title'] = Lang::$txt['lp_misc'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_settings;sa=misc;save';

		$this->addDefaultValues([
			'lp_cache_interval' => LP_CACHE_TIME,
			'lp_portal_action'  => LP_ACTION,
			'lp_page_param'     => LP_PAGE_PARAM,
		]);

		$vars = ConfigBuilder::make()->addVars([
			TitleConfig::make('lp_debug_and_caching'),
			CheckConfig::make('lp_show_debug_info')
				->setHelp('lp_show_debug_info_help'),
			IntConfig::make('lp_cache_interval')
				->setPostInput(Lang::$txt['seconds']),
			TitleConfig::make('lp_compatibility_mode'),
			TextConfig::make('lp_portal_action')
				->setSubText(Config::$scripturl . '?action=<strong>' . LP_ACTION . '</strong>'),
			TextConfig::make('lp_page_param')
				->setSubText(Config::$scripturl . '?<strong>' . LP_PAGE_PARAM . '</strong>=page_slug'),
			TitleConfig::make('admin_maintenance'),
			CheckConfig::make('lp_weekly_cleaning'),
		]);

		$configVars = $vars->build();

		Utils::$context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			User::$me->checkSession();

			Db::$db->query('', '
				DELETE FROM {db_prefix}background_tasks
				WHERE task_file LIKE {string:task_file}',
				[
					'task_file' => '%$sourcedir/LightPortal%'
				]
			);

			if ($this->request()->has('lp_weekly_cleaning')) {
				Db::$db->insert('insert',
					'{db_prefix}background_tasks',
					['task_file' => 'string-255', 'task_class' => 'string-255', 'task_data' => 'string'],
					['$sourcedir/LightPortal/Tasks/Maintainer.php', '\\' . Maintainer::class, ''],
					['id_task']
				);
			}

			$saveVars = $configVars;
			ACP::saveDBSettings($saveVars);

			$this->session()->put('adm-save', true);

			$this->response()->redirect('action=admin;area=lp_settings;sa=misc');
		}

		ACP::prepareDBSettingContext($configVars);
	}
}
