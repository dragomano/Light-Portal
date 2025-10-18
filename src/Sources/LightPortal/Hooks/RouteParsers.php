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

namespace LightPortal\Hooks;

use Bugo\Compat\QueryString;
use LightPortal\Enums\Action;
use LightPortal\Routes\Forum;
use LightPortal\Routes\Page;
use LightPortal\Routes\Portal;

use const LP_ACTION;

class RouteParsers
{
	public function __invoke(): void
	{
		QueryString::$route_parsers[Action::FORUM->value] = Forum::class;
		QueryString::$route_parsers[Action::PAGES->value] = Page::class;
		QueryString::$route_parsers[LP_ACTION] = Portal::class;
	}
}
