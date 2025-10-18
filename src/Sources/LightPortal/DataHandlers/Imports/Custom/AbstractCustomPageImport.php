<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace LightPortal\DataHandlers\Imports\Custom;

use LightPortal\DataHandlers\Traits\HasComments;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\HasEvents;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomPageImport extends AbstractCustomImport
{
	use HasComments;
	use HasEvents;

	protected string $type = 'page';

	protected string $entity = 'pages';

	protected string $sortColumn = 'id';

	abstract protected function extractPermissions(array $row): int|array;

	protected function getResults(array $items): array
	{
		return $this->insertData('lp_pages', $items);
	}

	protected function importItems(array $items): array
	{
		$params = $comments = [];

		$this->events()->dispatch(
			PortalHook::onCustomPageImport,
			[
				'items'    => &$items,
				'params'   => &$params,
				'comments' => &$comments,
			]
		);

		$results = parent::importItems($items);

		if ($results === [])
			return [];

		$this->replaceParams($params, $results, replace: false);
		$this->replaceComments($comments, $results, replace: false);

		return $results;
	}
}
