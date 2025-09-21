<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace Bugo\LightPortal\DataHandlers\Imports\Custom;

use Bugo\LightPortal\DataHandlers\Traits\HasComments;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\HasEvents;

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
		return $this->insertData(
			'lp_pages',
			'replace',
			$items,
			[
				'page_id'         => 'int',
				'category_id'     => 'int',
				'author_id'       => 'int',
				'slug'            => 'string-255',
				'type'            => 'string-10',
				'entry_type'      => 'string-10',
				'permissions'     => 'int',
				'status'          => 'int',
				'num_views'       => 'int',
				'num_comments'    => 'int',
				'created_at'      => 'int',
				'updated_at'      => 'int',
				'deleted_at'      => 'int',
				'last_comment_id' => 'int',
			],
			['page_id'],
		);
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

		$this->replaceParams($params, $results);
		$this->replaceComments($comments, $results);

		return $results;
	}
}
