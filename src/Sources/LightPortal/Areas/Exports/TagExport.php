<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Bricks\Tables\RowPosition;
use Bugo\Bricks\Tables\TablePresenter;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ExportButtonsRow;
use Bugo\LightPortal\UI\Tables\IconColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;

use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class TagExport extends XmlExporter
{
	protected string $entity = 'tags';

	public function __construct(private readonly TagRepository $repository) {}

	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_export'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_tags_export'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_tags;sa=export';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_export_description'],
		];

		$this->run();

		app(TablePresenter::class)->show(
			PortalTableBuilder::make('lp_tags', Lang::$txt['lp_tags_export'])
				->setDefaultSortColumn('id')
				->setItems($this->repository->getAll(...))
				->setCount($this->repository->getTotalCount(...))
				->addColumns([
					IdColumn::make()->setSort('tag_id'),
					IconColumn::make(),
					TitleColumn::make(entity: $this->entity),
					CheckboxColumn::make(entity: $this->entity)
				])
				->addRows([
					ExportButtonsRow::make()
						->setPosition(RowPosition::ABOVE_COLUMN_HEADERS),
					ExportButtonsRow::make()
				])
		);
	}

	protected function getData(): array
	{
		if ($this->isEntityEmpty())
			return [];

		$tags = $this->hasEntityInRequest() ? $this->request()->get($this->entity) : [];

		$result = Db::$db->query('
			SELECT tag.*, pt.page_id, t.lang, COALESCE(t.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_tags AS tag
				LEFT JOIN {db_prefix}lp_page_tag AS pt ON (tag.tag_id = pt.tag_id)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag}
				)
			WHERE (title IS NOT NULL AND title != {string:empty_string})' . (empty($tags) ? '' : '
				AND tag.tag_id IN ({array_int:tags})'),
			[
				'empty_string' => '',
				'tags'         => $tags,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] ??= [
				'tag_id' => $row['tag_id'],
				'slug'   => $row['slug'],
				'icon'   => trim($row['icon'] ?? ''),
				'status' => $row['status'],
			];

			if ($row['lang'] && $row['title']) {
				$items[$row['tag_id']]['titles'][$row['lang']] = trim($row['title']);
			}

			if ($row['page_id']) {
				$items[$row['tag_id']]['pages'][$row['page_id']] = [
					'id' => $row['page_id'],
				];
			}
		}

		Db::$db->free_result($result);

		return $items;
	}

	protected function getFile(): string
	{
		$items = $this->getData();

		return $this->createXmlFile($items, ['tag_id', 'status']);
	}
}
