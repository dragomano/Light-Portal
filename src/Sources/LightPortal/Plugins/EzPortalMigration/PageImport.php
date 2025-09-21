<?php declare(strict_types=1);

/**
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.09.25
 */

namespace Bugo\LightPortal\Plugins\EzPortalMigration;

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\Custom\AbstractCustomPageImport;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\PageSlugColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\DateTime;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractCustomPageImport
{
	protected string $langKey = 'lp_ez_portal_migration';

	protected string $formAction = 'import_from_ez';

	protected string $uiTableId = 'ez_pages';

	protected function defineUiColumns(): array
	{
		return [
			IdColumn::make()
				->setSort('id_page'),
			PageSlugColumn::make()
				->setSort('title DESC', 'title'),
			TitleColumn::make()
				->setData('title', 'word_break'),
			CheckboxColumn::make(entity: 'pages'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_page'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'ezp_page')))
			return [];

		$result = Db::$db->query('
			SELECT id_page, date, title, views
			FROM {db_prefix}ezp_page
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
				'slug'       => $this->getSlug($row),
				'type'       => 'html',
				'status'     => 1,
				'num_views'  => $row['views'],
				'author_id'  => User::$me->id,
				'created_at' => DateTime::relative((int) $row['date']),
				'title'      => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'ezp_page')))
			return 0;

		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}ezp_page',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT id_page, date, title, content, views, permissions
			FROM {db_prefix}ezp_page' . (empty($ids) ? '' : '
			WHERE id_page IN ({array_int:pages})'),
			[
				'pages' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id_page']] = [
				'page_id'         => (int) $row['id_page'],
				'category_id'     => 0,
				'author_id'       => User::$me->id,
				'slug'            => $this->getSlug($row),
				'description'     => '',
				'content'         => $row['content'],
				'type'            => 'html',
				'entry_type'      => EntryType::DEFAULT->name(),
				'permissions'     => $this->getPermission($row),
				'status'          => 1,
				'num_views'       => (int) $row['views'],
				'num_comments'    => 0,
				'created_at'      => (int) $row['date'],
				'updated_at'      => 0,
				'deleted_at'      => 0,
				'last_comment_id' => 0,
				'title'           => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	protected function extractPermissions(array $row): int|array
	{
		return array_map('intval', explode(',', (string) $row['permissions']));
	}

	private function getSlug(array $row): string
	{
		return Utils::$smcFunc['strtolower'](explode(' ', (string) $row['title'])[0]) . $row['id_page'];
	}
}
