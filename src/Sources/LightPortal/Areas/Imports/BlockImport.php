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

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\TitleClass;

use function intval;
use function str_contains;
use function str_replace;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property $item
 */
final class BlockImport extends AbstractImport
{
	protected string $entity = 'blocks';

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_blocks_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_blocks_import_info'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_blocks;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function run(): void
	{
		if (empty($xml = $this->getFile()))
			return;

		if (! isset($xml->blocks->item[0]['block_id'])) {
			ErrorHandler::fatalLang('lp_wrong_import_file', false);
		}

		$items = $translations = $params = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'block_id'      => $blockId = intval($item['block_id']),
					'icon'          => $item->icon,
					'type'          => str_replace('md', 'markdown', (string) $item->type),
					'placement'     => $item->placement,
					'priority'      => intval($item['priority']),
					'permissions'   => $item['user_id'] > 0 ? 4 : intval($item['permissions']),
					'status'        => intval($item['status']),
					'areas'         => $item->areas,
					'title_class'   => str_contains((string) $item->title_class, 'div.')
						? TitleClass::CAT_BAR->value
						: $item->title_class,
					'content_class' => str_contains((string) $item->content_class, 'div.')
						? ContentClass::ROUNDFRAME->value
						: $item->content_class,
				];

				if ($item->titles || $item->contents || $item->descriptions) {
					foreach ($item->titles as $title) {
						foreach ($title as $lang => $text) {
							if (! isset($translations[$lang . '_' . $blockId])) {
								$translations[$lang . '_' . $blockId] = [
									'item_id' => $blockId,
									'type'    => 'block',
									'lang'    => $lang,
									'title'   => (string) $text,
								];
							}
						}
					}

					foreach ($item->contents as $content) {
						foreach ($content as $lang => $text) {
							if (! isset($translations[$lang . '_' . $blockId])) {
								$translations[$lang . '_' . $blockId] = [
									'item_id' => $blockId,
									'type'    => 'block',
									'lang'    => $lang,
								];
							}

							$translations[$lang . '_' . $blockId]['content'] = (string) $text;
						}
					}

					foreach ($item->descriptions as $description) {
						foreach ($description as $lang => $text) {
							if (! isset($translations[$lang . '_' . $blockId])) {
								$translations[$lang . '_' . $blockId] = [
									'item_id' => $blockId,
									'type'    => 'block',
									'lang'    => $lang,
								];
							}

							$translations[$lang . '_' . $blockId]['description'] = (string) $text;
						}
					}
				}

				if ($item->params) {
					foreach ($item->params as $param) {
						foreach ($param as $k => $v) {
							$params[] = [
								'item_id' => $blockId,
								'type'    => 'block',
								'name'    => $k,
								'value'   => $v,
							];
						}
					}
				}
			}
		}

		$this->startTransaction($items);

		$results = $this->insertData(
			'lp_blocks',
			'replace',
			$items,
			[
				'block_id'      => 'int',
				'icon'          => 'string',
				'type'          => 'string',
				'placement'     => 'string-10',
				'priority'      => 'int',
				'permissions'   => 'int',
				'status'        => 'int',
				'areas'         => 'string',
				'title_class'   => 'string',
				'content_class' => 'string',
			],
			['block_id'],
		);

		$this->replaceTranslations($translations, $results);
		$this->replaceParams($params, $results);

		$this->finishTransaction($results);
	}
}
