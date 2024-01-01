<?php declare(strict_types=1);

/**
 * ChosenPageArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Front;

if (! defined('SMF'))
	die('No direct access...');

final class ChosenPageArticle extends PageArticle
{
	private array $selected_pages = [];

	public function init(): void
	{
		parent::init();

		$this->selected_categories = [];

		$this->selected_pages = empty($this->modSettings['lp_frontpage_pages']) ? [] : explode(',', $this->modSettings['lp_frontpage_pages']);

		$this->wheres[] = 'AND p.page_id IN ({array_int:selected_pages})';

		$this->params['selected_pages'] = $this->selected_pages;
	}

	public function getData(int $start, int $limit): array
	{
		if (empty($this->selected_pages))
			return [];

		return parent::getData($start, $limit);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selected_pages))
			return 0;

		return parent::getTotalCount();
	}
}
