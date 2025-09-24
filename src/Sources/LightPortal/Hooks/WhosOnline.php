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

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\Utils\Setting;

use function Bugo\LightPortal\app;

use const LP_ACTION;
use const LP_BASE_URL;
use const LP_PAGE_PARAM;
use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

class WhosOnline
{
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

			if (isset($actions['sa']) && $actions['sa'] === PortalSubAction::TAGS->name()) {
				$tags = app(TagList::class)();

				$result = isset($actions['id'])
					? Lang::getTxt('lp_who_viewing_the_tag', [
						PortalSubAction::TAGS->url() . ';id=' . $actions['id'],
						$tags[$actions['id']]
					])
					: sprintf(
						Lang::$txt['lp_who_viewing_tags'],
						PortalSubAction::TAGS->url()
					);
			}

			if (isset($actions['sa']) && $actions['sa'] === PortalSubAction::CATEGORIES->name()) {
				$categories = app(CategoryList::class)();

				$result = isset($actions['id'])
					? Lang::getTxt('lp_who_viewing_the_category', [
						PortalSubAction::CATEGORIES->url() . ';id=' . $actions['id'],
						$categories[$actions['id']]['name']
					])
					: sprintf(
						Lang::$txt['lp_who_viewing_categories'],
						PortalSubAction::CATEGORIES->url()
					);
			}
		}

		if ($actions['action'] === Action::FORUM->value) {
			$result = sprintf(
				Lang::$txt['lp_who_viewing_index'],
				Config::$scripturl . '?action=forum'
			);
		}

		return $result;
	}
}
