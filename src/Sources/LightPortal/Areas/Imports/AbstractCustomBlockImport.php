<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Config;
use Bugo\LightPortal\Enums\PortalHook;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomBlockImport extends AbstractCustomImport
{
	protected string $entity = 'blocks';

	protected function importItems(array &$items, array &$titles): array
	{
		$this->events()->dispatch(PortalHook::importBlocks, ['items' => &$items, 'titles' => &$titles]);

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
