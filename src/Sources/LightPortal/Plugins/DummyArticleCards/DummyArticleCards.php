<?php declare(strict_types=1);

/**
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\DummyArticleCards;

use Bugo\Compat\Config;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class DummyArticleCards extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'dummy_articles_cards';

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['check', 'use_lorem_ipsum'];
		$e->args->settings[$this->name][] = ['text', 'keywords', 'placeholder' => 'paris,girl'];
	}

	public function frontModes(Event $e): void
	{
		$e->args->modes[$this->mode] = DummyArticle::class;

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
	}

	public function credits(Event $e): void
	{
		if (empty($this->context['use_lorem_ipsum'])) {
			$e->args->links[] = [
				'title'   => 'DummyJSON',
				'link'    => 'https://github.com/Ovi/DummyJSON',
				'author'  => 'Muhammad Ovi (Owais)',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/Ovi/DummyJSON?tab=License-1-ov-file#readme'
				]
			];
		} else {
			$e->args->links[] = [
				'title'   => 'LoremFlickr',
				'link'    => 'https://github.com/MastaBaba/LoremFlickr',
				'author'  => 'Babak Fakhamzadeh',
				'license' => [
					'name' => 'the GPL-2.0 License',
					'link' => 'https://github.com/MastaBaba/LoremFlickr/blob/master/LICENSE'
				]
			];
		}
	}
}
