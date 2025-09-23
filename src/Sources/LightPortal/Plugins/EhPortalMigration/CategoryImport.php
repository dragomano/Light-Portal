<?php declare(strict_types=1);

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.09.25
 */

namespace Bugo\LightPortal\Plugins\EhPortalMigration;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\LightPortal\DataHandlers\Imports\Custom\AbstractCustomCategoryImport;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryImport extends AbstractCustomCategoryImport
{
	protected string $langKey = 'lp_eh_portal_migration';

	protected string $formAction = 'import_from_ep';

	protected string $uiTableId = 'ep_categories';

	protected function defineUiColumns(): array
	{
		return [
			TitleColumn::make()
				->setData('title', 'word_break'),
			Column::make('status', Lang::$txt['status'])
				->setData('status', 'centertext')
				->setSort('status DESC', 'status'),
			CheckboxColumn::make(entity: 'categories'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_category'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_categories')))
			return [];

		$result = Db::$db->query('
			SELECT id_category, name AS title, publish AS status
			FROM {db_prefix}sp_categories
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'sort'  => $sort,
				'start' => $start,
				'limit' => $limit,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id_category']] = [
				'id'     => $row['id_category'],
				'title'  => $row['title'],
				'status' => $row['status'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_categories')))
			return 0;

		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}sp_categories',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT id_category, name AS title, publish AS status
			FROM {db_prefix}sp_categories' . (empty($ids) ? '' : '
			WHERE id_category IN ({array_int:categories})'),
			[
				'categories' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id_category']] = [
				'title'       => $row['title'],
				'parent_id'   => 0,
				'slug'        => '',
				'icon'        => '',
				'description' => '',
				'priority'    => 0,
				'status'      => (int) $row['status'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
