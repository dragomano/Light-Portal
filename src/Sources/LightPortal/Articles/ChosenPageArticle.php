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

namespace LightPortal\Articles;

use LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

final class ChosenPageArticle extends PageArticle
{
	private array $selectedPages = [];

	public function init(): void
	{
		parent::init();

		$this->selectedCategories = [];

		$this->selectedPages = Setting::get('lp_frontpage_pages', 'array', []);

		$this->wheres[] = ['p.page_id' => $this->selectedPages];

		$this->params['selected_pages'] = $this->selectedPages;
	}

	public function getData(int $start, int $limit, string $sortType = null): iterable
	{
		if (empty($this->selectedPages))
			return [];

		return parent::getData($start, $limit, $sortType);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedPages))
			return 0;

		return parent::getTotalCount();
	}
}
