<?php declare(strict_types=1);

/**
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 04.11.25
 */

namespace LightPortal\Plugins\DummyArticleCards;

use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::FRONTPAGE)]
class DummyArticleCards extends Plugin
{
	private string $mode = 'dummy_articles_cards';

	public function frontModes(Event $e): void
	{
		$e->args->modes[$this->mode] = DummyArticle::class;

		app()->add(DummyArticle::class);

		$e->args->currentMode = $this->mode;
	}

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
