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

namespace LightPortal\Database\Migrations\Upgraders;

use Bugo\Compat\Config;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Extra\Sql\Migrations\AbstractTableUpgrader;

if (! defined('SMF'))
	die('No direct access...');

abstract class TableUpgrader extends AbstractTableUpgrader
{
	protected function migrateRowsToTranslations(string $primary, string $type, ResultInterface $rows): void
	{
		$lang = Config::$language ?? 'english';

		foreach ($rows as $row) {
			$itemId      = $row[$primary];
			$title       = $row['title'] ?? '';
			$content     = $row['content'] ?? '';
			$description = $row['description'] ?? '';

			$select = $this->sql->select('lp_translations')
				->where([
					'item_id' => $itemId,
					'type'    => $type,
					'lang'    => $lang,
				]);

			$result = $this->sql->execute($select);

			if ($result->count() > 0) {
				$update = $this->sql->update('lp_translations')
					->set([
						'content'     => $content,
						'description' => $description,
					])
					->where([
						'item_id' => $itemId,
						'type'    => $type,
						'lang'    => $lang,
					]);

				$this->sql->execute($update);
			} else {
				$insert = $this->sql->insert('lp_translations')
					->values([
						'item_id'     => $itemId,
						'type'        => $type,
						'lang'        => $lang,
						'title'       => $title,
						'content'     => $content,
						'description' => $description,
					]);

				$this->sql->execute($insert);
			}
		}
	}
}
