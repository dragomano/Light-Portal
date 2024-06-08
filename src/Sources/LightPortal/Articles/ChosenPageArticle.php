<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Articles;

use Bugo\Compat\Config;

use function explode;

if (! defined('SMF'))
	die('No direct access...');

final class ChosenPageArticle extends PageArticle
{
	private array $selectedPages = [];

	public function init(): void
	{
		parent::init();

		$this->selectedCategories = [];

		$this->selectedPages = empty(Config::$modSettings['lp_frontpage_pages'])
			? [] : explode(',', (string) Config::$modSettings['lp_frontpage_pages']);

		$this->wheres[] = 'AND p.page_id IN ({array_int:selected_pages})';

		$this->params['selected_pages'] = $this->selectedPages;
	}

	public function getData(int $start, int $limit): array
	{
		if (empty($this->selectedPages))
			return [];

		return parent::getData($start, $limit);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedPages))
			return 0;

		return parent::getTotalCount();
	}
}
