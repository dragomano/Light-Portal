<?php declare(strict_types=1);

/**
 * GalleryFrontPage.php
 *
 * @package GalleryFrontPage (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.03.22
 */

namespace Bugo\LightPortal\Addons\GalleryFrontPage;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class GalleryFrontPage extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'gallery_front_page_addon_mode';

	public function addSettings(array &$config_vars)
	{
		$config_vars['gallery_front_page'][] = ['multicheck', 'gallery_categories', $this->getGalleryCategories()];
	}

	public function frontModes(array &$modes)
	{
		$modes[$this->mode] = GalleryArticle::class;

		$this->modSettings['lp_frontpage_mode'] = $this->mode;
	}

	private function getGalleryCategories(): array
	{
		if (($categories = $this->cache()->get('smf_gallery_categories')) === null) {
			$result = $this->smcFunc['db_query']('', '
				SELECT id_cat, title
				FROM {db_prefix}gallery_cat
				WHERE redirect = {int:redirect}
				ORDER BY roworder',
				[
					'redirect' => 0,
				]
			);

			$categories = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($result))
				$categories[$row['id_cat']] = $row['title'];

			$this->smcFunc['db_free_result']($result);
			$this->context['lp_num_queries']++;

			$this->cache()->put('smf_gallery_categories', $categories);
		}

		return $categories;
	}
}
