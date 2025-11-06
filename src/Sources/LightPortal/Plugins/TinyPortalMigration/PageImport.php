<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 26.10.25
 */

namespace LightPortal\Plugins\TinyPortalMigration;

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Parsers\BBCodeParser;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabasePageImport;
use LightPortal\Enums\EntryType;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\PageSlugColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\DateTime;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractDatabasePageImport
{
	protected string $langKey = 'lp_tiny_portal_migration';

	protected string $formAction = 'import_from_tp';

	protected string $uiTableId = 'tp_pages';

	protected function defineUiColumns(): array
	{
		return [
			IdColumn::make()->setSort('id'),
			PageSlugColumn::make()->setSort('shortname DESC', 'shortname'),
			TitleColumn::make()
				->setData('title', 'word_break')
				->setSort('subject', 'subject DESC'),
			CheckboxColumn::make(entity: 'pages'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		if (! $this->sql->tableExists('tp_articles'))
			return [];

		$select = $this->sql->select()
			->from('tp_articles')
			->columns(['id', 'date', 'subject', 'author_id', 'off', 'views', 'shortname', 'type'])
			->order($sort)
			->limit($limit)
			->offset($start);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id']] = [
				'id'         => $row['id'],
				'slug'       => $row['shortname'] ?: $this->generateSlug(['english' => $row['subject']]),
				'type'       => $row['type'],
				'status'     => empty($row['off']),
				'num_views'  => $row['views'],
				'author_id'  => $row['author_id'],
				'created_at' => DateTime::relative($row['date']),
				'title'      => $row['subject'],
			];
		}

		return $items;
	}

	public function getTotalCount(): int
	{
		if (! $this->sql->tableExists('tp_articles'))
			return 0;

		$select = $this->sql->select()
			->from('tp_articles')
			->columns(['count' => new Expression('COUNT(*)')]);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	protected function getItems(array $ids): array
	{
		$select = $this->sql->select()
			->from(['a' => 'tp_articles'])
			->columns([
				'id', 'date', 'body', 'intro', 'subject', 'author_id', 'off', 'options',
				'comments', 'views', 'shortname', 'type', 'pub_start', 'pub_end',
			])
			->join(
				['v' => 'tp_variables'],
				new Expression('a.category = v.id AND v.type = ?', ['category']),
				['value3'],
				Select::JOIN_LEFT
			);

		if ($ids !== []) {
			$select->where->in('a.id', $ids);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id']] = [
				'page_id'         => $row['id'],
				'category_id'     => 0,
				'author_id'       => $row['author_id'],
				'slug'            => $row['shortname'] ?: $this->generateSlug(['english' => $row['subject']]),
				'description'     => strip_tags((string) BBCodeParser::load()->parse($row['intro'])),
				'content'         => $row['body'],
				'type'            => $row['type'],
				'entry_type'      => EntryType::DEFAULT->name(),
				'permissions'     => $this->getPermission($row),
				'status'          => empty($row['off']),
				'num_views'       => $row['views'],
				'num_comments'    => $row['comments'],
				'created_at'      => $row['date'],
				'updated_at'      => 0,
				'deleted_at'      => 0,
				'last_comment_id' => 0,
				'title'           => $row['subject'],
				'options'         => explode(',', (string) $row['options']),
			];
		}

		return $items;
	}

	protected function extractPermissions(array $row): int|array
	{
		return array_map(intval(...), explode(',', (string) $row['value3']));
	}
}
