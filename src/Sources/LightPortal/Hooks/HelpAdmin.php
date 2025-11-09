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

use Bugo\Compat\Lang;

if (! defined('SMF'))
	die('No direct access...');

class HelpAdmin
{
	public function __invoke(): void
	{
		Lang::$txt['lp_menu_separate_subsection_title_help'] = Lang::getTxt(
			'lp_menu_separate_subsection_title_help',
			[
				'<var>{lp_pages}</var>',
				'<var>$txt[`lp_pages`]</var>',
			]
		);
	}
}
