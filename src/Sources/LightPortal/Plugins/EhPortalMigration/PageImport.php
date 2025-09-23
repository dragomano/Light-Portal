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

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\Custom\AbstractCustomPageImport;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\PageSlugColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\DateTime;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractCustomPageImport
{
	protected string $langKey = 'lp_eh_portal_migration';

	protected string $formAction = 'import_from_eh';

	protected string $uiTableId = 'eh_pages';

	protected function defineUiColumns(): array
	{
		return [
			IdColumn::make()
				->setSort('id_page'),
			PageSlugColumn::make()
				->setSort('namespace DESC', 'namespace'),
			TitleColumn::make()
				->setData('title', 'word_break'),
			CheckboxColumn::make(entity: 'pages'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_page'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_pages')))
			return [];

		$result = Db::$db->query('
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
				'slug'       => $row['namespace'] ?: $this->getSlug($row),
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

		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}sp_pages',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query(/** @lang text */ '
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
				'page_id'         => (int) $row['id_page'],
				'category_id'     => 0,
				'author_id'       => User::$me->id,
				'slug'            => $row['namespace'] ?: $this->getSlug($row),
				'description'     => '',
				'content'         => $row['body'],
				'type'            => $row['type'],
				'entry_type'      => EntryType::DEFAULT->name(),
				'permissions'     => $this->getPermission($row),
				'status'          => (int) $row['status'],
				'num_views'       => (int) $row['views'],
				'num_comments'    => 0,
				'created_at'      => time(),
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
		if (! empty($row['permission_set'])) {
			return (int) $row['permission_set'];
		}

		return match ((int) $row['groups_allowed']) {
			-1      => Permission::GUEST->value,
			0       => Permission::MEMBER->value,
			1       => Permission::ADMIN->value,
			default => Permission::ALL->value,
		};
	}

	private function getSlug(array $row): string
	{
		return Utils::$smcFunc['strtolower'](explode(' ', (string) $row['title'])[0]) . $row['id_page'];
	}
}
