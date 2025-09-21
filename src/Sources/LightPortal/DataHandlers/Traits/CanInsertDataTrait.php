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

namespace Bugo\LightPortal\DataHandlers\Traits;

trait CanInsertDataTrait
{
	protected function insertData(
		string $table,
		string $method,
		array $data,
		array $columns,
		array $keys,
		int $chunkSize = 100
	): array
	{
		if ($data === [] || $columns === [] || $keys === [])
			return [];

		$data  = array_chunk($data, $chunkSize);
		$count = sizeof($data);

		$results = [];

		for ($i = 0; $i < $count; $i++) {
			$insertResult = $this->db->insert(
				$method,
				"{db_prefix}$table",
				$columns,
				$data[$i],
				$keys,
				2
			);

			// If insert fails, return false immediately
			if (! $insertResult) {
				return [];
			}

			$results[] = $insertResult;
		}

		return array_merge(...$results);
	}
}
