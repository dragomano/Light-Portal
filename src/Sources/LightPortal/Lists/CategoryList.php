<?php declare(strict_types=1);

/**
 * CategoryList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryList implements ListInterface
{
	use Helper;

	public function getAll(): array
	{
		$result = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT category_id, name, description, priority
			FROM {db_prefix}lp_categories
			ORDER BY priority',
			[]
		);

		$items = [0 => ['name' => $this->txt['lp_no_category']]];
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			$items[$row['category_id']] = [
				'id'       => $row['category_id'],
				'name'     => $row['name'],
				'desc'     => $row['description'],
				'priority' => $row['priority']
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $items;
	}
}
