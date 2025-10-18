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

namespace LightPortal\Lists;

use Bugo\Compat\Lang;
use LightPortal\Repositories\CategoryRepositoryInterface;

if (! defined('SMF'))
	die('No direct access...');

readonly class CategoryList implements ListInterface
{
	public function __construct(private CategoryRepositoryInterface $repository) {}

	public function __invoke(): array
	{
		$items = $this->repository->getAll(
			0,
			$this->repository->getTotalCount(),
			'priority',
			'list'
		);

		$processedItems = [[
			'icon'  => '',
			'title' => Lang::$txt['lp_no_category'],
		]];

		foreach ($items as $id => $item) {
			$processedItems[$id] = [
				'id'          => $item['id'],
				'slug'        => $item['slug'],
				'icon'        => $item['icon'],
				'priority'    => $item['priority'],
				'title'       => $item['title'],
				'description' => $item['description'],
			];
		}

		return $processedItems;
	}
}
