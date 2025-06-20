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
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;

use function intval;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property mixed|void $item
 */
final class CategoryImport extends AbstractImport
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

	protected function run(): void
	{
		if (empty($xml = $this->getFile()))
			return;

		if (! isset($xml->categories->item[0]['category_id'])) {
			ErrorHandler::fatalLang('lp_wrong_import_file', false);
		}

		$items = $translations = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'category_id' => $categoryId = intval($item['category_id']),
					'slug'        => (string) $item->slug,
					'icon'        => $item->icon,
					'priority'    => intval($item['priority']),
					'status'      => intval($item['status']),
				];

				if ($item->titles || $item->descriptions) {
					foreach ($item->titles as $title) {
						foreach ($title as $lang => $text) {
							if (! isset($translations[$lang . '_' . $categoryId])) {
								$translations[] = [
									'item_id' => $categoryId,
									'type'    => 'category',
									'lang'    => $lang,
									'title'   => (string) $text,
								];
							}
						}
					}

					foreach ($item->descriptions as $description) {
						foreach ($description as $lang => $text) {
							if (! isset($translations[$lang . '_' . $categoryId])) {
								$translations[$lang . '_' . $categoryId] = [
									'item_id' => $categoryId,
									'type'    => 'category',
									'lang'    => $lang,
								];
							}

							$translations[$lang . '_' . $categoryId]['description'] = (string) $text;
						}
					}
				}
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
