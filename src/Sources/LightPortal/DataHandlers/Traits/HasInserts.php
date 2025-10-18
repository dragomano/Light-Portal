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

namespace LightPortal\DataHandlers\Traits;

trait HasInserts
{
	protected function insertData(
		string $table,
		array $data,
		array $keys = [],
		bool $replace = false,
		int $chunkSize = 100
	): array
	{
		if ($data === []) {
			return [];
		}

		$data  = array_chunk($data, $chunkSize);
		$count = sizeof($data);

		$results = [];

		for ($i = 0; $i < $count; $i++) {
			$chunk = $data[$i];

			$sqlObject = $replace
				? $this->sql->replace($table)->setConflictKeys($keys)->batch($chunk)
				: $this->sql->insert($table)->batch($chunk);

			$result = $this->sql->execute($sqlObject);

			if ($result->getAffectedRows() === 0) {
				return [];
			}

			$generatedIds = [];
			if ($firstId = $result->getGeneratedValue()) {
				$affected = $result->getAffectedRows();
				for ($j = 0; $j < $affected; $j++) {
					$generatedIds[] = $firstId + $j;
				}
			}

			$results[] = $generatedIds;
		}

		return array_merge(...$results);
	}
}
