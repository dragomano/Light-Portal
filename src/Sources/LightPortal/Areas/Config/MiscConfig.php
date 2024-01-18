<?php declare(strict_types=1);

/**
 * MiscConfigArea
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Config;

use Bugo\LightPortal\Tasks\Maintainer;
use Bugo\LightPortal\Utils\{Config, Lang, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class MiscConfig extends AbstractConfig
{
	public function show(): void
	{
		Utils::$context['page_title'] = Lang::$txt['lp_misc'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_settings;sa=misc;save';

		$this->addDefaultValues([
			'lp_cache_update_interval' => LP_CACHE_TIME,
			'lp_portal_action'         => LP_ACTION,
			'lp_page_param'            => LP_PAGE_PARAM,
		]);

		$config_vars = [
			['title', 'lp_debug_and_caching'],
			['check', 'lp_show_debug_info', 'help' => 'lp_show_debug_info_help'],
			['int', 'lp_cache_update_interval', 'postinput' => Lang::$txt['seconds']],
			['title', 'lp_compatibility_mode'],
			['text', 'lp_portal_action', 'subtext' => Config::$scripturl . '?action=<strong>' . LP_ACTION . '</strong>'],
			['text', 'lp_page_param', 'subtext' => Config::$scripturl . '?<strong>' . LP_PAGE_PARAM . '</strong>=somealias'],
			['title', 'admin_maintenance'],
			['check', 'lp_weekly_cleaning']
		];

		Utils::$context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			User::$me->checkSession();

			Utils::$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}background_tasks
				WHERE task_file LIKE {string:task_file}',
				[
					'task_file' => '%$sourcedir/LightPortal%'
				]
			);

			if ($this->request()->has('lp_weekly_cleaning')) {
				Utils::$smcFunc['db_insert']('insert',
					'{db_prefix}background_tasks',
					['task_file' => 'string-255', 'task_class' => 'string-255', 'task_data' => 'string'],
					['$sourcedir/LightPortal/Tasks/Maintainer.php', '\\' . Maintainer::class, ''],
					['id_task']
				);
			}

			$save_vars = $config_vars;

			$this->saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);

			Utils::redirectexit('action=admin;area=lp_settings;sa=misc');
		}

		$this->prepareDBSettingContext($config_vars);
	}
}
