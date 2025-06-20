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
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\Traits\HasComments;
use Bugo\LightPortal\Enums\EntryType;

use function intval;
use function str_replace;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property mixed|void $item
 */
final class PageImport extends AbstractImport
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

	protected function run(): void
	{
		if (empty($xml = $this->getFile()))
			return;

		if (! isset($xml->pages->item[0]['page_id'])) {
			ErrorHandler::fatalLang('lp_wrong_import_file', false);
		}

		$items = $translations = $params = $comments = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$status = intval($item['status']);

				$entryType = match ($status) {
					3       => EntryType::INTERNAL->name(),
					4       => 'blog', // Deprecated status
					default => (string) ($item['entry_type'] ?? EntryType::DEFAULT->name()),
				};

				$items[] = [
					'page_id'      => $pageId = intval($item['page_id']),
					'category_id'  => intval($item['category_id']),
					'author_id'    => intval($item['author_id']),
					'slug'         => (string) ($item->alias ?? $item->slug),
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

				if ($item->titles || $item->contents || $item->descriptions) {
					foreach ($item->titles as $title) {
						foreach ($title as $lang => $text) {
							if (! isset($translations[$lang . '_' . $pageId])) {
								$translations[$lang . '_' . $pageId] = [
									'item_id' => $pageId,
									'type'    => 'page',
									'lang'    => $lang,
									'title'   => (string) $text,
								];
							}
						}
					}

					foreach ($item->contents as $content) {
						foreach ($content as $lang => $text) {
							if (! isset($translations[$lang . '_' . $pageId])) {
								$translations[$lang . '_' . $pageId] = [
									'item_id' => $pageId,
									'type'    => 'page',
									'lang'    => $lang,
								];
							}

							$translations[$lang . '_' . $pageId]['content'] = (string) $text;
						}
					}

					foreach ($item->descriptions as $description) {
						foreach ($description as $lang => $text) {
							if (! isset($translations[$lang . '_' . $pageId])) {
								$translations[$lang . '_' . $pageId] = [
									'item_id' => $pageId,
									'type'    => 'page',
									'lang'    => $lang,
								];
							}

							$translations[$lang . '_' . $pageId]['description'] = (string) $text;
						}
					}
				}

				if ($item->comments) {
					foreach ($item->comments as $comment) {
						foreach ($comment as $v) {
							$comments[] = [
								'id'         => intval($v['id']),
								'parent_id'  => intval($v['parent_id']),
								'page_id'    => $pageId,
								'author_id'  => intval($v['author_id']),
								'message'    => $v->message,
								'created_at' => intval($v['created_at']),
							];
						}
					}
				}

				if ($item->params) {
					foreach ($item->params as $param) {
						foreach ($param as $k => $v) {
							$params[] = [
								'item_id' => $pageId,
								'type'    => 'page',
								'name'    => $k,
								'value'   => $v,
							];
						}
					}
				}
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
}
