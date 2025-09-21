<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.09.25
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Parsers\BBCodeParser;
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
				'slug'       => $row['shortname'] ?: $this->getSlug($row),
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
				'page_id'         => (int) $row['id'],
				'category_id'     => 0,
				'author_id'       => (int) $row['author_id'],
				'slug'            => $row['shortname'] ?: $this->getSlug($row),
				'description'     => strip_tags((string) BBCodeParser::load()->parse($row['intro'])),
				'content'         => $row['body'],
				'type'            => $row['type'],
				'entry_type'      => EntryType::DEFAULT->name(),
				'permissions'     => $this->getPermission($row),
				'status'          => (int) empty($row['off']),
				'num_views'       => (int) $row['views'],
				'num_comments'    => (int) $row['comments'],
				'created_at'      => (int) $row['date'],
				'updated_at'      => 0,
				'deleted_at'      => 0,
				'last_comment_id' => 0,
				'title'           => $row['subject'],
				'options'         => explode(',', (string) $row['options']),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	protected function extractPermissions(array $row): int|array
	{
		return array_map('intval', explode(',', (string) $row['value3']));
	}

	private function getSlug(array $row): string
	{
		return Utils::$smcFunc['strtolower'](explode(' ', (string) $row['subject'])[0]) . $row['id'];
	}
}
