<?php declare(strict_types=1);

/**
 * TagList.php
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

use Bugo\Compat\{Database as Db, Utils};
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class TagList implements ListInterface
{
	use Helper;

	public function getAll(): array
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT tag_id, value
			FROM {db_prefix}lp_tags
			ORDER BY value',
			[]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] = $row['value'];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
