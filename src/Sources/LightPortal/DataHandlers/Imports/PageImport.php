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

namespace Bugo\LightPortal\DataHandlers\Imports;

use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\DataHandlers\Traits\HasComments;
use Bugo\LightPortal\DataHandlers\Traits\HasSlug;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;
use SimpleXMLElement;

if (! defined('SMF'))
	die('No direct access...');

class PageImport extends XmlImporter
{
	use HasComments;
	use HasSlug;

	protected string $entity = 'pages';

	public function __construct(
		PortalSqlInterface $sql,
		FileInterface $file,
		ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($this->entity, $sql, $file, $errorHandler);
	}

	protected function processItems(): void
	{
		$items = $translations = $params = $comments = [];
		$pageTitles = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$pageId = intval($item['page_id']);
			$status = intval($item['status']);

			$entryType = match ($status) {
				3       => EntryType::INTERNAL->name(),
				4       => 'blog', // Deprecated status
				default => (string) ($item['entry_type'] ?? EntryType::DEFAULT->name()),
			};

			$slug = $this->initializeSlugAndTranslations($item, $pageId, $pageTitles);

			$items[] = [
				'page_id'         => $pageId,
				'category_id'     => intval($item['category_id']),
				'author_id'       => intval($item['author_id']),
				'slug'            => $slug,
				'type'            => str_replace('md', 'markdown', (string) $item->type),
				'entry_type'      => $entryType,
				'permissions'     => intval($item['permissions']),
				'status'          => $status,
				'num_views'       => intval($item['num_views']),
				'num_comments'    => intval($item['num_comments']),
				'created_at'      => intval($item['created_at']),
				'updated_at'      => intval($item['updated_at']),
				'deleted_at'      => intval($item['deleted_at']),
				'last_comment_id' => intval($item['last_comment_id']),
			];

			$translations = array_merge($translations, $this->extractTranslations($item, $pageId));
			$params = array_merge($params, $this->extractParams($item, $pageId));
			$comments = array_merge($comments, $this->extractComments($item, $pageId));
		}

		$this->updateSlugs($items, $pageTitles, 'page_id');

		$this->startTransaction($items);

		$results = $this->insertData('lp_pages', $items, ['page_id'], true);
		$results = $this->replaceTranslations($translations, $results);
		$results = $this->replaceParams($params, $results);
		$results = $this->replaceComments($comments, $results);

		$this->finishTransaction($results);
	}

	protected function extractComments(SimpleXMLElement $item, int $id): array
	{
		$comments = [];

		if ($item->comments ?? null) {
			foreach ($item->comments->children() as $commentItem) {
				$comments[] = [
					'id'         => intval($commentItem['id']),
					'parent_id'  => intval($commentItem['parent_id']),
					'page_id'    => $id,
					'author_id'  => intval($commentItem['author_id']),
					'message'    => (string) $commentItem->message,
					'created_at' => intval($commentItem['created_at']),
				];
			}
		}

		return $comments;
	}
}
