<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace LightPortal\DataHandlers\Imports\Database;

use LightPortal\DataHandlers\Traits\HasComments;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractDatabasePageImport extends AbstractDatabaseImport
{
	use HasComments;

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

		$dispatcher = app(EventDispatcherInterface::class);

		$dispatcher->dispatch(
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

		$this->replaceParams($params, false);

		$commentTranslations = $this->extractCommentMessages($comments);

		$this->replaceComments($comments, false);
		$this->replaceCommentTranslations($commentTranslations);

		return $results;
	}

	protected function extractCommentMessages(array &$comments): array
	{
		$translations = [];

		foreach ($comments as &$comment) {
			if (isset($comment['messages'])) {
				foreach ($comment['messages'] as $lang => $message) {
					$translations[] = [
						'item_id' => $comment['id'],
						'type'    => 'comment',
						'lang'    => $lang,
						'content' => $message,
					];
				}

				unset($comment['messages']);
			}
		}

		return $translations;
	}
}
