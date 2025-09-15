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

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use SimpleXMLElement;

use function array_merge;
use function intval;
use function str_starts_with;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class TagImport extends XmlImporter
{
	protected string $entity = 'tags';

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_tags_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_tags_import_info'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_tags;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function processItems(): void
	{
		$items = $translations = $pages = [];
		$tagTitles = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$tagId = intval($item['tag_id']);
			$slug = (string) $item->slug;

			$itemTranslations = $this->extractTranslations($item);
			foreach ($itemTranslations as $translation) {
				if (isset($translation['title'])) {
					$tagTitles[$tagId][$translation['lang']] = $translation['title'];
				}
			}

			if (empty(trim($slug))) {
				$slug = 'temp-' . $tagId;
			}

			$items[] = [
				'tag_id' => $tagId,
				'slug'   => $slug,
				'icon'   => (string) $item->icon,
				'status' => intval($item['status']),
			];

			$translations = array_merge($translations, $itemTranslations);
			$pages = array_merge($pages, $this->extractPages($item));
		}

		foreach ($items as &$item) {
			if (str_starts_with($item['slug'], 'temp-')) {
				$tagId = $item['tag_id'];
				$titles = $tagTitles[$tagId] ?? [];
				$item['slug'] = $this->generateSlug($titles);
			}
		}

		$this->startTransaction($items);

		$results = $this->insertData(
			'lp_tags',
			'replace',
			$items,
			[
				'tag_id' => 'int',
				'slug'   => 'string',
				'icon'   => 'string',
				'status' => 'int',
			],
			['tag_id'],
		);

		$this->replaceTranslations($translations, $results);
		$this->replacePages($pages, $results);

		$this->finishTransaction($results);
	}

	protected function extractPages(SimpleXMLElement $item): array
	{
		$pages = [];
		$itemId = (string) $item['tag_id'];

		if ($item->pages ?? null) {
			foreach ($item->pages as $page) {
				foreach ($page as $v) {
					$pages[] = [
						'page_id' => intval($v['id']),
						'tag_id'  => $itemId,
					];
				}
			}
		}

		return $pages;
	}

	protected function replacePages(array $pages, array &$results): void
	{
		if ($pages === [] || $results === [])
			return;

		$results = $this->insertData(
			'lp_page_tag',
			'replace',
			$pages,
			[
				'page_id' => 'int',
				'tag_id'  => 'int',
			],
			['page_id', 'tag_id'],
		);
	}
}
