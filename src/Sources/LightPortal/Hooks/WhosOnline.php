<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\{Config, Lang};
use Bugo\LightPortal\Utils\EntityDataTrait;
use Bugo\LightPortal\Utils\Setting;

use function sprintf;

use const LP_ACTION;
use const LP_BASE_URL;
use const LP_PAGE_PARAM;
use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

class WhosOnline
{
	use EntityDataTrait;

	public function __invoke(array $actions): string
	{
		$result = '';
		if (empty($actions['action']) && empty($actions['board'])) {
			$result = sprintf(Lang::$txt['lp_who_viewing_frontpage'], Config::$scripturl);

			if (Setting::isStandaloneMode()) {
				$result = sprintf(
					Lang::$txt['lp_who_viewing_frontpage'],
					Config::$modSettings['lp_standalone_url']
				);
			}
		}

		if (isset($actions[LP_PAGE_PARAM])) {
			$result = sprintf(
				Lang::$txt['lp_who_viewing_page'],
				LP_PAGE_URL . $actions[LP_PAGE_PARAM]
			);
		}

		if (empty($actions['action']))
			return $result;

		if ($actions['action'] === LP_ACTION) {
			$result = sprintf(Lang::$txt['lp_who_viewing_frontpage'], LP_BASE_URL);

			if (isset($actions['sa']) && $actions['sa'] === 'tags') {
				$tags = $this->getEntityData('tag');

				$result = isset($actions['id'])
					? Lang::getTxt('lp_who_viewing_the_tag', [
						LP_BASE_URL . ';sa=tags;id=' . $actions['id'],
						$tags[$actions['id']]
					])
					: sprintf(
						Lang::$txt['lp_who_viewing_tags'],
						LP_BASE_URL . ';sa=tags'
					);
			}

			if (isset($actions['sa']) && $actions['sa'] === 'categories') {
				$categories = $this->getEntityData('category');

				$result = isset($actions['id'])
					? Lang::getTxt('lp_who_viewing_the_category', [
						LP_BASE_URL . ';sa=categories;id=' . $actions['id'],
						$categories[$actions['id']]['name']
					])
					: sprintf(
						Lang::$txt['lp_who_viewing_categories'],
						LP_BASE_URL . ';sa=categories'
					);
			}
		}

		if ($actions['action'] === 'forum') {
			$result = sprintf(
				Lang::$txt['lp_who_viewing_index'],
				Config::$scripturl . '?action=forum'
			);
		}

		return $result;
	}
}
