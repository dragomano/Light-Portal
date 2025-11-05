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

namespace LightPortal\Repositories;

interface DataManagerInterface extends RepositoryInterface
{
	public function getData(int $item): array;

	public function setData(int $item = 0): void;

	public function toggleStatus(mixed $items = []): void;

	public function remove(mixed $items): void;
}
