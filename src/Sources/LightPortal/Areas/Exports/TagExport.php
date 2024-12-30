<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Bricks\Tables\RowPosition;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ExportButtonsRow;
use Bugo\LightPortal\UI\Tables\IconColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use DomDocument;
use DOMException;

use function in_array;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class TagExport extends AbstractExport
{
	private readonly TagRepository $repository;

	public function __construct()
	{
		$this->repository = app('tag_repo');
	}

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

		TablePresenter::show(
			PortalTableBuilder::make('lp_tags', Lang::$txt['lp_tags_export'])
				->setDefaultSortColumn('id')
				->setItems($this->repository->getAll(...))
				->setCount($this->repository->getTotalCount(...))
				->addColumns([
					IdColumn::make()->setSort('tag_id'),
					IconColumn::make(),
					TitleColumn::make(entity: 'tags'),
					CheckboxColumn::make(entity: 'tags')
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
		if ($this->request()->isEmpty('tags') && $this->request()->hasNot('export_all'))
			return [];

		$tags = $this->request('tags') && $this->request()->hasNot('export_all') ? $this->request('tags') : null;

		$result = Db::$db->query('', '
			SELECT c.tag_id, c.icon, c.status, pt.page_id, t.lang, t.value AS title
			FROM {db_prefix}lp_tags AS c
				LEFT JOIN {db_prefix}lp_page_tag AS pt ON (c.tag_id = pt.tag_id)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					c.tag_id = t.item_id AND t.type = {literal:tag}
				)' . (empty($tags) ? '' : '
			WHERE c.tag_id IN ({array_int:tags})'),
			[
				'tags' => $tags,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] ??= [
				'tag_id' => $row['tag_id'],
				'icon'   => trim($row['icon'] ?? ''),
				'status' => $row['status'],
			];

			if ($row['lang'] && $row['title']) {
				$items[$row['tag_id']]['titles'][$row['lang']] = $row['title'];
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
		if (empty($items = $this->getData()))
			return '';

		try {
			$xml = new DomDocument('1.0', 'utf-8');
			$root = $xml->appendChild($xml->createElement('light_portal'));

			$xml->formatOutput = true;

			$xmlElements = $root->appendChild($xml->createElement('tags'));

			$items = $this->getGeneratorFrom($items);

			foreach ($items() as $item) {
				$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
				foreach ($item as $key => $val) {
					$xmlName = $xmlElement->appendChild(
						in_array($key, ['tag_id', 'status'])
							? $xml->createAttribute($key)
							: $xml->createElement($key)
					);

					if ($key === 'titles') {
						foreach ($val as $k => $v) {
							$xmlTitle = $xmlName->appendChild($xml->createElement($k));
							$xmlTitle->appendChild($xml->createTextNode($v));
						}
					} elseif ($key == 'pages') {
						foreach ($val as $page) {
							$xmlPage = $xmlName->appendChild($xml->createElement('page'));
							foreach ($page as $label => $text) {
								$xmlPageElem = $xmlPage->appendChild($xml->createAttribute($label));
								$xmlPageElem->appendChild($xml->createTextNode($text));
							}
						}
					} else {
						$xmlName->appendChild($xml->createTextNode($val));
					}
				}
			}

			$file = Sapi::getTempDir() . '/lp_tags_backup.xml';
			$xml->save($file);
		} catch (DOMException $e) {
			ErrorHandler::log('[LP] ' . Lang::$txt['lp_tags_export'] . ': ' . $e->getMessage(), 'user');
		}

		return $file ?? '';
	}
}
