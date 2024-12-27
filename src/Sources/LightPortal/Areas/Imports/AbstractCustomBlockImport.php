<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.8
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Config;
use Bugo\LightPortal\Args\ItemsTitlesArgs;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomBlockImport extends AbstractCustomImport
{
	protected string $entity = 'blocks';

	protected function importItems(array &$items, array &$titles): array
	{
		app('events')->dispatch(
			PortalHook::importBlocks,
			new Event(new ItemsTitlesArgs($items, $titles))
		);

		foreach ($items as $id => $item) {
			$titles[] = [
				'type'  => 'block',
				'lang'  => Config::$language,
				'value' => $item['title'],
			];

			unset($items[$id]['title']);
		}

		$results = $this->insertData(
			'lp_blocks',
			'',
			$items,
			[
				'type'          => 'string',
				'content'       => 'string',
				'placement'     => 'string',
				'permissions'   => 'int',
				'status'        => 'int',
				'title_class'   => 'string',
				'content_class' => 'string',
			],
			['block_id'],
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
