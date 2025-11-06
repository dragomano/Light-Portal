<?php declare(strict_types=1);

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 09.10.25
 */

namespace LightPortal\Plugins\EhPortalMigration;

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\User;
use Laminas\Db\Sql\Expression;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabasePageImport;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\PageSlugColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\DateTime;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractDatabasePageImport
{
	protected string $langKey = 'lp_eh_portal_migration';

	protected string $formAction = 'import_from_ep';

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
		if (! $this->sql->tableExists('sp_pages'))
			return [];

		$select = $this->sql->select()
			->from('sp_pages')
			->columns([
				'id_page', 'namespace', 'title', 'body', 'type', 'permission_set', 'groups_allowed', 'views', 'status',
			])
			->order($sort)
			->limit($limit)
			->offset($start);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id_page']] = [
				'id'         => $row['id_page'],
				'slug'       => $row['namespace'] ?: $this->generateSlug(['english' => $row['title']]),
				'type'       => $row['type'],
				'status'     => $row['status'],
				'num_views'  => $row['views'],
				'author_id'  => User::$me->id,
				'created_at' => DateTime::relative(time()),
				'title'      => $row['title'],
			];
		}

		return $items;
	}

	public function getTotalCount(): int
	{
		if (! $this->sql->tableExists('sp_pages'))
			return 0;

		$select = $this->sql->select()
			->from('sp_pages')
			->columns(['count' => new Expression('COUNT(*)')]);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	protected function getItems(array $ids): array
	{
		$select = $this->sql->select()
			->from('sp_pages')
			->columns([
				'id_page', 'namespace', 'title', 'body', 'type', 'permission_set', 'groups_allowed', 'views', 'status',
			]);

		if ($ids !== []) {
			$select->where->in('id_page', $ids);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id_page']] = [
				'page_id'         => $row['id_page'],
				'category_id'     => 0,
				'author_id'       => User::$me->id,
				'slug'            => $row['namespace'] ?: $this->generateSlug(['english' => $row['title']]),
				'description'     => '',
				'content'         => $row['body'],
				'type'            => $row['type'],
				'entry_type'      => EntryType::DEFAULT->name(),
				'permissions'     => $this->getPermission($row),
				'status'          => $row['status'],
				'num_views'       => $row['views'],
				'num_comments'    => 0,
				'created_at'      => time(),
				'updated_at'      => 0,
				'deleted_at'      => 0,
				'last_comment_id' => 0,
				'title'           => $row['title'],
			];
		}

		return $items;
	}

	protected function extractPermissions(array $row): int|array
	{
		if (! empty($row['permission_set'])) {
			return $row['permission_set'];
		}

		return match ($row['groups_allowed']) {
			-1      => Permission::GUEST->value,
			0       => Permission::MEMBER->value,
			1       => Permission::ADMIN->value,
			default => Permission::ALL->value,
		};
	}
}
