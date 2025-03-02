<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 19.02.25
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\User;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Language;

if (! defined('LP_NAME'))
	die('No direct access...');

class TinyPortalMigration extends Plugin
{
	public string $type = 'impex';

	private const AREA = 'import_from_tp';

	public function updateAdminAreas(Event $e): void
	{
		$areas = &$e->args->areas;

		if (User::$me->is_admin) {
			$areas['lp_blocks']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];

			$areas['lp_pages']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];

			$areas['lp_categories']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];
		}
	}

	public function updateBlockAreas(Event $e): void
	{
		$e->args->areas[self::AREA] = [new BlockImport(), 'main'];
	}

	public function updatePageAreas(Event $e): void
	{
		$e->args->areas[self::AREA] = [new PageImport(), 'main'];
	}

	public function updateCategoryAreas(Event $e): void
	{
		$e->args->areas[self::AREA] = [new CategoryImport(), 'main'];
	}

	public function importPages(Event $e): void
	{
		if ($this->request()->get('sa') !== self::AREA)
			return;

		$items = &$e->args->items;
		$titles = &$e->args->titles;
		$params = &$e->args->params;
		$comments = &$e->args->comments;

		$comments = $this->getComments(array_keys($items));

		foreach ($items as $pageId => $item) {
			$items[$pageId]['num_comments'] = empty($comments[$pageId]) ? 0 : sizeof($comments[$pageId]);

			$titles[] = [
				'item_id' => $pageId,
				'type'    => 'page',
				'lang'    => Config::$language,
				'title'   => $item['subject'],
			];

			if (Config::$language !== Language::getFallbackValue() && ! empty(Config::$modSettings['userLanguage'])) {
				$titles[] = [
					'item_id' => $pageId,
					'type'    => 'page',
					'lang'    => Language::getFallbackValue(),
					'title'   => $item['subject'],
				];
			}

			unset($items[$pageId]['subject']);

			if (in_array('author', $items[$pageId]['options']) || in_array('date', $items[$pageId]['options']))
				$params[] = [
					'item_id' => $pageId,
					'type'    => 'page',
					'name'    => 'show_author_and_date',
					'value'   => 1,
				];

			if (in_array('commentallow', $items[$pageId]['options']))
				$params[] = [
					'item_id' => $pageId,
					'type'    => 'page',
					'name'    => 'allow_comments',
					'value'   => 1,
				];

			unset($items[$pageId]['options']);
		}
	}

	private function getComments(array $pages): array
	{
		$result = Db::$db->query('', '
			SELECT *
			FROM {db_prefix}tp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (com.member_id = mem.id_member)
			WHERE com.item_type = {string:type}' . ($pages === [] ? '' : '
				AND com.item_id IN ({array_int:pages})'),
			[
				'type'  => 'article_comment',
				'pages' => $pages,
			]
		);

		$comments = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if ($row['item_id'] < 0 || empty($row['comment']))
				continue;

			$comments[$row['item_id']][] = [
				'id'         => $row['id'],
				'parent_id'  => 0,
				'page_id'    => $row['item_id'],
				'author_id'  => $row['member_id'],
				'message'    => $row['comment'],
				'created_at' => $row['datetime'],
			];
		}

		Db::$db->free_result($result);

		return $comments;
	}
}
