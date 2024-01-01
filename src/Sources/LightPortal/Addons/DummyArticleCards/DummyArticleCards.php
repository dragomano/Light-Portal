<?php

/**
 * DummyArticleCards.php
 *
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.23
 */

namespace Bugo\LightPortal\Addons\DummyArticleCards;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class DummyArticleCards extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'dummy_articles_cards';

	public function addSettings(array &$config_vars): void
	{
		$config_vars['dummy_article_cards'][] = ['check', 'use_lorem_ipsum'];
	}

	public function frontModes(array &$modes): void
	{
		$modes[$this->mode] = DummyArticle::class;

		$this->modSettings['lp_frontpage_mode'] = $this->mode;
	}

	public function credits(array &$links): void
	{
		if (empty($this->context['lp_dummy_article_cards_plugin']['use_lorem_ipsum'])) {
			$links[] = [
				'title'   => 'DummyJSON',
				'link'    => 'https://github.com/Ovi/DummyJSON',
				'author'  => 'Muhammad Ovi (Owais)',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/Ovi/DummyJSON?tab=License-1-ov-file#readme'
				]
			];
		} else {
			$links[] = [
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
