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

use Bugo\Compat\{Config, Db, ErrorHandler};
use Bugo\Compat\{Lang, Sapi, Utils};
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\Utils\ItemList;
use Bugo\LightPortal\Utils\RequestTrait;
use DomDocument;
use DOMException;
use Nette\Utils\Html;

use function in_array;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class TagExport extends AbstractExport
{
	use RequestTrait;

	private readonly TagRepository $repository;

	public function __construct()
	{
		$this->repository = new TagRepository();
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

		$listOptions = [
			'id' => 'lp_tags',
			'items_per_page' => 20,
			'title' => Lang::$txt['lp_tags_export'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'],
			'default_sort_col' => 'id',
			'get_items' => [
				'function' => $this->repository->getAll(...)
			],
			'get_count' => [
				'function' => $this->repository->getTotalCount(...)
			],
			'columns' => [
				'id' => [
					'header' => [
						'value' => '#',
						'style' => 'width: 5%'
					],
					'data' => [
						'db'    => 'id',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'tag_id',
						'reverse' => 'tag_id DESC'
					]
				],
				'icon' => [
					'header' => [
						'value' => Lang::$txt['custom_profile_icon']
					],
					'data' => [
						'db'    => 'icon',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'icon',
						'reverse' => 'icon DESC'
					]
				],
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title'],
					],
					'data' => [
						'function' => static fn($entry) => $entry['status']
							? Html::el('a', ['class' => 'bbc_link'])
								->href(LP_BASE_URL . ';sa=tags;id=' . $entry['id'])
								->setText($entry['title'])
								->toHtml()
							: $entry['title'],
						'class' => 'word_break',
					],
					'sort' => [
						'default' => 't.value DESC',
						'reverse' => 't.value',
					],
				],
				'actions' => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);">'
					],
					'data' => [
						'function' => static fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="tags[]">',
						'class' => 'centertext'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['form_action']
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="hidden">
						<input type="submit" name="export_selection" value="' . Lang::$txt['lp_export_selection'] . '" class="button">
						<input type="submit" name="export_all" value="' . Lang::$txt['lp_export_all'] . '" class="button">'
				]
			]
		];

		new ItemList($listOptions);
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
			ErrorHandler::log('[LP] ' . Lang::$txt['lp_tags_export'] . ': ' . $e->getMessage());
		}

		return $file ?? '';
	}
}
