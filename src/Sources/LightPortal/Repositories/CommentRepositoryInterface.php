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

interface CommentRepositoryInterface extends RepositoryInterface
{
	public function getAll(): array;

	public function getByPageId(int $id = 0): array;

	public function save(array $data): int;

	public function update(array $data): void;

	public function updateLastCommentId(int $item, int $pageId): void;
}
