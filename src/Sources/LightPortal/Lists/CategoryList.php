<?php declare(strict_types=1);

/**
 * CategoryList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class CategoryList implements ListInterface
{
	use Helper;

	public function getAll(): array
	{
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT category_id, name, description, priority
			FROM {db_prefix}lp_categories
			ORDER BY priority',
			[]
		);

		$items = [0 => ['name' => Lang::$txt['lp_no_category']]];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['category_id']] = [
				'id'       => $row['category_id'],
				'name'     => $row['name'],
				'desc'     => $row['description'],
				'priority' => $row['priority']
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
