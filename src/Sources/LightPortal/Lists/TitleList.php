<?php declare(strict_types=1);

/**
 * TitleList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Lists;

use Bugo\Compat\Db;

if (! defined('SMF'))
	die('No direct access...');

final class TitleList implements ListInterface
{
	public function __invoke(): array
	{
		return $this->getAll();
	}

	public function getAll(): array
	{
		$result = Db::$db->query('', '
			SELECT item_id, lang, value
			FROM {db_prefix}lp_titles
			WHERE type = {string:type}
				AND value <> {string:blank_string}
			ORDER BY lang, value',
			[
				'type'         => 'page',
				'blank_string' => '',
			]
		);

		$titles = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$titles[$row['item_id']][$row['lang']] = $row['value'];
		}

		Db::$db->free_result($result);

		return $titles;
	}
}
