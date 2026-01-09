<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
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

			$idColumn = $this->getIdColumn($table);
			$sqlObject->setReturning($idColumn);

			$result = $this->sql->execute($sqlObject);

			$results[] = $result->getGeneratedValues($idColumn);
		}

		return array_merge(...$results);
	}

	private function getIdColumn(string $table): string
	{
		return match ($table) {
			'lp_pages' => 'page_id',
			'lp_blocks' => 'block_id',
			'lp_categories' => 'category_id',
			'lp_tags', 'lp_page_tag' => 'tag_id',
			'members' => 'id_member',
			default => 'id',
		};
	}
}
