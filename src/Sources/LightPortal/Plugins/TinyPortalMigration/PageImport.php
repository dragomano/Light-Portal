<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.04.25
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\Compat\Parsers\BBCodeParser;
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

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tiny_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=import_from_tp';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tiny_portal_migration']['page_import_desc'],
		];

		$this->run();

		app(TablePresenter::class)->show(
			PortalTableBuilder::make('tp_pages', Lang::$txt['lp_pages_import'])
				->withParams(50, defaultSortColumn: 'id')
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					IdColumn::make()->setSort('id'),
					PageSlugColumn::make()->setSort('shortname DESC', 'shortname'),
					TitleColumn::make()
						->setData('title', 'word_break')
						->setSort('subject', 'subject DESC'),
					CheckboxColumn::make(entity: 'pages'),
				])
				->addRow(ImportButtonsRow::make())
		);
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'tp_articles')))
			return [];

		$result = Db::$db->query('
			SELECT id, date, subject, author_id, off, views, shortname, type
			FROM {db_prefix}tp_articles
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
				'id'         => $row['id'],
				'slug'       => $row['shortname'],
				'type'       => $row['type'],
				'status'     => (int) empty($row['off']),
				'num_views'  => $row['views'],
				'author_id'  => $row['author_id'],
				'created_at' => DateTime::relative((int) $row['date']),
				'title'      => $row['subject'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix. 'tp_articles')))
			return 0;

		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}tp_articles',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query('
			SELECT
				a.id, a.date, a.body, a.intro, a.subject, a.author_id, a.off, a.options, a.comments, a.views,
				a.shortname, a.type, a.pub_start, a.pub_end, v.value3
			FROM {db_prefix}tp_articles AS a
				LEFT JOIN {db_prefix}tp_variables AS v ON (
					a.category = v.id AND v.type = {string:type}
				)' . (empty($ids) ? '' : '
			WHERE a.id IN ({array_int:pages})'),
			[
				'type'  => 'category',
				'pages' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id']] = [
				'page_id'      => $row['id'],
				'author_id'    => $row['author_id'],
				'slug'         => $row['shortname'] ?: ('page_' . $row['id']),
				'description'  => strip_tags((string) BBCodeParser::load()->parse($row['intro'])),
				'content'      => $row['body'],
				'type'         => $row['type'],
				'permissions'  => $this->getPagePermission($row),
				'status'       => (int) empty($row['off']),
				'num_views'    => $row['views'],
				'num_comments' => $row['comments'],
				'created_at'   => $row['date'],
				'updated_at'   => 0,
				'title'        => $row['subject'],
				'options'      => explode(',', (string) $row['options']),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	private function getPagePermission(array $row): int
	{
		$permissions = explode(',', (string) $row['value3']);

		return match (true) {
			count($permissions) == 1 && $permissions[0] == -1 => Permission::GUEST->value,
			count($permissions) == 1 && $permissions[0] == 0 => Permission::MEMBER->value,
			in_array(-1, $permissions), in_array(0, $permissions) => Permission::ALL->value,
			default => Permission::ADMIN->value,
		};
	}
}
