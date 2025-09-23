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

use Bugo\Compat\Db;

if (! defined('SMF'))
	die('No direct access...');

class Database implements DatabaseInterface
{
	public function query(string $sql, array $params = []): bool|object
	{
		return Db::$db->query($sql, $params);
	}

	public function fetchAssoc(mixed $result): array
	{
		return Db::$db->fetch_assoc($result) ?: [];
	}

	public function fetchRow(mixed $result): array
	{
		return Db::$db->fetch_row($result) ?: [];
	}

	public function freeResult(mixed $result): void
	{
		Db::$db->free_result($result);
	}

	public function insert(
		string $method,
		string $table,
		array $columns,
		array $data,
		array $keys = [],
		int $returnmode = 0
	): int|array|null
	{
		return Db::$db->insert($method, $table, $columns, $data, $keys, $returnmode);
	}

	public function transaction(string $type = 'begin'): void
	{
		Db::$db->transaction($type);
	}

	public function numRows(mixed $result): int
	{
		return Db::$db->num_rows($result);
	}

	public function fetchAll(mixed $result): array
	{
		return Db::$db->fetch_all($result) ?: [];
	}

	public function getVersion(): string
	{
		return Db::$db->get_version();
	}
}
