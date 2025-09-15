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
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\TitleClass;

use function array_merge;
use function intval;
use function str_contains;
use function str_replace;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class BlockImport extends XmlImporter
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

	protected function processItems(): void
	{
		$items = $translations = $params = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$blockId = intval($item['block_id']);

			$items[] = [
				'block_id'      => $blockId,
				'icon'          => (string) $item->icon,
				'type'          => str_replace('md', 'markdown', (string) $item->type),
				'placement'     => (string) $item->placement,
				'priority'      => intval($item['priority']),
				'permissions'   => intval($item['permissions']),
				'status'        => intval($item['status']),
				'areas'         => (string) $item->areas,
				'title_class'   => str_contains((string) $item->title_class, 'div.')
					? TitleClass::CAT_BAR->value
					: (string) $item->title_class,
				'content_class' => str_contains((string) $item->content_class, 'div.')
					? ContentClass::ROUNDFRAME->value
					: (string) $item->content_class,
			];

			$translations = array_merge($translations, $this->extractTranslations($item));
			$params = array_merge($params, $this->extractParams($item));
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
