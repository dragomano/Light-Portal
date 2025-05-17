<?php declare(strict_types=1);

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.04.25
 */

namespace Bugo\LightPortal\Plugins\EhPortalMigration;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\AbstractCustomPageImport;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ImportButtonsRow;
use Bugo\LightPortal\UI\Tables\PageSlugColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\DateTime;

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractCustomPageImport
{
	public function main(): void
	{
		User::$me->isAllowedTo('admin_forum');

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_eh_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=import_from_ep';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_eh_portal_migration']['page_import_desc'],
		];

		$this->run();

		app(TablePresenter::class)->show(
			PortalTableBuilder::make('eh_pages', Lang::$txt['lp_pages_import'])
				->withParams(50, defaultSortColumn: 'id')
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					IdColumn::make()
						->setSort('id_page'),
					PageSlugColumn::make()
						->setSort('namespace DESC', 'namespace'),
					TitleColumn::make()
						->setData('title', 'word_break'),
					CheckboxColumn::make(entity: 'pages'),
				])
				->addRow(ImportButtonsRow::make())
		);
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_page'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_pages')))
			return [];

		$result = Db::$db->query('', '
			SELECT id_page, namespace, title, body, type, permission_set, groups_allowed, views, status
			FROM {db_prefix}sp_pages
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
			$items[$row['id_page']] = [
				'id'         => $row['id_page'],
				'slug'       => $row['namespace'],
				'type'       => $row['type'],
				'status'     => $row['status'],
				'num_views'  => $row['views'],
				'author_id'  => User::$me->id,
				'created_at' => DateTime::relative(time()),
				'title'      => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_pages')))
			return 0;

		$result = Db::$db->query('', /** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}sp_pages',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT id_page, namespace, title, body, type, permission_set, groups_allowed, views, status
			FROM {db_prefix}sp_pages' . (empty($ids) ? '' : '
			WHERE id_page IN ({array_int:pages})'),
			[
				'pages' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id_page']] = [
				'page_id'      => $row['id_page'],
				'author_id'    => User::$me->id,
				'slug'         => $row['namespace'] ?: ('page_' . $row['id_page']),
				'description'  => '',
				'content'      => $row['body'],
				'type'         => $row['type'],
				'permissions'  => $this->getPagePermission($row),
				'status'       => $row['status'],
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => time(),
				'updated_at'   => 0,
				'title'        => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	private function getPagePermission(array $row): int
	{
		$perm = (int) $row['permission_set'];

		if (empty($row['permission_set'])) {
			$perm = match ((int) $row['groups_allowed']) {
				-1      => Permission::GUEST->value,
				0       => Permission::MEMBER->value,
				1       => Permission::ADMIN->value,
				default => Permission::ALL->value,
			};
		}

		return $perm;
	}
}
