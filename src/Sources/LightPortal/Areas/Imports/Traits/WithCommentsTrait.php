<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Areas\Imports\Traits;

trait WithCommentsTrait
{
	protected function replaceComments(array $comments, array &$results): void
	{
		if ($comments === [] || $results === [])
			return;

		$results = $this->insertData(
			'lp_comments',
			'replace',
			$comments,
			[
				'id'         => 'int',
				'parent_id'  => 'int',
				'page_id'    => 'int',
				'author_id'  => 'int',
				'message'    => 'string',
				'created_at' => 'int',
			],
			['id', 'page_id'],
		);
	}
}
