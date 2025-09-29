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

namespace Bugo\LightPortal\Utils;

interface DatabaseInterface
{
	public function query(string $sql, array $params = []): mixed;

	public function fetchAssoc(mixed $result): array;

	public function fetchRow(mixed $result): array;

	public function freeResult(mixed $result): void;

	public function insert(
		string $method,
		string $table,
		array $columns,
		array $data,
		array $keys = [],
		int $returnmode = 0
	): mixed;

	public function transaction(string $type = 'begin'): void;

	public function numRows(mixed $result): int;

	public function fetchAll(mixed $result): array;

	public function getVersion(): string;
}
