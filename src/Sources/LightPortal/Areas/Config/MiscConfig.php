<?php declare(strict_types=1);

/**
 * MiscConfigArea
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Config;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Tasks\Maintainer;

if (! defined('SMF'))
	die('No direct access...');

final class MiscConfig
{
	use Helper;

	public function show(): void
	{
		$this->context['page_title'] = $this->txt['lp_misc'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_settings;sa=misc;save';

		$this->addDefaultValues([
			'lp_cache_update_interval' => LP_CACHE_TIME,
			'lp_portal_action'         => LP_ACTION,
			'lp_page_param'            => LP_PAGE_PARAM,
		]);

		$config_vars = [
			['title', 'lp_debug_and_caching'],
			['check', 'lp_show_debug_info', 'help' => 'lp_show_debug_info_help'],
			['int', 'lp_cache_update_interval', 'postinput' => $this->txt['seconds']],
			['title', 'lp_compatibility_mode'],
			['text', 'lp_portal_action', 'subtext' => $this->scripturl . '?action=<strong>' . LP_ACTION . '</strong>'],
			['text', 'lp_page_param', 'subtext' => $this->scripturl . '?<strong>' . LP_PAGE_PARAM . '</strong>=somealias'],
			['title', 'admin_maintenance'],
			['check', 'lp_weekly_cleaning']
		];

		$this->context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			$this->checkSession();

			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}background_tasks
				WHERE task_file LIKE {string:task_file}',
				[
					'task_file' => '%$sourcedir/LightPortal%'
				]
			);

			if ($this->request()->has('lp_weekly_cleaning')) {
				$this->smcFunc['db_insert']('insert',
					'{db_prefix}background_tasks',
					['task_file' => 'string-255', 'task_class' => 'string-255', 'task_data' => 'string'],
					['$sourcedir/LightPortal/Tasks/Maintainer.php', '\\' . Maintainer::class, ''],
					['id_task']
				);
			}

			$save_vars = $config_vars;

			$this->saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);

			$this->redirect('action=admin;area=lp_settings;sa=misc');
		}

		$this->prepareDBSettingContext($config_vars);
	}
}
