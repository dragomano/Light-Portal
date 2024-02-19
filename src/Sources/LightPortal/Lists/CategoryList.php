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
 * @version 2.5
 */

namespace Bugo\LightPortal\Lists;

use Bugo\Compat\{Database as Db, Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class CategoryList implements ListInterface
{
	public function __invoke(): array
	{
		return $this->getAll();
	}

	public function getAll(): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT category_id, name, description, priority
			FROM {db_prefix}lp_categories
			ORDER BY priority',
			[]
		);

		$items = [0 => ['name' => Lang::$txt['lp_no_category']]];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['category_id']] = [
				'id'       => $row['category_id'],
				'name'     => $row['name'],
				'desc'     => $row['description'],
				'priority' => $row['priority']
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
