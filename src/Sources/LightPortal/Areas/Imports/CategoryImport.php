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

use function array_merge;
use function intval;
use function str_starts_with;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryImport extends XmlImporter
{
	protected string $entity = 'categories';

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_categories_import_info'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_categories;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_categories_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function processItems(): void
	{
		$items = $translations = [];
		$categoryTitles = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$categoryId = intval($item['category_id']);
			$slug = (string) $item->slug;

			$itemTranslations = $this->extractTranslations($item);
			foreach ($itemTranslations as $translation) {
				if (isset($translation['title'])) {
					$categoryTitles[$categoryId][$translation['lang']] = $translation['title'];
				}
			}

			if (empty(trim($slug))) {
				$slug = 'temp-' . $categoryId;
			}

			$items[] = [
				'category_id' => $categoryId,
				'slug'        => $slug,
				'icon'        => (string) $item->icon,
				'priority'    => intval($item['priority']),
				'status'      => intval($item['status']),
			];

			$translations = array_merge($translations, $itemTranslations);
		}

		foreach ($items as &$item) {
			if (str_starts_with($item['slug'], 'temp-')) {
				$categoryId = $item['category_id'];
				$titles = $categoryTitles[$categoryId] ?? [];
				$item['slug'] = $this->generateSlug($titles);
			}
		}

		$this->startTransaction($items);

		$results = $this->insertData(
			'lp_categories',
			'replace',
			$items,
			[
				'category_id' => 'int',
				'slug'        => 'string',
				'icon'        => 'string',
				'priority'    => 'int',
				'status'      => 'int',
			],
			['category_id'],
		);

		$this->replaceTranslations($translations, $results);

		$this->finishTransaction($results);
	}
}
