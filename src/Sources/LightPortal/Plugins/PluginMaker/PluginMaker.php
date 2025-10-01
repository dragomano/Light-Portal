<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 30.09.25
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Icon;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute]
class PluginMaker extends Plugin
{
	#[HookAttribute(PortalHook::init)]
	public function init(): void
	{
		Utils::$context['lp_plugin_option_types'] = array_combine(
			[
				'text', 'url', 'color', 'int', 'float', 'check',
				'multiselect', 'select', 'range', 'title', 'desc', 'callback'
			],
			$this->txt['option_type_set']
		);
	}

	#[HookAttribute(PortalHook::extendAdminAreas)]
	public function extendAdminAreas(Event $e): void
	{
		$areas = &$e->args->areas;

		$areas['lp_plugins']['subsections'] = array_merge(
			['main' => $areas['lp_plugins']['subsections']['main']],
			['add'  => [Icon::get('plus') . $this->txt['add']]],
			$areas['lp_plugins']['subsections']
		);
	}

	#[HookAttribute(PortalHook::extendPluginAreas)]
	public function extendPluginAreas(Event $e): void
	{
		$e->args->areas['add'] = [new Handler, 'add'];
	}

	#[HookAttribute(PortalHook::credits)]
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
