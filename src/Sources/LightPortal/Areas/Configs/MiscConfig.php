<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\Actions\Admin\ACP;
use Bugo\Compat\{Config, Db, Lang};
use Bugo\Compat\{User, Utils};
use Bugo\LightPortal\Tasks\Maintainer;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionTrait;

use const LP_ACTION;
use const LP_CACHE_TIME;
use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

final class MiscConfig extends AbstractConfig
{
	use RequestTrait;
	use SessionTrait;

	public function show(): void
	{
		Utils::$context['page_title'] = Lang::$txt['lp_misc'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_settings;sa=misc;save';

		$this->addDefaultValues([
			'lp_cache_interval' => LP_CACHE_TIME,
			'lp_portal_action'  => LP_ACTION,
			'lp_page_param'     => LP_PAGE_PARAM,
		]);

		$configVars = [
			['title', 'lp_debug_and_caching'],
			['check', 'lp_show_debug_info', 'help' => 'lp_show_debug_info_help'],
			['int', 'lp_cache_interval', 'postinput' => Lang::$txt['seconds']],
			['title', 'lp_compatibility_mode'],
			[
				'text',
				'lp_portal_action',
				'subtext' => Config::$scripturl . '?action=<strong>' . LP_ACTION . '</strong>'
			],
			[
				'text',
				'lp_page_param',
				'subtext' => Config::$scripturl . '?<strong>' . LP_PAGE_PARAM . '</strong>=page_slug'
			],
			['title', 'admin_maintenance'],
			['check', 'lp_weekly_cleaning']
		];

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

			Utils::redirectexit('action=admin;area=lp_settings;sa=misc');
		}

		ACP::prepareDBSettingContext($configVars);
	}
}
