<?php declare(strict_types=1);

/**
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 30.09.25
 */

namespace Bugo\LightPortal\Plugins\DummyArticleCards;

use Bugo\Compat\Config;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::FRONTPAGE)]
class DummyArticleCards extends Plugin
{
	private string $mode = 'dummy_articles_cards';

	#[HookAttribute(PortalHook::frontModes)]
	public function frontModes(Event $e): void
	{
		$e->args->modes[$this->mode] = DummyArticle::class;

		app()->add(DummyArticle::class);

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
	}

	#[HookAttribute(PortalHook::credits)]
	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title'   => 'DummyJSON',
			'link'    => 'https://github.com/Ovi/DummyJSON',
			'author'  => 'Muhammad Ovi (Owais)',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/Ovi/DummyJSON?tab=License-1-ov-file#readme'
			]
		];
	}
}
