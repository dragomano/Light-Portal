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

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\Traits\HasComments;
use Bugo\LightPortal\Enums\EntryType;
use SimpleXMLElement;

use function array_merge;
use function intval;
use function str_replace;
use function str_starts_with;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class PageImport extends XmlImporter
{
	use HasComments;

	protected string $entity = 'pages';

	public function main(): void
	{
		User::$me->isAllowedTo('admin_forum');

		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_pages_import_info'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function processItems(): void
	{
		$items = $translations = $params = $comments = [];
		$pageTitles = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$pageId = intval($item['page_id']);
			$status = intval($item['status']);
			$slug = (string) ($item->alias ?? $item->slug);

			$itemTranslations = $this->extractTranslations($item);
			foreach ($itemTranslations as $translation) {
				if (isset($translation['title'])) {
					$pageTitles[$pageId][$translation['lang']] = $translation['title'];
				}
			}

			$entryType = match ($status) {
				3       => EntryType::INTERNAL->name(),
				4       => 'blog', // Deprecated status
				default => (string) ($item['entry_type'] ?? EntryType::DEFAULT->name()),
			};

			if (empty(trim($slug))) {
				$slug = 'temp-' . $pageId;
			}

			$items[] = [
				'page_id'      => $pageId,
				'category_id'  => intval($item['category_id']),
				'author_id'    => intval($item['author_id']),
				'slug'         => $slug,
				'type'         => str_replace('md', 'markdown', (string) $item->type),
				'entry_type'   => $entryType,
				'permissions'  => intval($item['permissions']),
				'status'       => $status,
				'num_views'    => intval($item['num_views']),
				'num_comments' => intval($item['num_comments']),
				'created_at'   => intval($item['created_at']),
				'updated_at'   => intval($item['updated_at']),
				'deleted_at'   => intval($item['deleted_at']),
			];

			$translations = array_merge($translations, $itemTranslations);
			$params = array_merge($params, $this->extractParams($item));
			$comments = array_merge($comments, $this->extractComments($item));
		}

		foreach ($items as &$item) {
			if (str_starts_with($item['slug'], 'temp-')) {
				$pageId = $item['page_id'];
				$titles = $pageTitles[$pageId] ?? [];
				$item['slug'] = $this->generateSlug($titles);
			}
		}

		$this->startTransaction($items);

		$results = $this->insertData(
			'lp_pages',
			'replace',
			$items,
			[
				'page_id'      => 'int',
				'category_id'  => 'int',
				'author_id'    => 'int',
				'slug'         => 'string-255',
				'type'         => 'string',
				'entry_type'   => 'string',
				'permissions'  => 'int',
				'status'       => 'int',
				'num_views'    => 'int',
				'num_comments' => 'int',
				'created_at'   => 'int',
				'updated_at'   => 'int',
				'deleted_at'   => 'int',
			],
			['page_id'],
		);

		$this->replaceTranslations($translations, $results);
		$this->replaceParams($params, $results);
		$this->replaceComments($comments, $results);

		$this->finishTransaction($results);
	}

	protected function extractComments(SimpleXMLElement $item): array
	{
		$comments = [];
		$itemId = (string) $item['page_id'];

		if ($item->comments ?? null) {
			foreach ($item->comments as $comment) {
				foreach ($comment as $v) {
					$comments[] = [
						'id'         => intval($v['id']),
						'parent_id'  => intval($v['parent_id']),
						'page_id'    => $itemId,
						'author_id'  => intval($v['author_id']),
						'message'    => (string) $v->message,
						'created_at' => intval($v['created_at']),
					];
				}
			}
		}

		return $comments;
	}
}
