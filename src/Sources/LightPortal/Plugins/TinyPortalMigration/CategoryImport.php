<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\AbstractCustomCategoryImport;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ImportButtonsRow;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryImport extends AbstractCustomCategoryImport
{
	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tiny_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_categories;sa=import_from_tp';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tiny_portal_migration']['category_import_desc'],
		];

		$this->run();

		TablePresenter::show(
			PortalTableBuilder::make('tp_categories', Lang::$txt['lp_categories_import'])
				->withParams(
					50,
					defaultSortColumn: 'title'
				)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					TitleColumn::make()
						->setData('title', 'word_break')
						->setSort('title DESC', 'title'),
					CheckboxColumn::make(entity: 'categories'),
				])
				->addRow(ImportButtonsRow::make())
		);
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'tp_variables')))
			return [];

		$result = Db::$db->query('', '
			SELECT id, value1 AS title
			FROM {db_prefix}tp_variables
			WHERE type = {literal:category}
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
			$items[$row['id']] = [
				'id'    => $row['id'],
				'title' => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'tp_variables')))
			return 0;

		$result = Db::$db->query('', '
			SELECT COUNT(*)
			FROM {db_prefix}tp_variables
			WHERE type = {literal:category}',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query('', '
			SELECT id, value1 AS title
			FROM {db_prefix}tp_variables
			WHERE type = {literal:category}' . (empty($ids) ? '' : '
				AND id IN ({array_int:categories})'),
			[
				'categories' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id']] = [
				'title'       => $row['title'],
				'icon'        => '',
				'description' => '',
				'priority'    => 0,
				'status'      => 1,
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
