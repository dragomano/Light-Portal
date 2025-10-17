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

namespace Bugo\LightPortal\DataHandlers\Traits;

trait HasComments
{
	protected function replaceComments(array $comments, array $results, bool $replace = true): array
	{
		if ($comments === [] || $results === [])
			return [];

		return $this->insertData('lp_comments', $comments, ['id', 'page_id'], $replace);
	}
}
