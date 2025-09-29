<?php declare(strict_types=1);

/**
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.09.25
 */

namespace Bugo\LightPortal\Plugins\DummyArticleCards;

use Bugo\Compat\Config;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

class DummyArticleCards extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'dummy_articles_cards';

	public function frontModes(Event $e): void
	{
		$e->args->modes[$this->mode] = DummyArticle::class;

		app()->add(DummyArticle::class);

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
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
