<?php

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\{Lang, Utils};
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Icon;

use function array_combine;
use function array_merge;

if (! defined('LP_NAME'))
	die('No direct access...');

class PluginMaker extends Plugin
{
	public string $type ='other';

	public function init(): void
	{
		Utils::$context['lp_plugin_option_types'] = array_combine(
			[
				'text', 'url', 'color', 'int', 'float', 'check',
				'multiselect', 'select', 'range', 'title', 'desc', 'callback'
			],
			Lang::$txt['lp_plugin_maker']['option_type_set']
		);
	}

	public function updateAdminAreas(Event $e): void
	{
		$areas = &$e->args->areas;

		$areas['lp_plugins']['subsections'] = array_merge(
			['main' => $areas['lp_plugins']['subsections']['main']],
			['add'  => [Icon::get('plus') . Lang::$txt['lp_plugin_maker']['add']]],
			$areas['lp_plugins']['subsections']
		);
	}

	public function updatePluginAreas(Event $e): void
	{
		$e->args->areas['add'] = [new Handler, 'add'];
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Nette PHP Generator',
			'link' => 'https://github.com/nette/php-generator',
			'author' => 'David Grudl',
			'license' => [
				'name' => 'the New BSD License',
				'link' => 'https://github.com/nette/php-generator/blob/master/license.md'
			]
		];
	}
}
