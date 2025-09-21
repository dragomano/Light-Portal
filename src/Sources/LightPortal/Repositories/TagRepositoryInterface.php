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

namespace Bugo\LightPortal\Repositories;

if (! defined('SMF'))
	die('No direct access...');

interface TagRepositoryInterface extends RepositoryInterface
{
	public function getAll(int $start, int $limit, string $sort): array;

	public function getTotalCount(): int;

	public function getData(int $item): array;

	public function setData(int $item = 0): void;

	public function remove(array $items): void;
}
