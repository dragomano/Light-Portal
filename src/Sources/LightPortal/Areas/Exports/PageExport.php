<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
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
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ExportButtonsRow;
use Bugo\LightPortal\UI\Tables\PageSlugColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\Str;
use DomDocument;
use DOMException;

use function in_array;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class PageExport extends AbstractExport
{
	private readonly PageRepository $repository;

	public function __construct()
	{
		$this->repository = app('page_repo');
	}

	public function main(): void
	{
		User::mustHavePermission('admin_forum');

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_export'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_export'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=export';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_export_description'],
		];

		$this->run();

		TablePresenter::show(
			PortalTableBuilder::make('lp_pages', Lang::$txt['lp_pages_export'])
				->setDefaultSortColumn('id')
				->setItems($this->repository->getAll(...))
				->setCount($this->repository->getTotalCount(...))
				->addColumns([
					IdColumn::make()->setSort('p.page_id'),
					PageSlugColumn::make(),
					TitleColumn::make(entity: 'pages')->setData(static fn($entry) => Str::html('a', [
						'class' => 'bbc_link' . ($entry['is_front'] ? ' new_posts' : ''),
						'href'  => $entry['is_front'] ? Config::$scripturl : (LP_PAGE_URL . $entry['slug']),
					])->setText($entry['title'])),
					CheckboxColumn::make(entity: 'pages')
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
		if ($this->request()->isEmpty('pages') && $this->request()->hasNot('export_all'))
			return [];

		$pages = $this->request('pages') && $this->request()->hasNot('export_all') ? $this->request('pages') : null;

		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.slug, p.description, p.content, p.type, p.entry_type,
				p.permissions, p.status, p.num_views, p.num_comments, p.created_at, p.updated_at, p.deleted_at,
				pt.lang, pt.value AS title, pp.name, pp.value,
				com.id, com.parent_id, com.author_id AS com_author_id, com.message, com.created_at AS com_created_at
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {literal:page})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
				LEFT JOIN {db_prefix}lp_comments AS com ON (p.page_id = com.page_id)' . (empty($pages) ? '' : '
			WHERE p.page_id IN ({array_int:pages})'),
			[
				'pages' => $pages,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['page_id']] ??= [
				'page_id'      => $row['page_id'],
				'category_id'  => $row['category_id'],
				'author_id'    => $row['author_id'],
				'slug'         => $row['slug'],
				'description'  => trim($row['description'] ?? ''),
				'content'      => $row['content'],
				'type'         => $row['type'],
				'entry_type'   => $row['entry_type'],
				'permissions'  => $row['permissions'],
				'status'       => $row['status'],
				'num_views'    => $row['num_views'],
				'num_comments' => $row['num_comments'],
				'created_at'   => $row['created_at'],
				'updated_at'   => $row['updated_at'],
				'deleted_at'   => $row['deleted_at'],
			];

			if ($row['lang'] && $row['title']) {
				$items[$row['page_id']]['titles'][$row['lang']] = $row['title'];
			}

			if ($row['name'] && $row['value']) {
				$items[$row['page_id']]['params'][$row['name']] = $row['value'];
			}

			if ($row['message'] && trim((string) $row['message'])) {
				$items[$row['page_id']]['comments'][$row['id']] = [
					'id'         => $row['id'],
					'parent_id'  => $row['parent_id'],
					'author_id'  => $row['com_author_id'],
					'message'    => trim((string) $row['message']),
					'created_at' => $row['com_created_at'],
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

			$xmlElements = $root->appendChild($xml->createElement('pages'));

			$items = $this->getGeneratorFrom($items);

			foreach ($items() as $item) {
				$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
				foreach ($item as $key => $val) {
					$xmlName = $xmlElement->appendChild(
						in_array($key, [
							'page_id', 'category_id', 'author_id', 'permissions', 'status', 'num_views',
							'num_comments', 'created_at', 'updated_at', 'deleted_at'
						])
							? $xml->createAttribute($key)
							: $xml->createElement($key)
					);

					if (in_array($key, ['titles', 'params'])) {
						foreach ($val as $k => $v) {
							$xmlTitle = $xmlName->appendChild($xml->createElement($k));
							$xmlTitle->appendChild($xml->createTextNode($v));
						}
					} elseif (in_array($key, ['description', 'content'])) {
						$xmlName->appendChild($xml->createCDATASection($val));
					} elseif ($key == 'comments') {
						foreach ($val as $comment) {
							$xmlComment = $xmlName->appendChild($xml->createElement('comment'));
							foreach ($comment as $label => $text) {
								$xmlCommentElem = $xmlComment->appendChild($label == 'message'
									? $xml->createElement($label)
									: $xml->createAttribute($label));
								$xmlCommentElem->appendChild($label == 'message'
									? $xml->createCDATASection($text)
									: $xml->createTextNode($text));
							}
						}
					} else {
						$xmlName->appendChild($xml->createTextNode($val));
					}
				}
			}

			$file = Sapi::getTempDir() . '/lp_pages_backup.xml';
			$xml->save($file);
		} catch (DOMException $e) {
			ErrorHandler::log('[LP] ' . Lang::$txt['lp_pages_export'] . ': ' . $e->getMessage(), 'user');
		}

		return $file ?? '';
	}
}
