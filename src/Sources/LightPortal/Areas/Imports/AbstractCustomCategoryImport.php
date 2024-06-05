<?php declare(strict_types=1);

/**
 * AbstractCustomCategoryImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Config;
use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\Enums\PortalHook;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomCategoryImport extends AbstractCustomImport
{
	protected string $entity = 'categories';

	protected function importItems(array &$items, array &$titles): array
	{
		AddonHandler::getInstance()->run(PortalHook::importCategories, [&$items, &$titles]);

		foreach ($items as $id => $item) {
			$titles[] = [
				'type'  => 'category',
				'lang'  => Config::$language,
				'value' => $item['title'],
			];

			unset($items[$id]['title']);
		}

		$results = $this->insertData(
			'lp_categories',
			'',
			$items,
			[
				'icon'        => 'string-60',
				'description' => 'string-255',
				'priority'    => 'int',
				'status'      => 'int',
			],
			['category_id'],
		);

		if ($titles && $results) {
			foreach ($results as $key => $value) {
				$titles[$key]['item_id'] = $value;
			}

			$this->replaceTitles($titles, $results, '');
		}

		return $results;
	}
}
