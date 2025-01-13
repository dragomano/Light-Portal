<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\LightPortal\Areas\Imports\Traits\WithCommentsTrait;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\EventManagerFactory;
use Bugo\LightPortal\Plugins\Event;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomPageImport extends AbstractCustomImport
{
	use WithCommentsTrait {
		replaceComments as replaceCommentsTrait;
	}

	protected string $entity = 'pages';

	protected function importItems(array &$items, array &$titles): array
	{
		$params = $comments = [];

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::importPages,
			new Event(new class ($items, $titles, $params, $comments) {
				public function __construct(
					public array &$items,
					public array &$titles,
					public array &$params,
					public array &$comments
				) {}
			})
		);

		$results = $this->insertData(
			'lp_pages',
			'replace',
			$items,
			[
				'page_id'      => 'int',
				'author_id'    => 'int',
				'slug'         => 'string-255',
				'description'  => 'string-255',
				'content'      => 'string',
				'type'         => 'string',
				'permissions'  => 'int',
				'status'       => 'int',
				'num_views'    => 'int',
				'num_comments' => 'int',
				'created_at'   => 'int',
				'updated_at'   => 'int',
			],
			['page_id'],
		);

		if ($results === [])
			return [];

		$this->replaceTitles($titles, $results);
		$this->replaceParams($params, $results);
		$this->replaceComments($comments, $results);

		return $results;
	}

	private function replaceComments(array $comments, array &$results): void
	{
		if ($comments === [] && $results === [])
			return;

		$tempComments = [];

		foreach ($comments as $comment) {
			foreach ($comment as $com) {
				$tempComments[] = $com;
			}
		}

		$this->replaceCommentsTrait($tempComments, $results);
	}
}
