<?php declare(strict_types=1);

/**
 * TagList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class TagList implements ListInterface
{
	use Helper;

	public function getAll(): array
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT tag_id, value
			FROM {db_prefix}lp_tags
			ORDER BY value',
			[]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['tag_id']] = $row['value'];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}
}
